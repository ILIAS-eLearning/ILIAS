<?php

use ILIAS\GlobalScreen\Scope\Notification\Collector\MainNotificationCollector;

require_once(__DIR__ . "/../BaseNotificationSetUp.php");

/**
 * Class MainNotificationCollectorTest
 */
class MainNotificationCollectorTest extends BaseNotificationSetUp
{
    public function testConstruct()
    {
        $povider = $this->getDummyNotificationsProviderWithNotifications([]);
        $collector = new MainNotificationCollector([$povider]);
        $this->assertInstanceOf(MainNotificationCollector::class, $collector);
    }


    public function testHasNotifications()
    {
        $povider = $this->getDummyNotificationsProviderWithNotifications([]);
        $collector = new MainNotificationCollector([$povider]);
        $this->assertFalse($collector->hasItems());

        $group_notification = $this->factory->standardGroup($this->id);
        $povider = $this->getDummyNotificationsProviderWithNotifications([$group_notification]);
        $collector = new MainNotificationCollector([$povider]);
        $this->assertTrue($collector->hasItems());
    }


    public function testGetNotifications()
    {
        $povider = $this->getDummyNotificationsProviderWithNotifications([]);
        $collector = new MainNotificationCollector([$povider]);
        $this->assertEquals([], $collector->getNotifications());

        $group_notification = $this->factory->standardGroup($this->id);
        $povider = $this->getDummyNotificationsProviderWithNotifications([$group_notification]);
        $collector = new MainNotificationCollector([$povider]);
        $this->assertEquals([$group_notification], $collector->getNotifications());

        $group_notification = $this->factory->standardGroup($this->id);
        $povider = $this->getDummyNotificationsProviderWithNotifications([$group_notification, $group_notification]);
        $collector = new MainNotificationCollector([$povider]);
        $this->assertEquals([$group_notification, $group_notification], $collector->getNotifications());
    }


    public function testGetAmountOfNewNotifications()
    {
        $povider = $this->getDummyNotificationsProviderWithNotifications([]);
        $collector = new MainNotificationCollector([$povider]);
        $this->assertEquals(0, $collector->getAmountOfNewNotifications());

        $group_notification = $this->factory->standardGroup($this->id);
        $povider = $this->getDummyNotificationsProviderWithNotifications([$group_notification]);
        $collector = new MainNotificationCollector([$povider]);
        $this->assertEquals(0, $collector->getAmountOfNewNotifications());

        $group_notification = $this->factory->standardGroup($this->id);
        $standard_notification = $this->factory->standard($this->id)->withNewAmount(3);
        $group_notification->addNotification($standard_notification);
        $group_notification->addNotification($standard_notification);

        $povider = $this->getDummyNotificationsProviderWithNotifications([$group_notification, $group_notification]);
        $collector = new MainNotificationCollector([$povider]);
        $this->assertEquals(12, $collector->getAmountOfNewNotifications());
    }


    public function testGetAmountOfOldNotifications()
    {
        $povider = $this->getDummyNotificationsProviderWithNotifications([]);
        $collector = new MainNotificationCollector([$povider]);
        $this->assertEquals(0, $collector->getAmountOfOldNotifications());

        $group_notification = $this->factory->standardGroup($this->id);
        $povider = $this->getDummyNotificationsProviderWithNotifications([$group_notification]);
        $collector = new MainNotificationCollector([$povider]);
        $this->assertEquals(0, $collector->getAmountOfOldNotifications());

        $group_notification = $this->factory->standardGroup($this->id);
        $standard_notification = $this->factory->standard($this->id)->withOldAmount(3);
        $group_notification->addNotification($standard_notification);
        $group_notification->addNotification($standard_notification);

        $povider = $this->getDummyNotificationsProviderWithNotifications([$group_notification, $group_notification]);
        $collector = new MainNotificationCollector([$povider]);
        $this->assertEquals(12, $collector->getAmountOfOldNotifications());
    }


    public function testGetNotificationsIdentifiersAsArray()
    {
        $provider = $this->getDummyNotificationsProviderWithNotifications([]);
        $collector = new MainNotificationCollector([$provider]);

        $this->assertEquals([], $collector->getNotificationsIdentifiersAsArray());

        $group_notification = $this->factory->standardGroup($this->id);
        $provider = $this->getDummyNotificationsProviderWithNotifications([$group_notification]);
        $collector = new MainNotificationCollector([$provider]);

        $this->assertEquals([$this->id->serialize()], $collector->getNotificationsIdentifiersAsArray());

        $group_notification = $this->factory->standardGroup($this->id);
        $provider = $this->getDummyNotificationsProviderWithNotifications([$group_notification, $group_notification]);
        $collector = new MainNotificationCollector([$provider]);

        $this->assertEquals([$this->id->serialize(), $this->id->serialize()], $collector->getNotificationsIdentifiersAsArray());
    }
}
