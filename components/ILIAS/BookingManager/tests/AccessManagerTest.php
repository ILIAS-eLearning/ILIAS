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

use ILIAS\BookingManager\Access\AccessManager;
use ILIAS\BookingManager\InternalDomainService;
use PHPUnit\Framework\TestCase;

/**
 * @author Tim Joussen <tim.joussen@databay.de>
 */
class AccessManagerTest extends TestCase
{
    protected function getAccessManager(
        ilAccessHandler $access,
        int $current_user_id = 7
    ): AccessManager {
        $user = $this->createMock(ilObjUser::class);
        $user->method("getId")->willReturn($current_user_id);

        $tree = $this->createMock(ilTree::class);

        $domain = $this->getMockBuilder(InternalDomainService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["user", "repositoryTree"])
            ->getMock();
        $domain->method("user")->willReturn($user);
        $domain->method("repositoryTree")->willReturn($tree);

        return new AccessManager($domain, $access);
    }

    public function testCanReadUsesReadPermissionOfUser(): void
    {
        $access = $this->createMock(ilAccessHandler::class);
        $access->expects($this->once())
            ->method("checkAccessOfUser")
            ->with(7, "read", "", 42)
            ->willReturn(true);

        $manager = $this->getAccessManager($access);

        $this->assertTrue($manager->canRead(42));
    }

    public function testCanReadReturnsFalseWhenReadIsDenied(): void
    {
        $access = $this->createMock(ilAccessHandler::class);
        $access->expects($this->once())
            ->method("checkAccessOfUser")
            ->with(7, "read", "", 42)
            ->willReturn(false);

        $manager = $this->getAccessManager($access);

        $this->assertFalse($manager->canRead(42));
    }

    public function testCanManageSettingsUsesWritePermissionOfUser(): void
    {
        $access = $this->createMock(ilAccessHandler::class);
        $access->expects($this->once())
            ->method("checkAccessOfUser")
            ->with(7, "write", "", 42)
            ->willReturn(true);

        $manager = $this->getAccessManager($access);

        $this->assertTrue($manager->canManageSettings(42));
    }
}
