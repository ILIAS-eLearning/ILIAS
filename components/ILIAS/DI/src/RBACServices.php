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
 * @deprecated use ilAccess whenever possible. The legacy ilRbacReview, ilRbacSystem
 *             and ilRbacAdmin will become internal only in the near future. They are
 *             currently only exposed because many places use them via global $DIC.
 */
class RBACServices
{
    public function __construct(
        private readonly \ilRbacReview $review,
        private readonly \ilRbacSystem $system,
        private readonly \ilRbacAdmin $admin,
    ) {
    }

    /**
     * Get the interface to the RBAC system.
     */
    public function system(): \ilRbacSystem
    {
        return $this->system;
    }

    /**
     * Get the interface to insert relations into the RBAC system.
     */
    public function admin(): \ilRbacAdmin
    {
        return $this->admin;
    }

    /**
     * Get the interface to query the RBAC system.
     */
    public function review(): \ilRbacReview
    {
        return $this->review;
    }
}
