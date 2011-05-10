<?php /****************** vi: set fenc=utf-8 ts=4 sw=4 et: *****************
 *
 *   Copyright : (C) 2011 Nicolas Grekas. All rights reserved.
 *   Email     : p@tchwork.org
 *   License   : http://www.gnu.org/licenses/agpl.txt GNU/AGPL
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU Affero General Public License as
 *   published by the Free Software Foundation, either version 3 of the
 *   License, or (at your option) any later version.
 *
 ***************************************************************************/


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
