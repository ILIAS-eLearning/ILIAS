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

declare(strict_types=1);

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation as I;
use ILIAS\Data;
use ILIAS\UI\Implementation\Component\Symbol\Avatar\Letter;
use ILIAS\UI\Implementation\Component\Symbol\Avatar\Picture;

/**
 * Test items
 */
class ItemTest extends ILIAS_UI_TestBase
{
    public function getFactory(): C\Item\Factory
    {
        return new I\Component\Item\Factory();
    }

    public function testImplementsFactoryInterface(): void
    {
        $f = $this->getFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Item\\Standard", $f->standard("title"));
    }

    public function testGetTitle(): void
    {
        $f = $this->getFactory();
        $c = $f->standard("title");

        $this->assertEquals("title", $c->getTitle());
    }

    public function testWithDescription(): void
    {
        $f = $this->getFactory();

        $c = $f->standard("title")->withDescription("description");

        $this->assertEquals("description", $c->getDescription());
    }

    public function testWithProperties(): void
    {
        $f = $this->getFactory();

        $props = array("prop1" => "val1", "prop2" => "val2");
        $c = $f->standard("title")->withProperties($props);

        $this->assertEquals($c->getProperties(), $props);
    }

    public function testWithProgress(): void
    {
        $f = $this->getFactory();
        $chart = new I\Component\Chart\ProgressMeter\ProgressMeter(100, 50);

        $c = $f->standard("title")->withProgress($chart);

        $this->assertEquals($c->getProgress(), $chart);
    }

    public function testWithActions(): void
    {
        $f = $this->getFactory();

        $actions = new I\Component\Dropdown\Standard(array(
            new I\Component\Button\Shy("ILIAS", "https://www.ilias.de"),
            new I\Component\Button\Shy("GitHub", "https://www.github.com")
        ));
        $c = $f->standard("title")->withActions($actions);

        $this->assertEquals($c->getActions(), $actions);
    }

    public function testWithColor(): void
    {
        $f = $this->getFactory();
        $df = new Data\Factory();

        $color = $df->color('#ff00ff');

        $c = $f->standard("title")->withColor($color);

        $this->assertEquals($c->getColor(), $color);
    }

    public function testWithLeadImage(): void
    {
        $f = $this->getFactory();

        $image = new I\Component\Image\Image("standard", "src", "str");

        $c = $f->standard("title")->withLeadImage($image);

        $this->assertEquals($c->getLead(), $image);
    }

    public function testWithLeadIcon(): void
    {
        $f = $this->getFactory();

        $icon = new I\Component\Symbol\Icon\Standard("name", "aria_label", "small", false);

        $c = $f->standard("title")->withLeadIcon($icon);

        $this->assertEquals($icon, $c->getLead());
    }

    public function testWithLeadLetterAvatar(): void
    {
        $f = $this->getFactory();

        $avatar = new Letter('il');

        $c = $f->standard("title")->withLeadAvatar($avatar);

        $this->assertEquals($avatar, $c->getLead());
    }

    public function testWithLeadPictureAvatar(): void
    {
        $f = $this->getFactory();

        $avatar = new Picture('./templates/default/images/placeholder/no_photo_xsmall.jpg', 'demo.user');

        $c = $f->standard("title")->withLeadAvatar($avatar);

        $this->assertEquals($avatar, $c->getLead());
    }

    public function testWithLeadText(): void
    {
        $f = $this->getFactory();

        $c = $f->standard("title")->withLeadText("text");

        $this->assertEquals("text", $c->getLead());
    }

    public function testWithNoLead(): void
    {
        $f = $this->getFactory();

        $c = $f->standard("title")->withLeadText("text")->withNoLead();

        $this->assertEquals(null, $c->getLead());
    }

    public function testWithAudioPlayer(): void
    {
        $f = $this->getFactory();

        $audio = new I\Component\Player\Audio("src", "transcript");
        $c = $f->standard("title")->withAudioPlayer($audio);

        $this->assertEquals($c->getAudioPlayer(), $audio);
    }

