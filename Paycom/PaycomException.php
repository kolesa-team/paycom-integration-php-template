<?php

declare(strict_types=1);

namespace Paycom;

class PaycomException extends \Exception
{
    public const int ERROR_INTERNAL_SYSTEM         = -32400;
    public const int ERROR_INSUFFICIENT_PRIVILEGE  = -32504;
    public const int ERROR_INVALID_JSON_RPC_OBJECT = -32600;
    public const int ERROR_METHOD_NOT_FOUND        = -32601;
    public const int ERROR_INVALID_AMOUNT          = -31001;
    public const int ERROR_TRANSACTION_NOT_FOUND   = -31003;
    public const int ERROR_INVALID_ACCOUNT         = -31050;
    public const int ERROR_COULD_NOT_CANCEL        = -31007;
    public const int ERROR_COULD_NOT_PERFORM       = -31008;

    public ?int $request_id;
    public array $error;
    public string|array|null $data;

    /**
     * PaycomException constructor.
     * @param int|null $request_id id of the request.
     * @param string|array|null $message error message.
     * @param int $code error code.
     * @param string|null $data parameter name, that resulted to this error.
     */
    public function __construct(?int $request_id, string|array|null $message, int $code, ?string $data = null)
    {
        $this->request_id = $request_id;
        $this->message    = $message;
        $this->code       = $code;
        $this->data       = $data;

        // prepare error data
        $this->error = ['code' => $this->code];

        if ($this->message) {
            $this->error['message'] = $this->message;
        }

        if ($this->data) {
            $this->error['data'] = $this->data;
        }
    }

    public function send(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        // create response
        $response = [
            'id'     => $this->request_id,
            'result' => null,
            'error'  => $this->error,
        ];

        echo json_encode($response);
    }

    public static function message(string $ru, string $uz = '', string $en = ''): array
    {
        return ['ru' => $ru, 'uz' => $uz, 'en' => $en];
    }
}
