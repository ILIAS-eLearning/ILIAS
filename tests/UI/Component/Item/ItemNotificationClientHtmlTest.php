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

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\MainControls\Slate\Notification;

/**
 * Checks if the HTML used for the Client tests is rendered as specified
 */
class ItemNotificationClientHtmlTest extends ILIAS_UI_TestBase
{
    /**
     * @var I\SignalGenerator
     */
    protected $sig_gen;

    public function setUp(): void
    {
        $this->sig_gen = new I\SignalGenerator();
    }

    public function getUIFactory(): NoUIFactory
    {
        $factory = new class () extends NoUIFactory {
            public function counter(): C\Counter\Factory
            {
                return new I\Counter\Factory();
            }
            public function button(): C\Button\Factory
            {
                return new I\Button\Factory($this->sig_gen);
            }
            public function symbol(): ILIAS\UI\Component\Symbol\Factory
            {
                return new I\Symbol\Factory(
                    new I\Symbol\Icon\Factory(),
                    new I\Symbol\Glyph\Factory(),
                    new I\Symbol\Avatar\Factory()
                );
            }
            public function item(): C\Item\Factory
            {
                return new I\Item\Factory();
            }
            public function mainControls(): C\MainControls\Factory
            {
                return new I\MainControls\Factory(
                    $this->sig_gen,
                    new I\MainControls\Slate\Factory(
                        $this->sig_gen,
                        new \ILIAS\UI\Implementation\Component\Counter\Factory(),
                        $this->symbol()
                    )
                );
            }
        };
        $factory->sig_gen = $this->sig_gen;

        return $factory;
    }

    public function testRenderClientHtml(): void
    {
        $f = $this->getUIFactory();
        $expected_html = file_get_contents(__DIR__ . "/../../Client/Item/Notification/NotificationItemTest.html");

        $icon = $f->symbol()->icon()->standard("name", "aria_label", "small", false);

        $item = $f->item()->notification("item title", $icon)
                          ->withCloseAction("close_action");

        $item2 = $item->withDescription("Existing Description")
                      ->withProperties(["Label 1" => "Property Value 1","Label 2" => "Property Value 2"])
                      ->withAggregateNotifications([$item]);
        $notification_slate = $f->mainControls()->slate()->notification(
            "slate title",
            [$item,$item2]
        );

        $glyph = $f->symbol()->glyph()->notification()->withCounter($this->getUIFactory()->counter()->novelty(2));
        $notification_center = $f->mainControls()->slate()->combined("notification center", $glyph)
                                                         ->withAdditionalEntry($notification_slate);

        $this->metabar = $f->mainControls()->metaBar()->withAdditionalEntry("Test Slate", $notification_center);
        $rendered_html = $this->getDefaultRenderer()->render($this->metabar);

        $this->assertEquals($this->brutallyTrimHTML($expected_html), $this->brutallyTrimHTML($rendered_html));
    }
}
