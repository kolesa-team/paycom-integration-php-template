<?php

declare(strict_types=1);

namespace Paycom;

class Format
{
    /**
     * Converts coins to som.
     * @param int|float|string $coins coins.
     * @return float coins converted to som.
     */
    public static function toSom(int|float|string $coins): float
    {
        return 1 * $coins / 100;
    }

    /**
     * Converts som to coins.
     * @param int|float|string $amount
     * @return float
     */
    public static function toCoins(int|float|string $amount): float
    {
        return round(1 * $amount * 100);
    }

    /**
     * Get current timestamp in seconds or milliseconds.
     * @param bool $milliseconds true - get timestamp in milliseconds, false - in seconds.
     * @return int|float current timestamp value
     */
    public static function timestamp(bool $milliseconds = false): int|float
    {
        if ($milliseconds) {
            return round(microtime(true)) * 1000; // milliseconds
        }

        return time(); // seconds
    }

    /**
     * Converts timestamp value from milliseconds to seconds.
     * @param int|float|string $timestamp timestamp in milliseconds.
     * @return int|float|string timestamp in seconds.
     */
    public static function timestamp2seconds(int|float|string $timestamp): int|float|string
    {
        // is it already as seconds
        if (strlen((string)$timestamp) == 10) {
            return $timestamp;
        }

        return floor(1 * $timestamp / 1000);
    }

    /**
     * Converts timestamp value from seconds to milliseconds.
     * @param int|float|string $timestamp timestamp in seconds.
     * @return int|float|string timestamp in milliseconds.
     */
    public static function timestamp2milliseconds(int|float|string $timestamp): int|float|string
    {
        // is it already as milliseconds
        if (strlen((string)$timestamp) == 13) {
            return $timestamp;
        }

        return $timestamp * 1000;
    }

    /**
     * Converts timestamp to date time string.
     * @param int|float|string $timestamp timestamp value as seconds or milliseconds.
     * @return string string representation of the timestamp value in 'Y-m-d H:i:s' format.
     */
    public static function timestamp2datetime(int|float|string $timestamp): string
    {
        // if as milliseconds, convert to seconds
        if (strlen((string)$timestamp) == 13) {
            $timestamp = self::timestamp2seconds($timestamp);
        }

        // convert to datetime string
        return date('Y-m-d H:i:s', (int)$timestamp);
    }

    /**
     * Converts date time string to timestamp value.
     * @param string|null $datetime date time string.
     * @return int|string|null timestamp as milliseconds.
     */
    public static function datetime2timestamp(?string $datetime): int|string|null
    {
        if ($datetime) {
            return 1000 * strtotime($datetime);
        }

        return $datetime;
    }
}
