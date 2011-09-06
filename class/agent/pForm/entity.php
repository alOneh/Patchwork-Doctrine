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

    static protected $entityNs = 'Entities';

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

        $this->entityClass = self::$entityNs . "\\";

        foreach ($u as $u) $this->entityClass .= ucfirst ($u); //TODO: Ugly

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

    protected function formIsOk($f)
    {
        if (!parent::formIsOk($f)) return false;
        $this->entityIsNew && EM()->persist($this->entity);

        return true;
    }

    protected function save($data)
    {
        $this->setEntityData($data);

        EM()->flush();

        $id = $this->getEntityMetadata($this->entityClass);
        $id = $id->getSingleIdentifierFieldName();
        $id = 'get' . Doctrine\Common\Util\Inflector::classify($id);

        return $this->entityUrl . '/' . $this->entity->$id();
    }

    /**
     * Return the ClassMetadata of an Entity
     *
     * @return ClassMetadata
     */
    protected function getEntityMetadata($entityClass)
    {
        return EM()->getClassMetadata($entityClass);
    }

    /**
     * Return an array of the entity's values
     *
     * @return object $data
     */
    protected function getEntityData($entity = null)
    {
        $data = array();

        $entity || $entity = $this->entity;

        $p = $this->getEntityMetadata(get_class($entity))->getColumnNames();

        foreach ($p as $p)
        {
            $getProp = 'get' . Doctrine\Common\Util\Inflector::classify($p);

            if (method_exists($entity, $getProp))
            {
                $data[$p] = $entity->$getProp();

                if ($data[$p] instanceof DateTime)
                {
                    $data[$p . '_timestamp'] = $data[$p]->format('U');
                    $data[$p] = $data[$p]->format('c');
                }
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
        $meta = $this->getEntityMetadata($this->entityClass);
        $id = $meta->getSingleIdentifierFieldName();

        foreach ($data as $f => $v)
        {
            if (in_array($f, $meta->fieldNames) && $f !== $id)
            {
                $setter = 'set' . Doctrine\Common\Util\Inflector::classify($f);
                $this->entity->$setter($v);
            }
            else if (isset($meta->associationMappings[$f]))
            {
                $v || $v = null;

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
    protected function getAssociationLoop($assoc, Doctrine\ORM\Query $query = null)
    {
        $meta = $this->getEntityMetadata($this->entityClass);

        if (!$meta->hasAssociation($assoc))
            throw Doctrine\ORM\Mapping\MappingException::mappingNotFound($entity, $assoc);

        if (!$this->entityIsNew)
        {
            $assoc = $meta->associationMappings[$assoc];

            $id = $meta->getSingleIdentifierFieldName();
            $id = 'get' . Doctrine\Common\Util\Inflector::classify($id);

            if ($query === null)
            {
                $dql = EM()->createQueryBuilder();
                $dql->select('a')
                    ->from($assoc['targetEntity'], 'a');
                !$assoc['isOwningSide']
                    ? $dql->where("a.{$assoc['mappedBy']} = :id")
                    : $dql->where("a.{$meta->getSingleIdentifierFieldName()} = :id");
                $dql->setParameter('id', $this->entity->$id());
                $query = $dql->getQuery();
            }

            return !$meta->isSingleValuedAssociation($assoc['fieldName'])
                        ? new loop_array($query->getArrayResult(), 'filter_rawArray')
                        : $query->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        }

        return new loop_array(array());
    }

    protected function getRepository()
    {
        return EM()->getRepository($this->entityClass);
    }

    protected function getEntityAssociationData($o, $entity, $assoc)
    {
        $meta = $this->getEntityMetadata(get_class($entity));

        if (!$meta->hasAssociation($assoc))
            throw Doctrine\ORM\Mapping\MappingException::mappingNotFound($entity, $assoc);

        $getAssoc = 'get' . Doctrine\Common\Util\Inflector::classify($assoc);
        $data = $this->getEntityData($entity->$getAssoc());

        $assoc_mapping = $meta->getAssociationMapping($assoc);

        $id = $assoc_mapping['joinColumns'][0]['referencedColumnName'];

        $this->data->$assoc = $data->{$id};

        foreach ($data as $k => $v)
        {
            if ($id == $k)
            {
                $k = 'id';
            }

            $o->{"{$assoc}_{$k}"} = $v;
        }

        return $o;
    }

    function getDqlLoop($dql)
    {
        $dql = EM()->createQuery($dql);

        return new loop_array($dql->getArrayResult(), 'filter_rawArray');
    }
}

interface agent_pForm_entity_indexable
{
    function composeIndex($o);
    function composeEntity($o);
}