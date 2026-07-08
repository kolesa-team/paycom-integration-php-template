<?php

declare(strict_types=1);

namespace Paycom;

class Database
{
    public ?array $config;

    protected static ?\PDO $db = null;

    public function __construct(?array $config = null)
    {
        $this->config = $config;
    }

    public function new_connection(): \PDO
    {
        // connect to the database
        $db_options = [
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC, // fetch rows as associative array
            \PDO::ATTR_PERSISTENT         => true // use existing connection if exists
        ];

        return new \PDO(
            'mysql:dbname=' . $this->config['db']['database'] . ';host=' . $this->config['db']['host'] . ';charset=utf8',
            $this->config['db']['username'],
            $this->config['db']['password'],
            $db_options
        );
    }

    /**
     * Connects to the database
     * @return \PDO connection
     */
    public static function db(): \PDO
    {
        if (!self::$db) {
            $config   = require_once CONFIG_FILE;
            $instance = new self($config);
            self::$db = $instance->new_connection();
        }

        return self::$db;
    }
}
