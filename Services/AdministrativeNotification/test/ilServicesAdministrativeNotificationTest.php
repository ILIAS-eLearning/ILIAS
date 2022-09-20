<?php

declare(strict_types=1);

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

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

class ilServicesAdministrativeNotificationTest extends TestCase
{
    private ?\ILIAS\DI\Container $dic_backup;
    /**
     * @var ilRbacReview|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $rbacreview_mock;

    protected function setUp(): void
    {
        global $DIC;
        $this->dic_backup = is_object($DIC) ? clone $DIC : $DIC;

        $DIC = new Container();
        $DIC['ilDB'] = $this->createMock(ilDBInterface::class);
        $this->rbacreview_mock = $DIC['rbacreview'] = $this->createMock(ilRbacReview::class);
    }

    protected function tearDown(): void
    {
        global $DIC;
        $DIC = $this->dic_backup;
    }

    public function testBasisc(): void
    {
        $notification = new ilADNNotification();
        $this->assertEquals(0, $notification->getId());

        $notification->setTitle('Title');
        $notification->setActive(true);
        $notification->setType(ilADNNotification::TYPE_WARNING);
        $notification->setTypeDuringEvent(ilADNNotification::TYPE_ERROR);

        $this->assertTrue($notification->isActive());
        $this->assertFalse($notification->isDuringEvent());
    }

    public function testVisibilityByDate(): void
    {
        $notification = new ilADNNotification();
        $user_mock = $this->createMock(ilObjUser::class);
        $user_mock->expects($this->atLeast(1))
                  ->method('getId')
                  ->willReturn(42);

        $notification->setPermanent(true);
        $this->assertTrue($notification->isVisibleForUser($user_mock));

        $notification->setPermanent(false);

        $notification->setDisplayStart(new DateTimeImmutable("-2 hours"));
        $notification->setEventStart(new DateTimeImmutable("-1 hours"));
        $notification->setDisplayEnd(new DateTimeImmutable("+2 hours"));
        $notification->setEventEnd(new DateTimeImmutable("+1 hours"));
        $this->assertTrue($notification->isVisibleForUser($user_mock));

        $notification->setDisplayStart(new DateTimeImmutable("+1 hours"));
        $notification->setEventStart(new DateTimeImmutable("+2 hours"));
        $notification->setDisplayEnd(new DateTimeImmutable("+4 hours"));
        $notification->setEventEnd(new DateTimeImmutable("+3 hours"));
        $this->assertFalse($notification->isVisibleForUser($user_mock));
    }

    public function testVisibilityByRole(): void
    {
        $notification = new ilADNNotification();
        $user_mock = $this->createMock(ilObjUser::class);
        $user_mock->expects($this->atLeast(1))
                  ->method('getId')
                  ->willReturn(42);

        $notification->setPermanent(true);
        $notification->setLimitToRoles(true);
        $notification->setLimitedToRoleIds([2, 22, 222]);

        $this->rbacreview_mock->expects($this->once())
                              ->method('isAssignedToAtLeastOneGivenRole')
                              ->with(42, [2, 22, 222])
                              ->willReturn(true);

        $this->assertTrue($notification->isVisibleForUser($user_mock));
    }

    public function testVisibilityByRoleNotGranted(): void
    {
        $notification = new ilADNNotification();
        $user_mock = $this->createMock(ilObjUser::class);
        $user_mock->expects($this->atLeast(1))
                  ->method('getId')
                  ->willReturn(42);

        $notification->setPermanent(true);
        $notification->setLimitToRoles(true);
        $notification->setLimitedToRoleIds([2, 22, 222]);

        $this->rbacreview_mock->expects($this->once())
                              ->method('isAssignedToAtLeastOneGivenRole')
                              ->with(42, [2, 22, 222])
                              ->willReturn(false);

        $this->assertFalse($notification->isVisibleForUser($user_mock));
    }
}
