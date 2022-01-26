<?php

namespace ILIAS\Services\Logging;


/**
 * Provides fluid interface to RBAC services.
 */
interface LoggingServicesInterface
{
    /**
     * Get interface to the global logger.
     */
    public function root();
}
