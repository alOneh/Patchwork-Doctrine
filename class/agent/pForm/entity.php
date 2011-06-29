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

    $entityUrl,
    $entityClass,
    $entity,
    $entityIsNew = false;


    function control()
    {
        parent::control();

        if ($this->entity) return;

        if (empty($this->entityUrl))
        {
            $u = explode('_', substr(get_class($this), 6));

            if ('new' === end($u))
            {
                $this->entityIsNew = true;
                array_pop($u);
            }

            $this->entityUrl = implode('/', $u);
        }

        $this->entityClass = 'Entities\\' . str_replace('/', '', ucwords($this->entityUrl));

        if (!empty($this->get->__1__))
        {
            $this->entity = EM()->find($this->entityClass, $this->get->__1__);
            $this->entity || patchwork::forbidden();
        }
        else if ($this->entityIsNew)
        {
            $this->entity = new $this->entityClass;
        }
        else if ($this instanceof agent_pForm_entity_indexable)
        {
            $this->template = $this->entityUrl . '/index';
        }
        else patchwork::forbidden();
    }

    function compose($o)
    {
        if (empty($this->entity))
        {
            return $this->composeIndex($o);
        }
        else
        {
            if (!$this->entityIsNew)
            {
                $this->data = $this->getEntityData();
                foreach ($this->data as $k => $v) $o->$k = $v;
            }

            if ($this instanceof agent_pForm_entity_indexable)
            {
                $o = $this->composeEntity($o);
            }

            return parent::compose($o);
        }
    }

    protected function save($data)
    {
        $this->setEntityData($data);

        $this->entityIsNew && EM()->persist($this->entity);
        EM()->flush();

        $id = $this->getEntityMetadata();
        $id = $id->getSingleIdentifierFieldName();
        $id = 'get' . Doctrine\Common\Util\Inflector::classify($id);

        return $this->entityUrl . '/' . $this->entity->$id();
    }

    /**
     * Return the ClassMetadata of an Entity
     *
     * @return ClassMetadata
     */
    protected function getEntityMetadata()
    {
        return EM()->getClassMetadata($this->entityClass);
    }

    /**
     * Return an array of the entity's values
     *
     * @return object $data
     */
    protected function getEntityData()
    {
        $data = array();

        $p = $this->getEntityMetadata()->getColumnNames();

        foreach ($p as $p)
        {
            $getProp = 'get' . Doctrine\Common\Util\Inflector::classify($p);
            $data[$p] = $this->entity->$getProp();

            if ($data[$p] instanceof DateTime)
            {
                $data[$p . '_timestamp'] = $data[$p]->format('U');
                $data[$p] = $data[$p]->format('c');
            }
        }

        return (object) $data;
    }

    /**
     * Inject data with entity's setters
     *
     * @param array $data
     */
    protected function setEntityData($data)
    {
        $meta = $this->getEntityMetadata();
        $id = $meta->getSingleIdentifierFieldName();

        foreach ($data as $f => $v)
        {
            if (in_array($f, $meta->fieldNames) && $f !== $id)
            {
                $setter = 'set' . Doctrine\Common\Util\Inflector::classify($f);
                $this->entity->$setter($v);
            }
            else if (isset($meta->associationMappings[$f]) && $v !== null)
            {
                $assocTargetEntity = $meta->associationMappings[$f]['targetEntity'];

                if ($v instanceof $assocTargetEntity)
                {
                    $v = $v;
                }
                else
                {
                    $v = EM()->getReference($assocTargetEntity, $v);
                }

                $setter = 'set' . Doctrine\Common\Util\Inflector::classify($f);
                $this->entity->$setter($v);
            }
        }
    }

    /**
     * Return a loop of the association mapping of an entity
     *
     * @param string $assoc
     * @return loop
     */
    protected function getAssociationLoop($assoc)
    {
        $meta = $this->getEntityMetadata();

        if (!$meta->hasAssociation($assoc))
            throw Doctrine\ORM\Mapping\MappingException::mappingNotFound($entity, $assoc);

        if (!$this->entityIsNew)
        {
            $assoc = $meta->associationMappings[$assoc];

            $id = $meta->getSingleIdentifierFieldName();
            $id = 'get' . Doctrine\Common\Util\Inflector::classify($id);

            $dql = "SELECT a
                    FROM {$assoc['targetEntity']} a
                    WHERE a.{$assoc['mappedBy']} = ?1";E($assoc);
            $dql = EM()->createQuery($dql);
            $dql->setParameter(1, $this->entity->$id());

            return new loop_array($dql->getArrayResult(), 'filter_rawArray');
        }

        return new loop_array(array());
    }
}

interface agent_pForm_entity_indexable
{
    function composeIndex($o);
    function composeEntity($o);
}