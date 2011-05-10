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

namespace Doctrine\DBAL;

class Connection extends self
{
    public function delete($tableName, array $identifier)
    {
        list($tableName, $identifier) = $this->quoteArgsAsIdentifiers($tableName, $identifier);

        return parent::delete($tableName, $identifier);
    }

    public function update($tableName, array $data, array $identifier)
    {
        list($tableName, $data, $identifier) = $this->quoteArgsAsIdentifiers($tableName, $data, $identifier);

        return parent::update($tableName, $data, $identifier);
    }

    public function insert($tableName, array $data)
    {
        list($tableName, $data) = $this->quoteArgsAsIdentifiers($tableName, $data);

        return parent::insert($tableName, $data);
    }


    protected function quoteArgsAsIdentifiers()
    {
        $a = func_get_args();
        $c = $this->quoteIdentifier('');

        foreach ($a as &$data)
        {
            if (is_string($data))
            {
                if ('' === $data || $c[0] !== $data[0])
                    $data = $this->quoteIdentifier($data);
            }
            else
            {
                $quotedData = array();
                foreach ($data as $k => $v)
                {
                    list($k) = $this->quoteArgsAsIdentifiers($k);
                    $quotedData[$k] = $v;
                }
                $data = $quotedData;
            }
        }

        return $a;
    }
}