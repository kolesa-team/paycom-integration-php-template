<?php

declare(strict_types=1);

namespace Paycom;

/**
 * Class Transaction
 *
 * Example MySQL table might look like to the following:
 *
 * CREATE TABLE `transactions` (
 *   `id` INT(11) NOT NULL AUTO_INCREMENT,
 *   `paycom_transaction_id` VARCHAR(25) NOT NULL COLLATE 'utf8_unicode_ci',
 *   `paycom_time` VARCHAR(13) NOT NULL COLLATE 'utf8_unicode_ci',
 *   `paycom_time_datetime` DATETIME NOT NULL,
 *   `create_time` DATETIME NOT NULL,
 *   `perform_time` DATETIME NULL DEFAULT NULL,
 *   `cancel_time` DATETIME NULL DEFAULT NULL,
 *   `amount` INT(11) NOT NULL,
 *   `state` TINYINT(2) NOT NULL,
 *   `reason` TINYINT(2) NULL DEFAULT NULL,
 *   `receivers` VARCHAR(500) NULL DEFAULT NULL COMMENT 'JSON array of receivers' COLLATE 'utf8_unicode_ci',
 *   `order_id` INT(11) NOT NULL,
 *
 *   PRIMARY KEY (`id`)
 * )
 *   COLLATE='utf8_unicode_ci'
 *   ENGINE=InnoDB
 *   AUTO_INCREMENT=1;
 *
 */
class Transaction extends Database
{
    /** Transaction expiration time in milliseconds. 43 200 000 ms = 12 hours. */
    public const int TIMEOUT = 43200000;

    public const int STATE_CREATED                  = 1;
    public const int STATE_COMPLETED                = 2;
    public const int STATE_CANCELLED                = -1;
    public const int STATE_CANCELLED_AFTER_COMPLETE = -2;

    public const int REASON_RECEIVERS_NOT_FOUND         = 1;
    public const int REASON_PROCESSING_EXECUTION_FAILED = 2;
    public const int REASON_EXECUTION_FAILED            = 3;
    public const int REASON_CANCELLED_BY_TIMEOUT        = 4;
    public const int REASON_FUND_RETURNED               = 5;
    public const int REASON_UNKNOWN                     = 10;

    /** @var string|null Paycom transaction id. */
    public ?string $paycom_transaction_id = null;

    /** @var int|null Paycom transaction time as is without change. */
    public ?int $paycom_time = null;

    /** @var string|null Paycom transaction time as date and time string. */
    public ?string $paycom_time_datetime = null;

    /** @var int|string|null Transaction id in the merchant's system. */
    public int|string|null $id = null;

    /** @var string|null Transaction create date and time in the merchant's system. */
    public ?string $create_time = null;

    /** @var string|null Transaction perform date and time in the merchant's system. */
    public ?string $perform_time = null;

    /** @var string|null Transaction cancel date and time in the merchant's system. */
    public ?string $cancel_time = null;

    /** @var int|null Transaction state. */
    public ?int $state = null;

    /** @var int|null Transaction cancelling reason. */
    public ?int $reason = null;

    /** @var int|null Amount value in coins, this is service or product price. */
    public ?int $amount = null;

    /** @var array|null Pay receivers. Null - owner is the only receiver. */
    public ?array $receivers = null;

    // additional fields:
    // - to identify order or product, for example, code of the order
    // - to identify client, for example, account id or phone number

    /** @var int|string|null Code to identify the order or service for pay. */
    public int|string|null $order_id = null;

