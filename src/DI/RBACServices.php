<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\DI;

/**
 * Provides fluid interface to RBAC services.
 */
class RBACServices
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
     * Get the interface to the RBAC system.
     *
     * @return	\ilRbacSystem
     */
    public function system()
    {
        return $this->container["rbacsystem"];
    }

    /**
     * Get the interface to insert relations into the RBAC system.
     *
     * @return	\ilRbacAdmin
     */
    public function admin()
    {
        return $this->container["rbacadmin"];
    }

    /**
     * Get the interface to query the RBAC system.
     *
     * @return	\ilRbacReview
     */
    public function review()
    {
        return $this->container["rbacreview"];
    }
}
