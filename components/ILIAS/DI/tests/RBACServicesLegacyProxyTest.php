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

use PHPUnit\Framework\TestCase;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class RBACServicesLegacyProxyTest extends TestCase
{
    public function testARegisteredServiceIsPreferred(): void
    {
        $container = new Container();
        $services = new RBACServices(
            $this->createMock(\ilRbacReview::class),
            $this->createMock(\ilRbacSystem::class),
            $this->createMock(\ilRbacAdmin::class),
        );
        $container[RBACServices::class] = static fn(): RBACServices => $services;

        $this->assertSame($services, $container->rbac());
    }

    public function testTheLegacyOffsetsAnswerWithoutARegisteredService(): void
    {
        $container = new Container();
        $review = $this->createMock(\ilRbacReview::class);
        $system = $this->createMock(\ilRbacSystem::class);
        $admin = $this->createMock(\ilRbacAdmin::class);
        $container['rbacreview'] = static fn(): \ilRbacReview => $review;
        $container['rbacsystem'] = static fn(): \ilRbacSystem => $system;
        $container['rbacadmin'] = static fn(): \ilRbacAdmin => $admin;

        $this->assertInstanceOf(RBACServicesLegacyProxy::class, $container->rbac());
        $this->assertSame($review, $container->rbac()->review());
        $this->assertSame($system, $container->rbac()->system());
        $this->assertSame($admin, $container->rbac()->admin());
    }

    /**
     * Tests commonly register only the offset they are about, so asking for one
     * service must not resolve the other two.
     */
    public function testOnlyTheRequestedOffsetIsResolved(): void
    {
        $container = new Container();
        $system = $this->createMock(\ilRbacSystem::class);
        $container['rbacsystem'] = static fn(): \ilRbacSystem => $system;

        $this->assertSame($system, $container->rbac()->system());
    }

    /**
     * Every getter of RBACServices has to be overridden, since the proxy holds
     * no services of its own.
     */
    public function testEveryServiceGetterIsOverridden(): void
    {
        $overridden = array_map(
            static fn(\ReflectionMethod $method): string => $method->getName(),
            (new \ReflectionClass(RBACServicesLegacyProxy::class))->getMethods(\ReflectionMethod::IS_PUBLIC)
        );

        foreach ((new \ReflectionClass(RBACServices::class))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isConstructor()) {
                continue;
            }
            $this->assertContains($method->getName(), $overridden);
        }
    }
}
