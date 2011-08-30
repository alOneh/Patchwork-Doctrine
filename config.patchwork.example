<?php

$CONFIG += array(
    'DSN' => array(
        'dbname'   => 'database',
        'user'     => 'user',
        'password' => 'password',
        'host'     => 'localhost',
        'driver'   => 'pdo_mysql'
    ),
    'doctrine.cache'       => '\Doctrine\Common\Cache\ArrayCache', // use ApcCache for production env
    'doctrine.mapping.dir' => 'data/mapping',
    'doctrine.proxy.dir'   => 'class/Proxies',
    'doctrine.proxy.generate' => true, // set to false to production env
    'doctrine.dbal.logger'      => '',
    'doctrine.event'       => false,
    'doctrine.event.listeners' => array(),
);

function EM()
{
    return adapter_EM::connect($GLOBALS['CONFIG']['DSN']);
}