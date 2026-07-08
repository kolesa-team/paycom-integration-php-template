<?php

declare(strict_types=1);

// fallback for getallheaders(), e.g. for nginx
if (!function_exists('getallheaders')) {
    function getallheaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (str_starts_with((string)$name, 'HTTP_')) {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr((string)$name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}
