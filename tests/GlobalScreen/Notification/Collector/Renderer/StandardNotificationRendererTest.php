<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer\StandardNotificationRenderer;

require_once(__DIR__ . "/../../BaseNotificationSetUp.php");

/**
 * Class StandardNotificationTest
 */
class StandardNotificationRendererTest extends BaseNotificationSetUp
{
    use Hasher;


    protected function setUp() : void
    {
        parent::setUp();
        if (!defined("ILIAS_HTTP_PATH")) {
            define("ILIAS_HTTP_PATH", "http://localhost");
        }
    }

    public function testConstruct()
    {
        $renderer = new StandardNotificationRenderer($this->getUIFactory());
        $this->assertInstanceOf(StandardNotificationRenderer::class, $renderer);
    }


    public function testGetNotificationComponentForItem()
    {
        $renderer = new StandardNotificationRenderer($this->getUIFactory());
        $icon = $this->getUIFactory()->symbol()->icon()->standard("mail", "mail");
        $item = $this->getUIFactory()->item()->notification("hello", $icon);

        $standard_notification = $this->factory->standard($this->id)->withNotificationItem($item);

        $this->assertEquals($item, $renderer->getNotificationComponentForItem($standard_notification));
    }


    public function testGetNotificationComponentForItemWithCloseCallable()
    {
        $renderer = new StandardNotificationRenderer($this->getUIFactory());
        $icon = $this->getUIFactory()->symbol()->icon()->standard("mail", "mail");
        $item = $this->getUIFactory()->item()->notification("hello", $icon);

        $standard_notification = $this->factory->standard($this->id)
            ->withNotificationItem($item)
            ->withClosedCallable(function () {
            });

        $item = $item->withCloseAction("http://localhost/src/GlobalScreen/Client/notify.php?mode=closed&item_id=" . $this->hash($this->id->serialize()));
        $this->assertEquals($item, $renderer->getNotificationComponentForItem($standard_notification));
    }
}