    /**
     * Saves current transaction instance in a data store.
     * @return bool true - on success
     */
    public function save(): bool
    {
        // todo: Implement creating/updating transaction into data store
        // todo: Populate $id property with newly created transaction id

        // Example implementation

        $db = self::db();

        if (!$this->id) {

            // Create a new transaction

            $this->create_time = Format::timestamp2datetime(Format::timestamp());
            $sql               = "INSERT INTO transactions SET
                                    paycom_transaction_id = :pPaycomTxId,
                                    paycom_time = :pPaycomTime,
                                    paycom_time_datetime = :pPaycomTimeStr,
                                    create_time = :pCreateTime,
                                    amount = :pAmount,
                                    state = :pState,
                                    receivers = :pReceivers,
                                    order_id = :pOrderId";

            $sth = $db->prepare($sql);

            $is_success = $sth->execute([
                ':pPaycomTxId'    => $this->paycom_transaction_id,
                ':pPaycomTime'    => $this->paycom_time,
                ':pPaycomTimeStr' => $this->paycom_time_datetime,
                ':pCreateTime'    => $this->create_time,
                ':pAmount'        => 1 * $this->amount,
                ':pState'         => $this->state,
                ':pReceivers'     => $this->receivers ? json_encode($this->receivers, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
                ':pOrderId'       => 1 * $this->order_id,
            ]);

            if ($is_success) {
                // set the newly inserted transaction id
                $this->id = $db->lastInsertId();
            }
        } else {

            // Update an existing transaction by id

            $sql = "UPDATE transactions SET
                    perform_time = :pPerformTime,
                    cancel_time = :pCancelTime,
                    state = :pState,
                    reason = :pReason";

            $params = [];

            if ($this->amount) {
                $sql                .= ", amount = :pAmount";
                $params[':pAmount'] = 1 * $this->amount;
            }

            $sql .= " where paycom_transaction_id = :pPaycomTxId and id=:pId";

            $sth = $db->prepare($sql);

            $perform_time = $this->perform_time ?: null;
            $cancel_time  = $this->cancel_time ?: null;
            $reason       = $this->reason ? 1 * $this->reason : null;

            $params[':pPerformTime'] = $perform_time;
            $params[':pCancelTime']  = $cancel_time;
            $params[':pState']       = 1 * $this->state;
            $params[':pReason']      = $reason;
            $params[':pPaycomTxId']  = $this->paycom_transaction_id;
            $params[':pId']          = $this->id;

            $is_success = $sth->execute($params);
        }

        return $is_success;
    }

    /**
     * Cancels transaction with the specified reason.
     * @param int $reason cancelling reason.
     * @return void
     */
    public function cancel(int $reason): void
    {
        // todo: Implement transaction cancelling on data store

        // todo: Populate $cancel_time with value
        $this->cancel_time = Format::timestamp2datetime(Format::timestamp());

        // todo: Change $state to cancelled (-1 or -2) according to the current state

        if ($this->state == self::STATE_COMPLETED) {
            // Scenario: CreateTransaction -> PerformTransaction -> CancelTransaction
            $this->state = self::STATE_CANCELLED_AFTER_COMPLETE;
        } else {
            // Scenario: CreateTransaction -> CancelTransaction
            $this->state = self::STATE_CANCELLED;
        }

        // set reason
        $this->reason = $reason;

        // todo: Update transaction on data store
        $this->save();
    }

    /**
     * Determines whether current transaction is expired or not.
     * @return bool true - if current instance of the transaction is expired, false - otherwise.
     */
    public function isExpired(): bool
    {
        // todo: Implement transaction expiration check
        // for example, if transaction is active and passed TIMEOUT milliseconds after its creation, then it is expired
        return $this->state == self::STATE_CREATED && abs(Format::datetime2timestamp($this->create_time) - Format::timestamp(true)) > self::TIMEOUT;
    }

    /**
     * Find transaction by given parameters.
     * @param array $params parameters
     * @return static|null
     * @throws PaycomException invalid parameters specified
     */
    public function find(array $params): ?static
    {
        $db = self::db();

        // todo: Implement searching transaction by id, populate current instance with data and return it
        if (isset($params['id'])) {
            $sql        = "SELECT * FROM transactions WHERE paycom_transaction_id = :pPaycomTxId";
            $sth        = $db->prepare($sql);
            $is_success = $sth->execute([':pPaycomTxId' => $params['id']]);
        } elseif (isset($params['account'], $params['account']['order_id'])) {
            // todo: Implement searching transactions by given parameters and return the list of transactions
            // search by order id active or completed transaction
            $sql        = "SELECT * FROM transactions WHERE state IN (1, 2) AND order_id = :pOrderId";
            $sth        = $db->prepare($sql);
            $is_success = $sth->execute([':pOrderId' => $params['account']['order_id']]);
        } else {
            throw new PaycomException(
                $params['request_id'],
                'Parameter to find a transaction is not specified.',
                PaycomException::ERROR_INTERNAL_SYSTEM
            );
        }

        // if SQL operation succeeded, then try to populate instance properties with values
        if ($is_success) {

            $row = $sth->fetch();

            if ($row) {

                $this->id                    = $row['id'];
                $this->paycom_transaction_id = $row['paycom_transaction_id'];
                $this->paycom_time           = 1 * $row['paycom_time'];
                $this->paycom_time_datetime  = $row['paycom_time_datetime'];
                $this->create_time           = $row['create_time'];
                $this->perform_time          = $row['perform_time'];
                $this->cancel_time           = $row['cancel_time'];
                $this->state                 = 1 * $row['state'];
                $this->reason                = $row['reason'] ? 1 * $row['reason'] : null;
                $this->amount                = 1 * $row['amount'];
                $this->receivers             = $row['receivers'] ? json_decode($row['receivers'], true) : null;
                $this->order_id              = 1 * $row['order_id'];

                return $this;
            }

        }

        // transaction not found, return null
        return null;

        // Possible features:
        // Search transaction by product/order id that specified in $params
        // Search transactions for a given period of time that specified in $params
    }

    /**
     * Gets list of transactions for the given period including period boundaries.
     * @param int|string $from_date start of the period in timestamp.
     * @param int|string $to_date end of the period in timestamp.
     * @return array list of found transactions converted into report format for send as a response.
     */
    public function report(int|string $from_date, int|string $to_date): array
    {
        $from_date = Format::timestamp2datetime($from_date);
        $to_date   = Format::timestamp2datetime($to_date);

        // container to hold rows/document from data store
        $rows = [];

        // todo: Retrieve transactions for the specified period from data store

        // Example implementation

        $db = self::db();

        $sql = "SELECT * FROM transactions
                WHERE paycom_time_datetime BETWEEN :from_date AND :to_date
                ORDER BY paycom_time_datetime";

        $sth        = $db->prepare($sql);
        $is_success = $sth->execute([':from_date' => $from_date, ':to_date' => $to_date]);
        if ($is_success) {
            $rows = $sth->fetchAll();
        }

        // assume, here we have $rows variable that is populated with transactions from data store
        // normalize data for response
        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id'           => $row['paycom_transaction_id'], // paycom transaction id
                'time'         => 1 * $row['paycom_time'], // paycom transaction timestamp as is
                'amount'       => 1 * $row['amount'],
                'account'      => [
                    'order_id' => 1 * $row['order_id'], // account parameters to identify client/order/service
                    // ... additional parameters may be listed here, which are belongs to the account
                ],
                'create_time'  => Format::datetime2timestamp($row['create_time']),
                'perform_time' => Format::datetime2timestamp($row['perform_time']),
                'cancel_time'  => Format::datetime2timestamp($row['cancel_time']),
                'transaction'  => 1 * $row['id'],
                'state'        => 1 * $row['state'],
                'reason'       => isset($row['reason']) ? 1 * $row['reason'] : null,
                'receivers'    => isset($row['receivers']) ? json_decode($row['receivers'], true) : null,
            ];
        }

        return $result;

    }
}
