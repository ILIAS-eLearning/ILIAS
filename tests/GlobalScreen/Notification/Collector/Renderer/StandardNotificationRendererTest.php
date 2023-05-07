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

    public function testConstruct() : void
    {
        $renderer = new StandardNotificationRenderer($this->getUIFactory());
        $this->assertInstanceOf(StandardNotificationRenderer::class, $renderer);
    }


    public function testGetNotificationComponentForItem() : void
    {
        $renderer = new StandardNotificationRenderer($this->getUIFactory());
        $icon = $this->getUIFactory()->symbol()->icon()->standard("mail", "mail");
        $item = $this->getUIFactory()->item()->notification("hello", $icon);

        $standard_notification = $this->factory->standard($this->id)->withNotificationItem($item);

        $this->assertEquals($item, $renderer->getNotificationComponentForItem($standard_notification));
    }


    public function testGetNotificationComponentForItemWithCloseCallable() : void
    {
        $renderer = new StandardNotificationRenderer($this->getUIFactory());
        $icon = $this->getUIFactory()->symbol()->icon()->standard("mail", "mail");
        $item = $this->getUIFactory()->item()->notification("hello", $icon);

        $standard_notification = $this->factory->standard($this->id)
            ->withNotificationItem($item)
            ->withClosedCallable(function () : void {
            });

        $item = $item->withCloseAction("src/GlobalScreen/Client/notify.php?mode=closed&item_id=" . $this->hash($this->id->serialize()));
        $this->assertEquals($item, $renderer->getNotificationComponentForItem($standard_notification));
    }
}
