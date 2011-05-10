<?php

namespace Doctrine\DBAL\Driver\PDOMySql;

class Driver extends self
{
    public function connect(array $params, $username = null, $password = null, array $driverOptions = array())
    {
        $conn = parent::connect($params, $username, $password, $driverOptions);

        $conn->exec("SET NAMES utf8 COLLATE utf8_general_ci");

        return $conn;
    }
}
