<?php

use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationProvider;

require_once(__DIR__ . "/../BaseNotificationSetUp.php");

/**
 * Class DummyProviderTest
 */
class DummyProviderTest extends BaseNotificationSetUp
{
    public function testConstruct(): void
    {
        $povider = $this->getDummyNotificationsProviderWithNotifications([]);
        $this->assertInstanceOf(AbstractNotificationProvider::class, $povider);
    }
}
