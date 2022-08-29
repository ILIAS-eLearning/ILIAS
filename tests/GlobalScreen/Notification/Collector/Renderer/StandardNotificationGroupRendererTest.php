<?php

use ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer\StandardNotificationGroupRenderer;

require_once(__DIR__ . "/../../BaseNotificationSetUp.php");

/**
 * Class StandardNotificationTest
 */
class StandardNotificationGroupRendererTest extends BaseNotificationSetUp
{
    public function testConstruct()
    {
        $renderer = new StandardNotificationGroupRenderer($this->getUIFactory());
        $this->assertInstanceOf(StandardNotificationGroupRenderer::class, $renderer);
    }

    public function testGetNotificationComponentForItem()
    {
        $renderer = new StandardNotificationGroupRenderer($this->getUIFactory());
        $icon = $this->getUIFactory()->symbol()->icon()->standard("mail", "mail");
        $item = $this->getUIFactory()->item()->notification("hello", $icon);

        $slate = $this->getUIFactory()->mainControls()->slate()->notification("title", [$item]);

        $group_notification = $this->factory->standardGroup($this->id)->withTitle("title")->addNotification(
            $this->factory->standard($this->id)->withNotificationItem($item)
        );

        $this->assertEquals($slate, $renderer->getNotificationComponentForItem($group_notification));
    }
}