    public function testWithMainActionButton(): void
    {
        $f = $this->getFactory();

        $main_action = $this->createMock(I\Component\Button\Standard::class);
        $c = $f->standard("Title")->withMainAction($main_action);

        $this->assertEquals($c->getMainAction(), $main_action);
    }

    public function testWithMainActionLink(): void
    {
        $f = $this->getFactory();

        $main_action = $this->createMock(I\Component\Link\Standard::class);
        $c = $f->standard("Title")->withMainAction($main_action);

        $this->assertEquals($c->getMainAction(), $main_action);
    }

    public function testRenderBase(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $actions = new I\Component\Dropdown\Standard(array(
            new I\Component\Button\Shy("ILIAS", "https://www.ilias.de"),
            new I\Component\Button\Shy("GitHub", "https://www.github.com")
        ));
        $c = $f->standard("Item Title")
            ->withActions($actions)
            ->withProperties(array(
                "Origin" => "Course Title 1",
                "Last Update" => "24.11.2011",
                "Location" => "Room 123, Main Street 44, 3012 Bern"))
            ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.");

        $html = $r->render($c);

        $expected = <<<EOT
        <div class="il-item il-std-item ">
            <div class="il-item-title">Item Title</div>
			<div class="il-item-actions l-bar__container"><div class="l-bar__element"><div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="id_3" aria-label="actions" aria-haspopup="true" aria-expanded="false" aria-controls="id_3_menu"><span class="caret"></span></button>
                <ul id="id_3_menu" class="dropdown-menu">
	                <li><button class="btn btn-link" data-action="https://www.ilias.de" id="id_1"  >ILIAS</button>
                    </li>
                        <li><button class="btn btn-link" data-action="https://www.github.com" id="id_2"  >GitHub</button>
                    </li>
                </ul>
            </div></div></div>
			<div class="il-item-description">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.</div>
			<hr class="il-item-divider" />
			<div class="row">
                <div class="col-md-6 il-multi-line-cap-3">
					<span class="il-item-property-name">Origin</span><span class="il-item-property-value">Course Title 1</span>
				</div>
				<div class="col-md-6 il-multi-line-cap-3">
					<span class="il-item-property-name">Last Update</span><span class="il-item-property-value">24.11.2011</span>
				</div>
			</div>
			<div class="row">
                <div class="col-md-6 il-multi-line-cap-3">
					<span class="il-item-property-name">Location</span><span class="il-item-property-value">Room 123, Main Street 44, 3012 Bern</span>
				</div>
				<div class="col-md-6 il-multi-line-cap-3">
					<span class="il-item-property-name"></span><span class="il-item-property-value"></span>
				</div>
			</div>
        </div>
EOT;

        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testRenderLeadImage(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $image = new I\Component\Image\Image("standard", "src", "str");

        $c = $f->standard("title")->withLeadImage($image);

        $html = $r->render($c);
        $expected = <<<EOT
<div class="il-item il-std-item ">
	<div class="row">
		<div class="col-xs-2 col-sm-3">
			<img src="src" class="img-standard" alt="str" />
		</div>
		<div class="col-xs-10 col-sm-9">
            <div class="il-item-title">title</div>
		</div>
	</div>
</div>
EOT;

        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testRenderLeadIcon(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $icon = new I\Component\Symbol\Icon\Standard("name", "aria_label", "small", false);

        $c = $f->standard("title")->withLeadIcon($icon);

        $html = $r->render($c);
        $expected = <<<EOT
<div class="il-item il-std-item ">
	<div class="media">
		<div class="media-left">
			<img class="icon name small" src="./templates/default/images/standard/icon_default.svg" alt="aria_label" />
        </div>
		<div class="media-body">
            <div class="il-item-title">title</div>
		</div>
	</div>
</div>
EOT;

        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testRenderLeadLetterAvatar(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $avatar = new Letter('il');

        $c = $f->standard("title")->withLeadAvatar($avatar);

        $html = $r->render($c);

        $expected = <<<EOT
<div class="il-item il-std-item ">
    <div class="media">
        <div class="media-left">
            <span class="il-avatar il-avatar-letter il-avatar-size-large il-avatar-letter-color-11" aria-label="user_avatar" role="img">
                <span class="abbreviation">il</span>
            </span>
        </div>
        <div class="media-body">
            <div class="il-item-title">title</div>
        </div>
    </div>
</div>
EOT;

        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testRenderLeadPictureAvatar(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $avatar = new Picture('./templates/default/images/placeholder/no_photo_xsmall.jpg', 'demo.user');

        $c = $f->standard("title")->withLeadAvatar($avatar);

        $html = $r->render($c);
        $expected = <<<EOT
<div class="il-item il-std-item ">
    <div class="media">
        <div class="media-left">
            <span class="il-avatar il-avatar-picture il-avatar-size-large">
                <img src="./templates/default/images/placeholder/no_photo_xsmall.jpg" alt="user_avatar"/>
            </span>
        </div>
        <div class="media-body">
            <div class="il-item-title">title</div>
        </div>
    </div>
</div>
EOT;

        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testRenderProgress(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $chart = new I\Component\Chart\ProgressMeter\Standard(100, 75);

        $c = $f->standard("title")->withProgress($chart);

        $html = $r->render($c);
        $expected = <<<EOT
<div class="il-item il-std-item ">
	<div class="row">
	    <div class="col-sm-9">
            <div class="il-item-title">title</div>
		</div>
		<div class="col-xs-3 col-sm-2 col-lg-2">
		    <div class="il-chart-progressmeter-box ">
		        <div class="il-chart-progressmeter-container">
		            <svg viewBox="0 0 50 40" class="il-chart-progressmeter-viewbox">
		                <path class="il-chart-progressmeter-circle-bg" stroke-dasharray="100, 100" d="M10.4646,37.0354 q-5.858,-5.858 -5.858,-14.142 a1,1 0 1,1 40,0 q0,8.284 -5.858,14.142"></path>
                        <g class="il-chart-progressmeter-monocircle">
                            <path class="il-chart-progressmeter-circle no-success" stroke-dasharray="75, 100" d="M10.4646,37.0354 q-5.858,-5.858 -5.858,-14.142 a1,1 0 1,1 40,0 q0,8.284 -5.858,14.142"></path>
                        </g>
                        <g class="il-chart-progressmeter-text">
                            <text class="text-score-info" x="25" y="16"></text>
                            <text class="text-score" x="25" y="25">75 %</text>
                            <text class="text-comparision" x="25" y="31"></text>
                            <text class="text-comparision-info" x="25" y="34"></text>
                        </g>
                        <g class="il-chart-progressmeter-needle no-needle" style="transform: rotate(deg)">
                            <polygon class="il-chart-progressmeter-needle-border" points="23.5,0.1 25,2.3 26.5,0.1"></polygon>
                            <polygon class="il-chart-progressmeter-needle-fill" points="23.5,0 25,2.2 26.5,0"></polygon>
                        </g>
                    </svg>
                </div>
            </div>
        </div>
	</div>
</div>
EOT;

        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testRenderProgressAndLeadImage(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $image = new I\Component\Image\Image("standard", "src", "str");
        $chart = new I\Component\Chart\ProgressMeter\Standard(100, 75);

        $c = $f->standard("title")->withLeadImage($image)->withProgress($chart);

        $html = $r->render($c);
        $expected = <<<EOT
<div class="il-item il-std-item ">
	<div class="row">
	    <div class="col-xs-3 col-sm-3 col-lg-2">
			<img src="src" class="img-standard" alt="str" />
		</div>
	    <div class="col-xs-6 col-sm-7 col-lg-8">
            <div class="il-item-title">title</div>
		</div>
		<div class="col-xs-3 col-sm-2 col-lg-2">
		    <div class="il-chart-progressmeter-box ">
		        <div class="il-chart-progressmeter-container">
		            <svg viewBox="0 0 50 40" class="il-chart-progressmeter-viewbox">
		                <path class="il-chart-progressmeter-circle-bg" stroke-dasharray="100, 100" d="M10.4646,37.0354 q-5.858,-5.858 -5.858,-14.142 a1,1 0 1,1 40,0 q0,8.284 -5.858,14.142"></path>
                        <g class="il-chart-progressmeter-monocircle">
                            <path class="il-chart-progressmeter-circle no-success" stroke-dasharray="75, 100" d="M10.4646,37.0354 q-5.858,-5.858 -5.858,-14.142 a1,1 0 1,1 40,0 q0,8.284 -5.858,14.142"></path>
                        </g>
                        <g class="il-chart-progressmeter-text">
                            <text class="text-score-info" x="25" y="16"></text>
                            <text class="text-score" x="25" y="25">75 %</text>
                            <text class="text-comparision" x="25" y="31"></text>
                            <text class="text-comparision-info" x="25" y="34"></text>
                        </g>
                        <g class="il-chart-progressmeter-needle no-needle" style="transform: rotate(deg)">
                            <polygon class="il-chart-progressmeter-needle-border" points="23.5,0.1 25,2.3 26.5,0.1"></polygon>
                            <polygon class="il-chart-progressmeter-needle-fill" points="23.5,0 25,2.2 26.5,0"></polygon>
                        </g>
                    </svg>
                </div>
            </div>
        </div>
	</div>
</div>
EOT;

        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testRenderProgressAndLeadIcon(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $icon = new I\Component\Symbol\Icon\Standard("name", "aria_label", "small", false);
        $chart = new I\Component\Chart\ProgressMeter\Standard(100, 75);

        $c = $f->standard("title")->withLeadIcon($icon)->withProgress($chart);

        $html = $r->render($c);
        $expected = <<<EOT
<div class="il-item il-std-item ">
    <div class="media">
		<div class="media-left">
			<img class="icon name small" src="./templates/default/images/standard/icon_default.svg" alt="aria_label" />
        </div>
		<div class="media-body">
            <div class="il-item-title">title</div>
		</div>
		<div class="media-right">
			<div class="il-chart-progressmeter-box ">
		        <div class="il-chart-progressmeter-container">
		            <svg viewBox="0 0 50 40" class="il-chart-progressmeter-viewbox">
		                <path class="il-chart-progressmeter-circle-bg" stroke-dasharray="100, 100" d="M10.4646,37.0354 q-5.858,-5.858 -5.858,-14.142 a1,1 0 1,1 40,0 q0,8.284 -5.858,14.142"></path>
                        <g class="il-chart-progressmeter-monocircle">
                            <path class="il-chart-progressmeter-circle no-success" stroke-dasharray="75, 100" d="M10.4646,37.0354 q-5.858,-5.858 -5.858,-14.142 a1,1 0 1,1 40,0 q0,8.284 -5.858,14.142"></path>
                        </g>
                        <g class="il-chart-progressmeter-text">
                            <text class="text-score-info" x="25" y="16"></text>
                            <text class="text-score" x="25" y="25">75 %</text>
                            <text class="text-comparision" x="25" y="31"></text>
                            <text class="text-comparision-info" x="25" y="34"></text>
                        </g>
                        <g class="il-chart-progressmeter-needle no-needle" style="transform: rotate(deg)">
                            <polygon class="il-chart-progressmeter-needle-border" points="23.5,0.1 25,2.3 26.5,0.1"></polygon>
                            <polygon class="il-chart-progressmeter-needle-fill" points="23.5,0 25,2.2 26.5,0"></polygon>
                        </g>
                    </svg>
                </div>
            </div>
        </div>
	</div>
</div>
EOT;

        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testRenderLeadTextAndColor(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();
        $df = new Data\Factory();

        $color = $df->color('#ff00ff');

        $c = $f->standard("title")->withColor($color)->withLeadText("lead");

        $html = $r->render($c);

        $expected = <<<EOT
<div class="il-item il-std-item il-item-marker " style="border-color:#ff00ff">
	<div class="row">
		<div class="col-sm-3">
			lead
		</div>
		<div class="col-sm-9">
            <div class="il-item-title">title</div>
		</div>
	</div>
</div>
EOT;

        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testShyTitleAndVariousProperties(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();
        $df = new Data\Factory();

        $df->color('#ff00ff');

        $c = $f->standard(new I\Component\Button\Shy("ILIAS", "https://www.ilias.de"))
            ->withProperties([
                "Property Text" => "Text",
                "Property HTML" => "<a>Link</a>",
                "Property Shy" => new I\Component\Button\Shy("GitHub", "https://www.github.com"),
                "Property Icon" => new I\Component\Symbol\Icon\Standard("name", "aria_label", "small", false)
            ]);

        $html = $this->brutallyTrimHTML($r->render($c));
        $expected = $this->brutallyTrimHTML(<<<EOT
<div class="il-item il-std-item ">
    <div class="il-item-title">
        <button class="btn btn-link" data-action="https://www.ilias.de" id="id_1">ILIAS</button>
    </div>
    <hr class="il-item-divider" />
    <div class="row">
        <div class="col-md-6 il-multi-line-cap-3">
            <span class="il-item-property-name">Property Text</span>
            <span class="il-item-property-value">Text</span>
        </div>
        <div class="col-md-6 il-multi-line-cap-3">
            <span class="il-item-property-name">Property HTML</span>
            <span class="il-item-property-value">
                <a>Link</a>
            </span>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 il-multi-line-cap-3">
            <span class="il-item-property-name">Property Shy</span>
            <span class="il-item-property-value">
                <button class="btn btn-link" data-action="https://www.github.com" id="id_2">GitHub</button>
            </span>
        </div>
        <div class="col-md-6 il-multi-line-cap-3">
            <span class="il-item-property-name">Property Icon</span>
            <span class="il-item-property-value">
                <img class="icon name small" src="./templates/default/images/standard/icon_default.svg" alt="aria_label"/>
            </span>
        </div>
    </div>
</div>
EOT);

        $this->assertEquals($expected, $html);
    }

    public function testLinkTitle(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $c = $f->standard(new I\Component\Link\Standard("ILIAS", "https://www.ilias.de"));
        $html = $r->render($c);

        $expected = <<<EOT
<div class="il-item il-std-item "><div class="il-item-title"><a href="https://www.ilias.de">ILIAS</a></div></div>
EOT;

        $this->assertHTMLEquals($expected, $html);
    }

    public function testRenderAudioPlayer(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $audio = new I\Component\Player\Audio("src", "");
        $c = $f->standard("title")->withAudioPlayer($audio);

        $html = $r->render($c);
        $expected = <<<EOT
<div class="il-item il-std-item ">
    <div class="il-item-title">title</div>
    <div class="il-item-audio"><div class="il-audio-container">
    <audio class="il-audio-player" id="id_1" src="src" preload="metadata"></audio>
</div></div>
</div>
EOT;
        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testMainActionButton(): void
    {
        $f = $this->getFactory();

        $expected_button_html = md5(I\Component\Button\Standard::class);
        $main_action = $this->createMock(I\Component\Button\Standard::class);
        $main_action->method('getCanonicalName')->willReturn($expected_button_html);

        $c = $f->standard("Title")->withMainAction($main_action);

        $html = $this->getDefaultRenderer(null, [
            $main_action
        ])->render($c);

        $expected = <<<EOT
        <div class="il-item il-std-item ">
            <div class="il-item-title">Title</div>
            <div class="il-item-actions l-bar__container">
                <div class="l-bar__element">$expected_button_html
            </div>
            </div>
        </div>
EOT;

        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testMainActionLink(): void
    {
        $f = $this->getFactory();
        $expected_link_html = md5(I\Component\Link\Standard::class);
        $main_action = $this->createMock(I\Component\Link\Standard::class);
        $main_action->method('getCanonicalName')->willReturn($expected_link_html);

        $c = $f->standard("Title")->withMainAction($main_action);

        $html = $this->getDefaultRenderer(null, [
            $main_action
        ])->render($c);

        $expected = <<<EOT
        <div class="il-item il-std-item ">
            <div class="il-item-title">Title</div>
            <div class="il-item-actions l-bar__container">
                <div class="l-bar__element">$expected_link_html</div>
            </div>
        </div>
EOT;

        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

}
