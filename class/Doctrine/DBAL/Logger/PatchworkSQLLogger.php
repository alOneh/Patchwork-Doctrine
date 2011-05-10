<?php

namespace Doctrine\DBAL\Logger;

use Doctrine\DBAL\Logging\SQLLogger;

/**
 * A SQL logger that logs queries and parameters to the Patchwork debug window.
 * 
 */
class PatchworkSQLLogger implements SQLLogger
{
    protected $query;

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        E(array(
            'sql'    => $sql,
            'params' => $params,
            'types'  => $types
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
        E('End of query');
    }
}
