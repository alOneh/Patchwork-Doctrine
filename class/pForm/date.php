<?php /***** vi: set encoding=utf-8 expandtab shiftwidth=4: ****************
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


class pForm_date extends self
{
    protected $getDateTimeFromString = true;

    function init(&$param)
    {
        if (isset($param['getDateTimeFromString']))
            $this->getDateTimeFromString = $param['getDateTimeFromString'];

        parent::init($param);
    }

    function setValue($value)
    {
        if ($value instanceof DateTime) $value = $value->format('Y-m-d');
        return parent::setValue($value);
    }

    function getDbValue()
    {
        $v = parent::getDbValue();

        if ($this->getDateTimeFromString)
            $v = $v ? new DateTime($v) : null;

        return $v;
    }
}
