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
