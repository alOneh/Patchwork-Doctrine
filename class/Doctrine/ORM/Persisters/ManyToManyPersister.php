<?php

namespace Doctrine\ORM\Persisters;

use Doctrine\ORM\PersistentCollection;

class ManyToManyPersister extends self
{
    protected function _getDeleteRowSQLParameters(PersistentCollection $coll, $element)
    {
        return $this->_collectJoinTableColumnParameters($coll, $element);
    }

    protected function _getInsertRowSQLParameters(PersistentCollection $coll, $element)
    {
        return $this->_collectJoinTableColumnParameters($coll, $element);
    }

    private function _collectJoinTableColumnParameters(PersistentCollection $coll, $element)
    {
        $params = array();
        $mapping = $coll->getMapping();

        $identifier1 = $this->_uow->getEntityIdentifier($coll->getOwner());
        $identifier2 = $this->_uow->getEntityIdentifier($element);

        foreach ($mapping['joinTableColumns'] as $joinTableColumn)
        {
            if (isset($mapping['relationToSourceKeyColumns'][$joinTableColumn]))
            {
                if (1 === count($identifier1))
                {
                    $params[] = current($identifier1);
                } else
                {
                    if (!isset($class1))
                    {
                        $class1 = $this->_em->getClassMetadata(get_class($coll->getOwner()));
                    }
                    if ($class1->containsForeignIdentifier)
                    {
                        $params[] = $identifier1[$class1->getFieldForColumn($mapping['relationToSourceKeyColumns'][$joinTableColumn])];
                    } else
                    {
                        $params[] = $identifier1[$class1->fieldNames[$mapping['relationToSourceKeyColumns'][$joinTableColumn]]];
                    }
                }
            } else
            {
                if (1 === count($identifier2))
                {
                    $params[] = current($identifier2);
                } else
                {
                    if (!isset($class2))
                    {
                        $class2 = $coll->getTypeClass();
                    }
                    if ($class2->containsForeignIdentifier)
                    {
                        $params[] = $identifier2[$class2->getFieldForColumn($mapping['relationToTargetKeyColumns'][$joinTableColumn])];
                    } else
                    {
                        $params[] = $identifier2[$class2->fieldNames[$mapping['relationToTargetKeyColumns'][$joinTableColumn]]];
                    }
                }
            }
        }

        return $params;
    }
}