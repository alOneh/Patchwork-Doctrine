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
    $entityName;

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

            $this->entity || patchwork::forbidden();
            $this->data = (object) $this->getEntityValuesOf($this->entity);
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

            if ($this instanceof agent_pForm_entity_indexable) $o = $this->composeRecord($o);

            return parent::compose($o);
        }
        else
        {
            return $this instanceof agent_pForm_entity_indexable
                ? $this->composeIndex($o)
                : parent::compose($o);
        }
    }

    protected function save($data)
    {
        $t = implode('_', $this->type);
        $this->setScalarData($this->entity, $data);

        $getId = 'get' . Doctrine\Common\Util\Inflector::classify("{$t}_id");

        if (empty($this->data))
        {
            EM()->persist($this->entity);
            EM()->flush();

            $this->data = (object) array("{$t}_id" => $this->entity->$getId());
        }
        else
        {
            EM()->flush();
        }

        return implode('/', $this->type) . "/{$this->entity->$getId()}";
    }

    /**
     * Return the ClassMetadata of an Entity
     *
     * @param Entity $entity
     * @return ClassMetadata
     */
    protected function getEntityMetadata($entity)
    {
        return EM()->getClassMetadata(get_class($entity));
    }

    /**
     * Return an array of the entity's values
     *
     * @param Entity $entity
     * @return array $data
     */
    protected function getEntityValuesOf($entity)
    {
        $data = array();

        $properties = $this->getEntityMetadata($entity)->getColumnNames();

        foreach ($properties as $p)
        {
            $getter = "get" . Doctrine\Common\Util\Inflector::classify($p);
            $data[$p] = $entity->$getter();

            if ($data[$p] instanceof DateTime)
            {
                $date = $data[$p];
                $data[$p] = $date->format('c');
                $data[$p . '_timestamp'] = $date->format('U');
            }
        }

        return $data;
    }

    /**
     * Provide set* method foreach entity columns
     *
     * @param Entity $entity
     * @param array $data
     */
    protected function setScalarData($entity, $data)
    {
        $properties = $this->getEntityMetadata($entity)->getColumnNames();

        foreach ($properties as $p)
        {
            // Test if the field if the identifier (entity_id)
            if ($this->getEntityMetadata($entity)->isIdentifier($p))
                continue;

            // Test if the field is a date type, if true save it as a DateTime Object
            if ('date' == $this->getEntityMetadata($entity)->getTypeOfField($p))
                $data[$p] = new DateTime($data[$p]);

            $setter = "set" . Doctrine\Common\Util\Inflector::classify($p);
            $this->entity->$setter($data[$p]);
        }
    }

    /**
     * Return a loop_array of the association mapping of an entity
     *
     * @param Entity $entity
     * @param string $assoc
     * @return loop_array
     */
    public function loadAssociation($entity, $assoc)
    {
        $entityMetadata = $this->getEntityMetadata($entity);

        if ($entityMetadata->hasAssociation($assoc))
        {
            $identifier = $entityMetadata->getTableName();

            $dql = "SELECT a
                    FROM {$entityMetadata->getAssociationTargetClass($assoc)} a
                    WHERE a.{$identifier} = ?1";
            $dql = EM()->createQuery($dql);
            $dql->setParameter(1, $this->get->__1__);

            return new loop_array($dql->getArrayResult(), 'filter_rawArray');
        }
        else
            throw Doctrine\ORM\Mapping\MappingException::mappingNotFound($entity, $assoc);
    }
}

interface agent_pForm_entity_indexable
{
    function composeIndex($o);
    function composeRecord($o);
}