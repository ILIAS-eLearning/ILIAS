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

declare(strict_types=1);

namespace ILIAS\AccessControl\PublicInterface;

/**
 * Default {@see RBAC} implementation.
 *
 * Holds the three legacy RBAC services and exposes them via getters. This is
 * what `AllModernComponents` plugs into the legacy `$DIC` offsets.
 */
class DefaultRBAC implements RBAC
{
    public function __construct(
        private readonly \ilRbacReview $review,
        private readonly \ilRbacSystem $system,
        private readonly \ilRbacAdmin $admin,
    ) {
    }

    public function review(): \ilRbacReview
    {
        return $this->review;
    }

    public function system(): \ilRbacSystem
    {
        return $this->system;
    }

    public function admin(): \ilRbacAdmin
    {
        return $this->admin;
    }
}
