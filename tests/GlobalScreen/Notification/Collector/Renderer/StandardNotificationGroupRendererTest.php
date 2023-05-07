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

use ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer\StandardNotificationGroupRenderer;

require_once(__DIR__ . "/../../BaseNotificationSetUp.php");

/**
 * Class StandardNotificationTest
 */
class StandardNotificationGroupRendererTest extends BaseNotificationSetUp
{
    public function testConstruct() : void
    {
        $renderer = new StandardNotificationGroupRenderer($this->getUIFactory());
        $this->assertInstanceOf(StandardNotificationGroupRenderer::class, $renderer);
    }

    public function testGetNotificationComponentForItem() : void
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
