<?php

use ILIAS\GlobalScreen\Scope\Notification\Factory\StandardNotification;
use ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer\StandardNotificationRenderer;

require_once(__DIR__ . "/../BaseNotificationSetUp.php");

/**
 * Class StandardNotificationTest
 */
class StandardNotificationTest extends BaseNotificationSetUp
{
    public function testConstructByFactory() : void
    {
        $standard_notification = $this->factory->standard($this->id);

        $this->assertInstanceOf(StandardNotification::class, $standard_notification);
        $this->assertEquals($this->id, $standard_notification->getProviderIdentification());
    }

    public function testWithNotificationItem()
    {
        $icon = $this->getUIFactory()->symbol()->icon()->standard("mail", "mail");
        $item = $this->getUIFactory()->item()->notification("hello", $icon);

        $standard_notification = $this->factory->standard($this->id)->withNotificationItem($item);
        $this->assertEquals($item, $standard_notification->getNotificationItem());
    }

    public function testWithNewAmout() : void
    {
        $standard_notification = $this->factory->standard($this->id);

        $this->assertEquals(1, $standard_notification->getNewAmount());
        $standard_notification = $standard_notification->withNewAmount(13);
        $this->assertEquals(13, $standard_notification->getNewAmount());
    }

    public function testWithOldAmout() : void
    {
        $standard_notification = $this->factory->standard($this->id);

        $this->assertEquals(0, $standard_notification->getOldAmount());
        $standard_notification = $standard_notification->withOldAmount(13);
        $this->assertEquals(13, $standard_notification->getOldAmount());
    }

    /**
     * Tests on AbstractBaseNotification
     */
    public function testGetProviderIdentification() : void
    {
        $standard_notification = $this->factory->standard($this->id);
        $this->assertEquals($this->id, $standard_notification->getProviderIdentification());
    }

    public function testGetRenderer() : void
    {
        $standard_notification = $this->factory->standard($this->id);
        $this->assertInstanceOf(
            StandardNotificationRenderer::class,
            $standard_notification->getRenderer($this->getUIFactory())
        );
    }

    public function testWithOpenedCallable() : void
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
    public function testWithClosedCallable() : void
    {
        $callable = function () {
            return "something";
        };
        $standard_notification = $this->factory->standard($this->id);
        $this->assertNull($standard_notification->getClosedCallable());
        $standard_notification = $standard_notification->withClosedCallable($callable);
        $this->assertEquals($callable, $standard_notification->getClosedCallable());
    }
    public function testHasClosedCallable() : void
    {
        $callable = function () {
            return "something";
        };
        $standard_notification = $this->factory->standard($this->id);
        $this->assertFalse($standard_notification->hasClosedCallable());
        $standard_notification = $standard_notification->withClosedCallable($callable);
        $this->assertTrue($standard_notification->hasClosedCallable());
    }
}
