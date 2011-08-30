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


class pForm_select extends self
{
    protected $entityClass = null;

    protected function init(&$param)
    {
        if (isset($param['entityClass']))
        {
            $this->entityClass = $param['entityClass'];
        }

        if (isset($param['dql']))
        {
            $param['loop'] = new loop_array(
                EM()->createQuery($param['dql'])->getResult(),
                'filter_rawArray'
            );
        }

        return parent::init($param);
    }

    function getDbValue()
    {
        return  ($this->entityClass && $v = $this->getValue())
            ? EM()->getReference($this->entityClass, $v)
            : parent::getDbValue();
    }
}