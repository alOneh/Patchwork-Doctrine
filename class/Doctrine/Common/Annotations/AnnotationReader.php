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

namespace Doctrine\Common\Annotations;

class AnnotationReader extends self
{
    public function getClassAnnotations(\ReflectionClass $class)
    {
        $c = $class->getName();
        $i = strrpos($c, '__');

        return false !== $i && (false !== $c = substr($c, $i+2)) && '' === trim($c, '0123456789')
            ? array()
            : parent::getClassAnnotations($class->getParentClass());
    }
}