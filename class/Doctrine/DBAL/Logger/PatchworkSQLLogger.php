<?php

namespace Doctrine\DBAL\Logger;

use Doctrine\DBAL\Logging\SQLLogger;

/**
 * A SQL logger that logs to the Patchwork debug bar, Doctrine query.
 * 
 */
class PatchworkSQLLogger implements SQLLogger
{
    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        E($sql, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
    }
}