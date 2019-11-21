<?php
use ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer\StandardNotificationRenderer;

require_once(__DIR__ . "/../../BaseNotificationSetUp.php");
const ILIAS_HTTP_PATH = "some_path";

/**
 * Class StandardNotificationTest
 */
class StandardNotificationRendererTest extends BaseNotificationSetUp
{
    public function testConstruct()
    {
        $renderer = new StandardNotificationRenderer($this->getUIFactory());
        $this->assertInstanceOf(StandardNotificationRenderer::class,$renderer);
    }

    public function testGetNotificationComponentForItem(){
        $renderer = new StandardNotificationRenderer($this->getUIFactory());
        $icon = $this->getUIFactory()->symbol()->icon()->standard("mail","mail");
        $item = $this->getUIFactory()->item()->notification("hello",$icon);

        $standard_notification = $this->factory->standard($this->id)->withNotificationItem($item);

        $this->assertEquals($item,$renderer->getNotificationComponentForItem($standard_notification));
    }

    public function testGetNotificationComponentForItemWithCloseCallable(){
        $renderer = new StandardNotificationRenderer($this->getUIFactory());
        $icon = $this->getUIFactory()->symbol()->icon()->standard("mail","mail");
        $item = $this->getUIFactory()->item()->notification("hello",$icon);

        $standard_notification = $this->factory->standard($this->id)
                                               ->withNotificationItem($item)
                                               ->withClosedCallable(function(){});

        $item = $item->withCloseAction("some_path/src/GlobalScreen/Client/notify.php?mode=closed&item_id=dummy");
        $this->assertEquals($item,$renderer->getNotificationComponentForItem($standard_notification));
    }
}