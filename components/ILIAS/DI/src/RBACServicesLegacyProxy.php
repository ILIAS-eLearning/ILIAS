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

namespace ILIAS\DI;

/**
 * Serves the RBAC services from the legacy container offsets.
 *
 * A bootstrapped installation gets its RBAC services from AccessControl, and
 * `AllModernComponents` registers them under {@see RBACServices}. Where that
 * did not happen - unit tests that populate only the single offsets they need -
 * this proxy answers from `rbacreview`, `rbacsystem` and `rbacadmin` instead,
 * one at a time, so a caller only has to provide what it actually asks for.
 *
 * It deliberately does not call the parent constructor: the three services are
 * resolved per call, not held. Every getter of {@see RBACServices} is therefore
 * overridden here, and any new one has to be as well.
 *
 * @deprecated goes away once nothing populates the three offsets by hand
 *             anymore.
 */
final class RBACServicesLegacyProxy extends RBACServices
{
    public function __construct(private readonly Container $container)
    {
    }

    public function review(): \ilRbacReview
    {
        return $this->container['rbacreview'];
    }

    public function system(): \ilRbacSystem
    {
        return $this->container['rbacsystem'];
    }

    public function admin(): \ilRbacAdmin
    {
        return $this->container['rbacadmin'];
    }
}
