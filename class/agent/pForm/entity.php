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

abstract class agent_pForm_entity extends agent_pForm
{
    public $get = array('__1__:i:1' => 0);

    protected

    $type = array(),
    $entity,
    $entityName,
    $entityMetadata;

    function control()
    {
        parent::control();

        $t = explode('_', substr(get_class($this), 6));

        if ('new' === end($t))
        {
            $new = true;
            array_pop($t);
        }
        else $new = false;

        $this->type || $this->type = $t;

        $t = ucwords(implode('_', $this->type));
        $this->entityName = "Entities\\{$t}";

        if ($this->entity) return;
        else $this->entity = new $this->entityName();

        $this->getEntityMetadata($this->entity);

        if (!empty($this->get->__1__))
        {
            $this->entity = EM()->find($this->entityName, $this->get->__1__);

            $this->getData();

            $this->entity || patchwork::forbidden();
            $this->data = (object) $this->entity->data;
        }
        else if ($this instanceof agent_pForm_entity_indexable)
        {
            $this->template = implode('/', $this->type) . '/index';
        }
        else if (!$this->entity)
        {
            patchwork::forbidden();
        }

    }

    function compose($o)
    {
        if ($this->data)
        {
            foreach ($this->data as $k => $v) is_scalar($v) && $o->$k = $v;

            if ($this instanceof agent_pForm_record_indexable) $o = $this->composeRecord($o);

            return parent::compose($o);
        }
        else
        {
            return $this instanceof agent_pForm_record_indexable
                ? $this->composeIndex($o)
                : parent::compose($o);
        }
    }

    protected function save($data)
    {
        $t = implode('_', $this->type);
        $this->setScalarData($data);

        $getId = 'get' . Doctrine\Common\Util\Inflector::classify("{$t}_id");
        $id = $this->entity->$getId();

        if (empty($this->data))
        {
            EM()->persist($this->entity);
            EM()->flush();

            $this->data = (object) array("{$t}_id" => $id);
        }
        else
        {
            EM()->flush();
        }

        return implode('/', $this->type) . "/{$id}";
    }

    protected function getEntityMetadata($entity)
    {
        $this->entityMetadata = EM()->getClassMetadata(get_class($entity));
    }

    protected function getEntityProperties()
    {
        return $this->entityMetadata->getColumnNames();
    }

    protected function getData()
    {
        $properties = $this->getEntityProperties();

        foreach ($properties as $p)
        {
            $getter = "get" . Doctrine\Common\Util\Inflector::classify($p);
            $this->entity->data[$p] = $this->entity->$getter();

            if ($this->entity->data[$p] instanceof DateTime)
                $this->entity->data[$p] = $this->entity->data[$p]->format('Y-m-d');
        }
    }

    protected function setScalarData($data)
    {
        foreach ($this->getEntityProperties() as $p)
        {
            // Test if the field if the identifier (entity_id)
            if (($this->entityMetadata->isIdentifier($p)))
                continue;

            // Test if the field is a date type, if true save it as a DateTime Object
            if ('date' == $this->entityMetadata->getTypeOfField($p))
                $data[$p] = new DateTime($data[$p]);

            $setter = "set" . Doctrine\Common\Util\Inflector::classify($p);
            $this->entity->$setter($data[$p]);
        }
    }
}

interface agent_pForm_entity_indexable
{
    function composeIndex($o);
    function composeRecord($o);
}