<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\DI;

/**
 * Provides fluid interface to RBAC services.
 */
class LoggingServices
{
    /**
     * @var	Container
     */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get interface to the global logger.
     *
     * @return	\ilLogger
     */
    public function root()
    {
        return $this->container["ilLoggerFactory"]->getRootLogger();
    }

    /**
     * Get a component logger.
     *
     * @return	\ilLogger
     */
    public function __call($method_name, $args)
    {
        assert(count($args) === 0);
        return $this->container["ilLoggerFactory"]->getLogger($method_name);
    }
}
