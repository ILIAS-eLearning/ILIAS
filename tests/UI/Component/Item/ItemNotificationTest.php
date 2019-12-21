<?php

/* Copyright (c) 2019 Timon Amstutz <timon.amstutz@ilub.unibe.ch Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Implementation as I;

/**
 * Test Notification Items
 */
class ItemNotificationTest extends ILIAS_UI_TestBase
{
    public function setUp() : void
    {
        $this->sig_gen = new I\Component\SignalGenerator();
    }

    public function getIcon()
    {
        return $this->getUIFactory()->symbol()->icon()->standard("name", "aria_label", "small", false);
    }

    public function getUIFactory()
    {
        $factory = new class extends NoUIFactory {
            public function item()
            {
                return new I\Component\Item\Factory();
            }
            public function Link()
            {
                return new \ILIAS\UI\Implementation\Component\Link\Factory();
            }
            public function button()
            {
                return new I\Component\Button\Factory();
            }
            public function symbol() : ILIAS\UI\Component\Symbol\Factory
            {
                return new I\Component\Symbol\Factory(
                    new I\Component\Symbol\Icon\Factory(),
                    new I\Component\Symbol\Glyph\Factory()
                );
            }
            public function mainControls() : ILIAS\UI\Component\MainControls\Factory
            {
                return new I\Component\MainControls\Factory(
                    $this->sig_gen,
                    new I\Component\MainControls\Slate\Factory(
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

    public function testImplementsFactoryInterface()
    {
        $f = $this->getUIFactory()->item();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Item\\Notification", $f->notification("title", $this->getIcon()));
    }


    public function testGetTitle()
    {
        $f = $this->getUIFactory()->item();
        $c = $f->standard("title");

        $this->assertEquals($c->getTitle(), "title");
    }

    public function testGetTitleAsLink()
    {
        $f = $this->getUIFactory()->item();
        $title_link = $this->getUIFactory()->link()->standard("TestLink", "");
        $c = $f->standard($title_link, $this->getIcon());

        $this->assertEquals($c->getTitle(), $title_link);
    }


    public function testWithDescription()
    {
        $f = $this->getUIFactory()->item();

        $c = $f->notification("title", $this->getIcon())->withDescription("description");

        $this->assertEquals($c->getDescription(), "description");
    }

    public function testWithProperties()
    {
        $f = $this->getUIFactory()->item();

        $props = array("prop1" => "val1", "prop2" => "val2");
        $c = $f->notification("title", $this->getIcon())->withProperties($props);

        $this->assertEquals($c->getProperties(), $props);
    }

    public function testWithActions()
    {
        $f = $this->getUIFactory()->item();

        $actions = new I\Component\Dropdown\Standard(array(
            new I\Component\Button\Shy("ILIAS", "https://www.ilias.de"),
            new I\Component\Button\Shy("GitHub", "https://www.github.com")
        ));
        $c = $f->notification("title", $this->getIcon())->withActions($actions);

        $this->assertEquals($c->getActions(), $actions);
    }

    public function testWithLeadIcon()
    {
        $f = $this->getUIFactory()->item();

        $c = $f->notification("title", $this->getIcon());
        $this->assertEquals($c->getLeadIcon(), $this->getIcon());
        $icon2 = $this->getIcon()->withIsOutlined(true);

        $this->assertEquals($c->withLeadIcon($icon2)->getLeadIcon(), $icon2);
    }

    public function testWithCloseAction()
    {
        $f = $this->getUIFactory()->item();

        $c = $f->notification("title", $this->getIcon())->withCloseAction("closeAction");

        $this->assertEquals($c->getCloseAction(), "closeAction");
    }

    public function testWithAdditionalContent()
    {
        $f = $this->getUIFactory()->item();

        $content = new I\Component\Legacy\Legacy("someContent", $this->sig_gen);
        $c = $f->notification("title", $this->getIcon())->withAdditionalContent($content);

        $this->assertEquals($c->getAdditionalContent(), $content);
    }

    public function testWithAggregateNotifications()
    {
        $f = $this->getUIFactory()->item();

        $aggregate = $f->notification("title_aggregate", $this->getIcon());
        $c = $f->notification("title", $this->getIcon())
               ->withAggregateNotifications([$aggregate,$aggregate]);


        $this->assertEquals($c->getAggregateNotifications(), [$aggregate,$aggregate]);
    }

    public function testRenderFullyFeatured()
    {
        $f = $this->getUIFactory()->item();
        $r = $this->getDefaultRenderer(new class implements \ILIAS\UI\Implementation\Render\JavaScriptBinding {
            public function createId()
            {
                return "id";
            }
            public $on_load_code = array();
            public function addOnLoadCode($code)
            {
                $this->on_load_code[] = $code;
            }
            public function getOnLoadCodeAsync()
            {
            }
        });

        $props = array("prop1" => "val1", "prop2" => "val2");
        $content = new I\Component\Legacy\Legacy("someContent", $this->sig_gen);
        $actions = new I\Component\Dropdown\Standard(array(
            new I\Component\Button\Shy("ILIAS", "https://www.ilias.de"),
            new I\Component\Button\Shy("GitHub", "https://www.github.com")
        ));
        $title_link = $this->getUIFactory()->link()->standard("TestLink", "");
        $aggregate = $f->notification("title_aggregate", $this->getIcon());

        $c = $f->notification($title_link, $this->getIcon())
            ->withDescription("description")
            ->withProperties($props)
            ->withAdditionalContent($content)
            ->withAggregateNotifications([$aggregate])
            ->withCloseAction("closeAction")
            ->withActions($actions)
            ;

        $html = $this->brutallyTrimHTML($r->render($c));
        $expected = <<<EOT
<span class="il-item-notification-replacement-container">
	<div class="il-item il-notification-item" id="id">
		<div class="media">
			<div class="media-left">
				<div class="icon name small" aria-label="aria_label"></div>
			</div>
			<div class="media-body">
				<h5 class="il-item-notification-title">
					<a href="">TestLink</a>
				</h5>
				<button type="button" class="close" data-dismiss="modal" id="id">
					<span aria-hidden="true">&times;</span>
					<span class="sr-only">Close</span>
				</button>
				<div class="il-item-description">description</div>
				<div class="dropdown">
					<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu">
						<li>
							<button class="btn btn-link" data-action="https://www.ilias.de" id="id">ILIAS</button>
						</li>
						<li>
							<button class="btn btn-link" data-action="https://www.github.com" id="id">GitHub</button>
						</li>
					</ul>
				</div>
				<div class="il-item-additional-content">someContent</div>
				<hr class="il-item-divider">
					<div class="row il-item-properties">
						<div class="col-md-6">
							<div class="row">
								<div class="col-sm-5 il-item-property-name">prop1</div>
								<div class="col-sm-7 il-item-property-value il-multi-line-cap-3">val1</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="row">
								<div class="col-sm-5 il-item-property-name">prop2</div>
								<div class="col-sm-7 il-item-property-value il-multi-line-cap-3">val2</div>
							</div>
						</div>
					</div>
					<div class="il-aggregate-notifications" data-aggregatedby="id">
						<div class="il-maincontrols-slate il-maincontrols-slate-notification">
							<div class="il-maincontrols-slate-notification-title">
								<button class="btn btn-bulky" data-action="">
									<span class="glyph" aria-label="back">
										<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
									</span>
									<span class="bulky-label">back</span>
								</button>
							</div>
							<div class="il-maincontrols-slate-content">
								<span class="il-item-notification-replacement-container">
									<div class="il-item il-notification-item" id="id">
										<div class="media">
											<div class="media-left">
												<div class="icon name small" aria-label="aria_label"></div>
											</div>
											<div class="media-body">
												<h5 class="il-item-notification-title">title_aggregate</h5>
												<div class="il-aggregate-notifications" data-aggregatedby="id">
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
					</div>
				</div>
			</div>
		</div>
	</span>
EOT;

        $this->assertEquals($this->brutallyTrimHTML($expected), $html);
    }
}
