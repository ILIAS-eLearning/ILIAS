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

use ILIAS\GlobalScreen\Scope\Notification\Collector\MainNotificationCollector;

require_once(__DIR__ . "/../BaseNotificationSetUp.php");

/**
 * Class MainNotificationCollectorTest
 */
class MainNotificationCollectorTest extends BaseNotificationSetUp
{
    public function testConstruct() : void
    {
        $povider = $this->getDummyNotificationsProviderWithNotifications([]);
        $collector = new MainNotificationCollector([$povider]);
        $this->assertInstanceOf(MainNotificationCollector::class, $collector);
    }


    public function testHasNotifications() : void
    {
        $povider = $this->getDummyNotificationsProviderWithNotifications([]);
        $collector = new MainNotificationCollector([$povider]);
        $this->assertFalse($collector->hasItems());

        $group_notification = $this->factory->standardGroup($this->id);
        $povider = $this->getDummyNotificationsProviderWithNotifications([$group_notification]);
        $collector = new MainNotificationCollector([$povider]);
        $this->assertTrue($collector->hasItems());
    }


    public function testGetNotifications() : void
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


    public function testGetAmountOfNewNotifications() : void
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
        $this->assertEquals(4, $collector->getAmountOfNewNotifications());
    }


    public function testGetAmountOfOldNotifications() : void
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
        $this->assertEquals(4, $collector->getAmountOfOldNotifications());
    }


    public function testGetNotificationsIdentifiersAsArray() : void
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
