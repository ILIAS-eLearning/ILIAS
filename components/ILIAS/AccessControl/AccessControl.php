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

namespace ILIAS;

use ILIAS\AccessControl\PublicInterface\Access;
use ILIAS\AccessControl\PublicInterface\RBAC;
use ILIAS\AccessControl\PublicInterface\DefaultRBAC;
use ILIAS\AccessControl\User\UserIdProviderProxy;
use ILIAS\AccessControl\Tree\RepositoryTreeAccessProxy;
use ILIAS\AccessControl\Object\ObjectDataAccessProxy;
use ILIAS\AccessControl\Object\ObjectDefinitionAccessProxy;
use ILIAS\Database\PDO\External;
use ILIAS\HTTP\GlobalHttpState;

class AccessControl implements Component\Component
{
    public function init(
        array | \ArrayAccess &$define,
        array | \ArrayAccess &$implement,
        array | \ArrayAccess &$use,
        array | \ArrayAccess &$contribute,
        array | \ArrayAccess &$seek,
        array | \ArrayAccess &$provide,
        array | \ArrayAccess &$pull,
        array | \ArrayAccess &$internal,
    ): void {
        // Public surface
        $define[] = Access::class;
        $define[] = RBAC::class;

        // Internal proxies (concrete classes — no interfaces)
        $internal[UserIdProviderProxy::class] = static fn(): UserIdProviderProxy => new UserIdProviderProxy();
        $internal[RepositoryTreeAccessProxy::class] = static fn(): RepositoryTreeAccessProxy => new RepositoryTreeAccessProxy();
        $internal[ObjectDataAccessProxy::class] = static fn(): ObjectDataAccessProxy => new ObjectDataAccessProxy();
        $internal[ObjectDefinitionAccessProxy::class] = static fn(): ObjectDefinitionAccessProxy => new ObjectDefinitionAccessProxy();

        // Internal RBAC services (legacy concrete classes)
        $internal[\ilRbacReview::class] = static fn(): \ilRbacReview => new \ilRbacReview(
            $use[External::class],
            $use[\ILIAS\Logging\Logger\LoggerFactoryInterface::class]->getLazy('ac'),
        );

        $internal[\ilRbacSystem::class] = static fn(): \ilRbacSystem => new \ilRbacSystem(
            $internal[UserIdProviderProxy::class],
            $use[External::class],
            $internal[\ilRbacReview::class],
            $internal[RepositoryTreeAccessProxy::class],
            $use[GlobalHttpState::class],
            $pull[\ILIAS\Refinery\Factory::class],
            $internal[ObjectDataAccessProxy::class],
        );

        $internal[\ilRbacAdmin::class] = static fn(): \ilRbacAdmin => new \ilRbacAdmin(
            $use[External::class],
            $internal[\ilRbacReview::class],
            $use[\ILIAS\Logging\Logger\LoggerFactoryInterface::class]->getLazy('ac'),
        );

        // Public implementations (in case of RBAC for legacy reasons only)
        $implement[RBAC::class] = static fn(): RBAC => new DefaultRBAC(
            $internal[\ilRbacReview::class],
            $internal[\ilRbacSystem::class],
            $internal[\ilRbacAdmin::class],
        );

        $implement[Access::class] = static fn(): Access => new \ilAccess(
            $internal[UserIdProviderProxy::class],
            $use[External::class],
            $internal[\ilRbacSystem::class],
            $internal[RepositoryTreeAccessProxy::class],
            $internal[ObjectDefinitionAccessProxy::class],
            $use[\ILIAS\Logging\Logger\LoggerFactoryInterface::class]->getLazy('ac'),
        );

        // Setup agents and assets
        $contribute[\ILIAS\Setup\Agent::class] = static fn() =>
            new \ilAccessControlSetupAgent(
                $pull[\ILIAS\Refinery\Factory::class]
            );
        $contribute[\ILIAS\Setup\Agent::class] = static fn() =>
            new \ilAccessRBACSetupAgent(
                $pull[\ILIAS\Refinery\Factory::class]
            );
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "ilPermSelect.js");
    }
}
