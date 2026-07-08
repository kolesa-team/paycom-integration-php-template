<?php

declare(strict_types=1);

namespace Paycom;

class Response
{
    /**
     * Response constructor.
     * @param Request $request request object.
     */
    public function __construct(private readonly Request $request)
    {
    }

    /**
     * Sends response with the given result and error.
     * @param mixed $result result of the request.
     * @param mixed $error error.
     */
    public function send(mixed $result, mixed $error = null): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        $response = [
            'jsonrpc' => '2.0',
            'id'      => $this->request->id,
            'result'  => $result,
            'error'   => $error,
        ];

        echo json_encode($response);
    }

    /**
     * Generates PaycomException exception with given parameters.
     * @param int $code error code.
     * @param string|array|null $message error message.
     * @param string|null $data parameter name, that resulted to this error.
     * @return never
     * @throws PaycomException
     */
    public function error(int $code, string|array|null $message = null, ?string $data = null): never
    {
        throw new PaycomException($this->request->id, $message, $code, $data);
    }
}
