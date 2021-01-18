<?php

/* Copyright (c) 2019 Timon Amstutz <timon.amstutz@ilub.unibe.ch Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation\Component as I;
use \ILIAS\UI\Implementation\Component\MainControls\Slate\Notification;

/**
 * Tests for the Slate.
 */
class NotificationSlateTest extends ILIAS_UI_TestBase
{
    public function setUp() : void
    {
        $this->sig_gen = new I\SignalGenerator();
    }

    public function getIcon()
    {
        return $this->getUIFactory()->symbol()->icon()->standard("name", "aria_label", "small", false);
    }

    public function getUIFactory()
    {
        $factory = new class extends NoUIFactory {
            public function button()
            {
                return new I\Button\Factory($this->sig_gen);
            }
            public function symbol() : ILIAS\UI\Component\Symbol\Factory
            {
                return new I\Symbol\Factory(
                    new I\Symbol\Icon\Factory(),
                    new I\Symbol\Glyph\Factory(),
                    new I\Symbol\Avatar\Factory()
                );
            }
            public function item()
            {
                return new I\Item\Factory();
            }
            public function mainControls() : C\MainControls\Factory
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
            public function icon() : I\Symbol\Icon\Factory
            {
                new I\Symbol\Icon\Factory();
            }
        };
        $factory->sig_gen = $this->sig_gen;

        return $factory;
    }

    public function testImplementsFactoryInterface()
    {
        $notificatino_slate = $this->getUIFactory()->mainControls()->slate()->notification("title", []);

        $this->assertInstanceOf("ILIAS\\UI\\Component\\MainControls\\Slate\\Notification", $notificatino_slate);
    }

    public function testGenerationByFactory()
    {
        $item = $this->getUIFactory()->item()->notification("title", $this->getIcon())
                                             ->withDescription("description");

        $notification_slate = $this->getUIFactory()->mainControls()->slate()->notification("title", [$item,$item]);
        $this->assertEquals($notification_slate->getName(), "title");
        $this->assertEquals($notification_slate->getContents(), [$item,$item]);
    }


    public function testWithAdditionalEntry()
    {
        $item = $this->getUIFactory()->item()->notification("title", $this->getIcon())
                     ->withDescription("description");
        $notification_slate = $this->getUIFactory()->mainControls()->slate()->notification("title", [$item,$item]);
        $this->assertEquals($notification_slate->getContents(), [$item,$item]);
        $notification_slate = $notification_slate->withAdditionalEntry($item);
        $this->assertEquals($notification_slate->getContents(), [$item,$item,$item]);
    }

    public function testRenderingWithSubslateAndButton()
    {
        $item = $this->getUIFactory()->item()->notification("item title", $this->getIcon());
        $notification_slate = $this->getUIFactory()->mainControls()->slate()->notification("slate title", [$item]);


        $r = $this->getDefaultRenderer();
        $html = $r->render($notification_slate);

        $expected = <<<EOT
<div class="il-maincontrols-slate il-maincontrols-slate-notification">
	<div class="il-maincontrols-slate-notification-title">slate title</div>
	<div class="il-maincontrols-slate-content">
		<span class="il-item-notification-replacement-container">
			<div class="il-item il-notification-item" id="id_1">
				<div class="media">
					<div class="media-left">
						<div class="icon name small" aria-label="aria_label"></div>
					</div>
					<div class="media-body">
						<h4 class="il-item-notification-title">item title</h4>
						<div class="il-aggregate-notifications" data-aggregatedby="id_1">
							<div class="il-maincontrols-slate il-maincontrols-slate-notification">
								<div class="il-maincontrols-slate-notification-title">
									<button class="btn btn-bulky" data-action="">
										<span class="glyph" aria-label="back">
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
		</span>
	</div>
</div>
EOT;
        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }
}
