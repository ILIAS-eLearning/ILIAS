<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\DI;

/**
 * Provides fluid interface to RBAC services.
 */
class RBACServices
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get the interface to the RBAC system.
     */
    public function system(): \ilRbacSystem
    {
        return $this->container["rbacsystem"];
    }

    /**
     * Get the interface to insert relations into the RBAC system.
     */
    public function admin(): \ilRbacAdmin
    {
        return $this->container["rbacadmin"];
    }

    /**
     * Get the interface to query the RBAC system.
     */
    public function review(): \ilRbacReview
    {
        return $this->container["rbacreview"];
    }
}
