<?php

declare(strict_types=1);

namespace Paycom;

class Merchant
{
    public array $config;

    public function __construct(array $config)
    {
        $this->config = $config;

        // read key from key file
        if ($this->config['keyFile']) {
            $this->config['key'] = trim((string)file_get_contents($this->config['keyFile']));
        }
    }

    public function Authorize(?int $request_id): bool
    {
        $headers = getallheaders();

        if (!$headers || !isset($headers['Authorization']) ||
            !preg_match('/^\s*Basic\s+(\S+)\s*$/i', $headers['Authorization'], $matches) ||
            base64_decode($matches[1]) != $this->config['login'] . ":" . $this->config['key']
        ) {
            throw new PaycomException(
                $request_id,
                'Insufficient privilege to perform this method.',
                PaycomException::ERROR_INSUFFICIENT_PRIVILEGE
            );
        }

        return true;
    }
}
