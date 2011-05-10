<?php

class adapter_DB
{
    protected static $db = array();

    static function connect($dsn)
    {
        $db = md5(implode(';', $dsn));

        if (isset(self::$db[$db])) return self::$db[$db];

        $db =& self::$db[$db];
        $db = \Doctrine\DBAL\DriverManager::getConnection($dsn);

        return $db;
    }

    static function disconnect($db)
    {
        $db->close();
    }

    static function __destructStatic()
    {
        foreach (self::$db as $db) self::disconnect($db);

        self::$db = array();
    }
}
