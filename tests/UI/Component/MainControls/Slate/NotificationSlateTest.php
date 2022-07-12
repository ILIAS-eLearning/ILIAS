<?php declare(strict_types=1);

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
 
require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\Counter\Factory;

/**
 * Tests for the Slate.
 */
class NotificationSlateTest extends ILIAS_UI_TestBase
{
    protected I\SignalGenerator $sig_gen;

    public function setUp() : void
    {
        $this->sig_gen = new I\SignalGenerator();
    }

    public function getIcon() : C\Symbol\Icon\Standard
    {
        return $this->getUIFactory()->symbol()->icon()->standard("name", "aria_label", "small", false);
    }

    public function getUIFactory() : NoUIFactory
    {
        $factory = new class extends NoUIFactory {
            public I\SignalGenerator $sig_gen;

            public function button() : C\Button\Factory
            {
                return new I\Button\Factory();
            }
            public function symbol() : ILIAS\UI\Component\Symbol\Factory
            {
                return new I\Symbol\Factory(
                    new I\Symbol\Icon\Factory(),
                    new I\Symbol\Glyph\Factory(),
                    new I\Symbol\Avatar\Factory()
                );
            }
            public function item() : C\Item\Factory
            {
                return new I\Item\Factory();
            }
            public function mainControls() : C\MainControls\Factory
            {
                return new I\MainControls\Factory(
                    $this->sig_gen,
                    new I\MainControls\Slate\Factory(
                        $this->sig_gen,
                        new Factory(),
                        $this->symbol()
                    )
                );
            }
            public function icon() : C\Symbol\Icon\Factory
            {
                return new I\Symbol\Icon\Factory();
            }
        };
        $factory->sig_gen = $this->sig_gen;

        return $factory;
    }

    public function testImplementsFactoryInterface() : void
    {
        $notificatino_slate = $this->getUIFactory()->mainControls()->slate()->notification("title", []);

        $this->assertInstanceOf("ILIAS\\UI\\Component\\MainControls\\Slate\\Notification", $notificatino_slate);
    }

    public function testGenerationByFactory() : void
    {
        $item = $this->getUIFactory()->item()->notification("title", $this->getIcon())
                                             ->withDescription("description");

        $notification_slate = $this->getUIFactory()->mainControls()->slate()->notification("title", [$item,$item]);
        $this->assertEquals("title", $notification_slate->getName());
        $this->assertEquals($notification_slate->getContents(), [$item,$item]);
    }


    public function testWithAdditionalEntry() : void
    {
        /** @var C\Item\Notification $item */
        $item = $this->getUIFactory()->item()->notification("title", $this->getIcon())
                     ->withDescription("description");
        $notification_slate = $this->getUIFactory()->mainControls()->slate()->notification("title", [$item,$item]);
        $this->assertEquals($notification_slate->getContents(), [$item,$item]);
        $notification_slate = $notification_slate->withAdditionalEntry($item);
        $this->assertEquals($notification_slate->getContents(), [$item,$item,$item]);
    }

    public function testRenderingWithSubslateAndButton() : void
    {
        $item = $this->getUIFactory()->item()->notification("item title", $this->getIcon());
        $notification_slate = $this->getUIFactory()->mainControls()->slate()->notification("slate title", [$item]);


        $r = $this->getDefaultRenderer();
        $html = $r->render($notification_slate);

        $expected = <<<EOT
<div class="il-maincontrols-slate il-maincontrols-slate-notification">
	<div class="il-maincontrols-slate-notification-title">slate title</div>
	<div class="il-maincontrols-slate-content">
		<div class="il-item-notification-replacement-container">
			<div class="il-item il-notification-item" id="id_1">
				<div class="media">
					<div class="media-left">
						<img class="icon name small" src="./templates/default/images/icon_default.svg" alt="aria_label"/>
					</div>
					<div class="media-body">
						<h4 class="il-item-notification-title">item title</h4>
						<div class="il-aggregate-notifications" data-aggregatedby="id_1">
							<div class="il-maincontrols-slate il-maincontrols-slate-notification">
								<div class="il-maincontrols-slate-notification-title">
									<button class="btn btn-bulky" data-action="">
										<span class="glyph" role="img">
											<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
										</span>
										<span class="bulky-label">back</span>
									</button>
								</div>
								<div class="il-maincontrols-slate-content"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
EOT;
        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }
}
