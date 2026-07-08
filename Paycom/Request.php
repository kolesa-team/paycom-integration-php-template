<?php

declare(strict_types=1);

namespace Paycom;

class Request
{
    /** @var mixed decoded request payload */
    public mixed $payload;

    /** @var int|null id of the request */
    public ?int $id;

    /** @var string|null method name, such as <em>CreateTransaction</em> */
    public ?string $method;

    /** @var array request parameters, such as <em>amount</em>, <em>account</em> */
    public array $params;

    /** @var int|null amount value in coins */
    public ?int $amount;

    /**
     * Request constructor.
     * Parses request payload and populates properties with values.
     */
    public function __construct()
    {
        $request_body  = file_get_contents('php://input');
        $this->payload = json_decode((string)$request_body, true);

        if (!$this->payload) {
            throw new PaycomException(
                null,
                'Invalid JSON-RPC object.',
                PaycomException::ERROR_INVALID_JSON_RPC_OBJECT
            );
        }

        // populate request object with data
        $this->id     = isset($this->payload['id']) ? 1 * $this->payload['id'] : null;
        $this->method = isset($this->payload['method']) ? trim((string)$this->payload['method']) : null;
        $this->params = $this->payload['params'] ?? [];
        $this->amount = isset($this->payload['params']['amount']) ? 1 * $this->payload['params']['amount'] : null;

        // add request id into params too
        $this->params['request_id'] = $this->id;
    }

    /**
     * Gets account parameter if such exists, otherwise returns null.
     * @param string $param name of the parameter.
     * @return mixed account parameter value or null if such parameter doesn't exists.
     */
    public function account(string $param): mixed
    {
        return isset($this->params['account'], $this->params['account'][$param]) ? $this->params['account'][$param] : null;
    }
}
