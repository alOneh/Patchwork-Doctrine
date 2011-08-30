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


class adapter_EM
{
    protected static $em = array();

    static function connect($dsn)
    {
        $hash = md5(implode(';', $dsn));

        if (isset(self::$em[$hash])) return self::$em[$hash];

        $config = new \Doctrine\ORM\Configuration;

        $cache = new $CONFIG['doctrine.cache'];

        $driver = $config->newDefaultAnnotationDriver(array($CONFIG['doctrine.entities.dir']));

        $config->setMetadataCacheImpl($cache);
        $config->setMetadataDriverImpl($driver);
        $config->setQueryCacheImpl($cache);
        $config->setProxyDir($CONFIG['doctrine.proxy.dir']);
        $config->setAutoGenerateProxyClasses($CONFIG['doctrine.proxy.dir']);
        $config->setProxyNamespace($CONFIG['doctrine.proxy.namespace']);

        if (!empty($CONFIG['doctrine.dbal.logger']))
        {
            $config->setSQLLogger(new $CONFIG['doctrine.dbal.logger']);
        }

        self::$em[$hash] = \Doctrine\ORM\EntityManager::create($dsn, $config);

        self::$em[$hash]->getConnection()->getDatabasePlatform()
            ->registerDoctrineTypeMapping('enum', 'string');

        return self::$em[$hash];
    }
}
