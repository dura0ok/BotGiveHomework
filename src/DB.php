<?php

namespace App;

date_default_timezone_set('Europe/Moscow');

use PDO;

class DB
{
    protected static $instance = null;
    private $host;
    private $user;
    private $password;
    private $dbname;
    private $char;

    public function __construct($host, $user, $password, $dbname, $char = 'utf8')
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->dbname = $dbname;
        $this->char = $char;
    }

    public static function __callStatic($method, $args)
    {
        return call_user_func_array(array(self::instance(), $method), $args);
    }

    public static function instance()
    {
        if (self::$instance === null) {
            $opt = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => true,
            );
            $dsn = 'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'] . ';charset=utf8';
            self::$instance = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], $opt);
        }
        return self::$instance;
    }

    public static function run($sql, $args = [])
    {
        if (!$args) {
            return self::instance()->query($sql);
        }
        $stmt = self::instance()->prepare($sql);
        $stmt->execute($args);
        return $stmt;
    }

    public function __clone()
    {
    }
}
