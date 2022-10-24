<?php

declare(strict_types=1);

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
use ILIAS\UI\Implementation as I;
use ILIAS\UI\Implementation\Render\JavaScriptBinding;

/**
 * Test Notification Items
 */
class ItemNotificationTest extends ILIAS_UI_TestBase
{
    protected I\Component\SignalGenerator $sig_gen;

    public function setUp(): void
    {
        $this->sig_gen = new I\Component\SignalGenerator();
    }

    public function getIcon(): C\Symbol\Icon\Standard
    {
        return $this->getUIFactory()->symbol()->icon()->standard("name", "aria_label", "small", false);
    }

    public function getUIFactory(): NoUIFactory
    {
        $factory = new class () extends NoUIFactory {
            public I\Component\SignalGenerator $sig_gen;

            public function item(): C\Item\Factory
            {
                return new I\Component\Item\Factory();
            }

            public function Link(): C\Link\Factory
            {
                return new I\Component\Link\Factory();
            }

            public function button(): C\Button\Factory
            {
                return new I\Component\Button\Factory();
            }

            public function symbol(): C\Symbol\Factory
            {
                return new I\Component\Symbol\Factory(
                    new I\Component\Symbol\Icon\Factory(),
                    new I\Component\Symbol\Glyph\Factory(),
                    new I\Component\Symbol\Avatar\Factory()
                );
            }

            public function mainControls(): C\MainControls\Factory
            {
                return new I\Component\MainControls\Factory(
                    $this->sig_gen,
                    new I\Component\MainControls\Slate\Factory(
                        $this->sig_gen,
                        new I\Component\Counter\Factory(),
                        $this->symbol()
                    )
                );
            }
        };
        $factory->sig_gen = $this->sig_gen;
        return $factory;
    }

    public function testImplementsFactoryInterface(): void
    {
        $f = $this->getUIFactory()->item();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Item\\Notification", $f->notification("title", $this->getIcon()));
    }

    public function testGetTitle(): void
    {
        $f = $this->getUIFactory()->item();
        $c = $f->standard("title");

        $this->assertEquals("title", $c->getTitle());
    }

    public function testGetTitleAsLink(): void
    {
        $f = $this->getUIFactory()->item();
        $title_link = $this->getUIFactory()->link()->standard("TestLink", "");
        $c = $f->standard($title_link);

        $this->assertEquals($c->getTitle(), $title_link);
    }

    public function testWithDescription(): void
    {
        $f = $this->getUIFactory()->item();

        $c = $f->notification("title", $this->getIcon())->withDescription("description");

        $this->assertEquals("description", $c->getDescription());
    }

    public function testWithProperties(): void
    {
        $f = $this->getUIFactory()->item();

        $props = array("prop1" => "val1", "prop2" => "val2");
        $c = $f->notification("title", $this->getIcon())->withProperties($props);

        $this->assertEquals($c->getProperties(), $props);
    }

    public function testWithActions(): void
    {
        $f = $this->getUIFactory()->item();

        $actions = new I\Component\Dropdown\Standard(array(
            new I\Component\Button\Shy("ILIAS", "https://www.ilias.de"),
            new I\Component\Button\Shy("GitHub", "https://www.github.com")
        ));
        $c = $f->notification("title", $this->getIcon())->withActions($actions);

        $this->assertEquals($c->getActions(), $actions);
    }

    public function testWithLeadIcon(): void
    {
        $f = $this->getUIFactory()->item();

        $c = $f->notification("title", $this->getIcon());
        $this->assertEquals($c->getLeadIcon(), $this->getIcon());
        $icon2 = $this->getIcon();

        $this->assertEquals($c->withLeadIcon($icon2)->getLeadIcon(), $icon2);
    }

    public function testWithCloseAction(): void
    {
        $f = $this->getUIFactory()->item();

        $c = $f->notification("title", $this->getIcon())->withCloseAction("closeAction");

        $this->assertEquals("closeAction", $c->getCloseAction());
    }

    public function testWithAdditionalContent(): void
    {
        $f = $this->getUIFactory()->item();

        $content = new I\Component\Legacy\Legacy("someContent", $this->sig_gen);
        $c = $f->notification("title", $this->getIcon())->withAdditionalContent($content);

        $this->assertEquals($c->getAdditionalContent(), $content);
    }

    public function testWithAggregateNotifications(): void
    {
        $f = $this->getUIFactory()->item();

        $aggregate = $f->notification("title_aggregate", $this->getIcon());
        $c = $f->notification("title", $this->getIcon())
               ->withAggregateNotifications([$aggregate,$aggregate]);


        $this->assertEquals($c->getAggregateNotifications(), [$aggregate,$aggregate]);
    }

    public function testRenderFullyFeatured(): void
    {
        $f = $this->getUIFactory()->item();
        $r = $this->getDefaultRenderer(new class () implements JavaScriptBinding {
            public array $on_load_code = array();

            public function createId(): string
            {
                return "id";
            }

            public function addOnLoadCode(string $code): void
            {
                $this->on_load_code[] = $code;
            }

            public function getOnLoadCodeAsync(): string
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
<div class="il-item-notification-replacement-container">
	<div class="il-item il-notification-item" id="id">
		<div class="media">
			<div class="media-left">
				<img class="icon name small" src="./templates/default/images/icon_default.svg" alt="aria_label"/>
			</div>
			<div class="media-body">
				<h4 class="il-item-notification-title">
					<a href="">TestLink</a>
				</h4>
				<button type="button" class="close" aria-label="close" id="id">
					<span aria-hidden="true">&times;</span>
				</button>
				<div class="il-item-description">description</div>
				<div class="dropdown">
					<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-label="actions" aria-haspopup="true" aria-expanded="false">
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
                        <div class="col-sm-12 il-multi-line-cap-3">
                            <span class="il-item-property-name">prop1</span><span class="il-item-property-value">val1</span>
                        </div>
                        <div class="col-sm-12 il-multi-line-cap-3">
                            <span class="il-item-property-name">prop2</span><span class="il-item-property-value">val2</span>
                        </div>
					</div>
					<div class="il-aggregate-notifications" data-aggregatedby="id">
						<div class="il-maincontrols-slate il-maincontrols-slate-notification">
							<div class="il-maincontrols-slate-notification-title">
								<button class="btn btn-bulky" data-action="">
									<span class="glyph" role="img">
										<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
									</span>
									<span class="bulky-label">back</span>
								</button>
							</div>
							<div class="il-maincontrols-slate-content">
								<div class="il-item-notification-replacement-container">
									<div class="il-item il-notification-item" id="id">
										<div class="media">
											<div class="media-left">
                                                <img class="icon name small" src="./templates/default/images/icon_default.svg" alt="aria_label"/>
											</div>
											<div class="media-body">
												<h4 class="il-item-notification-title">title_aggregate</h4>
												<div class="il-aggregate-notifications" data-aggregatedby="id">
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
					</div>
				</div>
			</div>
		</div>
	</div>
EOT;

        $this->assertEquals($this->brutallyTrimHTML($expected), $html);
    }
}
