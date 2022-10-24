<?php

use ILIAS\GlobalScreen\Scope\Notification\Factory\StandardNotificationGroup;
use ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer\StandardNotificationGroupRenderer;

require_once(__DIR__ . "/../BaseNotificationSetUp.php");

/**
 * Class StandardNotificationTest
 */
class GroupNotificationTest extends BaseNotificationSetUp
{
    public function testConstructByFactory(): void
    {
        $group_notification = $this->factory->standardGroup($this->id);

        $this->assertInstanceOf(StandardNotificationGroup::class, $group_notification);
        $this->assertEquals($this->id, $group_notification->getProviderIdentification());
    }

    public function testWitTitle(): void
    {
        $group_notification = $this->factory->standardGroup($this->id)->withTitle("test");
        $this->assertEquals("test", $group_notification->getTitle());
    }

    public function testAddNotification(): void
    {
        $group_notification = $this->factory->standardGroup($this->id);
        $this->assertEquals([], $group_notification->getNotifications());
        $standard_notification = $this->factory->standard($this->id);
        $group_notification->addNotification($standard_notification);
        $this->assertEquals([$standard_notification], $group_notification->getNotifications());
        $group_notification->addNotification($standard_notification);
        $this->assertEquals([$standard_notification,$standard_notification], $group_notification->getNotifications());
    }

    public function testNotificationCount(): void
    {
        $group_notification = $this->factory->standardGroup($this->id);
        $this->assertEquals(0, $group_notification->getNotificationsCount());
        $standard_notification = $this->factory->standard($this->id);
        $group_notification->addNotification($standard_notification);
        $this->assertEquals(1, $group_notification->getNotificationsCount());
        $group_notification->addNotification($standard_notification);
        $this->assertEquals(2, $group_notification->getNotificationsCount());
    }

    public function testNewNotificationCount(): void
    {
        $group_notification = $this->factory->standardGroup($this->id);
        $this->assertEquals(0, $group_notification->getNewNotificationsCount());
        $standard_notification = $this->factory->standard($this->id)->withNewAmount(3);
        $group_notification->addNotification($standard_notification);
        $this->assertEquals(3, $group_notification->getNewNotificationsCount());
        $group_notification->addNotification($standard_notification);
        $this->assertEquals(6, $group_notification->getNewNotificationsCount());
    }

    public function testOldNotificationCount(): void
    {
        $group_notification = $this->factory->standardGroup($this->id);
        $this->assertEquals(0, $group_notification->getOldNotificationsCount());
        $standard_notification = $this->factory->standard($this->id)->withOldAmount(3);
        $group_notification->addNotification($standard_notification);
        $this->assertEquals(3, $group_notification->getOldNotificationsCount());
        $group_notification->addNotification($standard_notification);
        $this->assertEquals(6, $group_notification->getOldNotificationsCount());
    }

    /**
     * Tests on AbstractBaseNotification
     */
    public function testGetProviderIdentification(): void
    {
        $standard_notification = $this->factory->standard($this->id);
        $this->assertEquals($this->id, $standard_notification->getProviderIdentification());
    }

    public function testGetRenderer(): void
    {
        $group_notification = $this->factory->standardGroup($this->id);
        $this->assertInstanceOf(
            StandardNotificationGroupRenderer::class,
            $group_notification->getRenderer($this->getUIFactory())
        );
    }

    public function testWithOpenedCallable(): void
    {
        $callable = function () {
            return "something";
        };
        $standard_notification = $this->factory->standard($this->id);
        $this->assertEquals(function () {
        }, $standard_notification->getOpenedCallable());
        $standard_notification = $standard_notification->withOpenedCallable($callable);
        $this->assertEquals($callable, $standard_notification->getOpenedCallable());
    }
}
