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
use ILIAS\UI\Implementation\Component\SignalGenerator;

/**
 * Test secondary legacy panels
 */
class PanelSecondaryLegacyTest extends ILIAS_UI_TestBase
{
    public function getUIFactory(): NoUIFactory
    {
        return new class () extends NoUIFactory {
            public function legacyPanel(string $title, C\Legacy\Legacy $content): I\Component\Panel\Secondary\Legacy
            {
                return new I\Component\Panel\Secondary\Legacy($title, $content);
            }

            public function legacy(string $content): C\Legacy\Legacy
            {
                $f = new I\Component\Legacy\Factory(new I\Component\SignalGenerator());
                return $f->legacy($content);
            }

            public function dropdown(): C\Dropdown\Factory
            {
                return new I\Component\Dropdown\Factory();
            }

            public function viewControl(): C\ViewControl\Factory
            {
                return new I\Component\ViewControl\Factory(new SignalGenerator());
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
        };
    }

    protected function cleanHTML(string $html): string
    {
        $html = str_replace(["\n", "\t"], "", $html);

        return trim($html);
    }

    public function test_implements_factory_interface(): void
    {
        $legacy = $this->getUIFactory()->legacy("Legacy content");
        $secondary_panel = $this->getUIFactory()->legacyPanel("List Title", $legacy);

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Panel\\Secondary\\Legacy", $secondary_panel);
    }

    public function test_get_title(): void
    {
        $legacy = $this->getUIFactory()->legacy("Legacy content");
        $secondary_panel = $this->getUIFactory()->legacyPanel("Title", $legacy);

        $this->assertEquals("Title", $secondary_panel->getTitle());
    }

    public function test_get_legacy_component(): void
    {
        $legacy = $this->getUIFactory()->legacy("Legacy content");
        $secondary_panel = $this->getUIFactory()->legacyPanel("title", $legacy);

        $this->assertEquals($secondary_panel->getLegacyComponent(), $legacy);
    }

    public function test_with_actions(): void
    {
        $legacy = $this->getUIFactory()->legacy("Legacy content");
        $actions = $this->getUIFactory()->dropdown()->standard(array(
            $this->getUIFactory()->button()->shy("ILIAS", "https://www.ilias.de"),
            $this->getUIFactory()->button()->shy("Github", "https://www.github.com")
        ));

        $secondary_panel = $this->getUIFactory()->legacyPanel("title", $legacy)
            ->withActions($actions);

        $this->assertEquals($secondary_panel->getActions(), $actions);
    }

    public function test_without_viewcontrols(): void
    {
        $legacy = $this->getUIFactory()->legacy("Legacy content");
        $secondary_panel = $this->getUIFactory()->legacyPanel("title", $legacy);
        $array_vc = $secondary_panel->getViewControls();

        $this->assertEquals(null, $array_vc);
    }

    public function test_with_sortation_viewcontrol(): void
    {
        $legacy = $this->getUIFactory()->legacy("Legacy content");
        $sort_options = array(
            'internal_rating' => 'Best',
            'date_desc' => 'Most Recent',
            'date_asc' => 'Oldest',
        );
        $sortation = $this->getUIFactory()->viewControl()->sortation($sort_options);

        $secondary_panel = $this->getUIFactory()->legacyPanel("title", $legacy)
            ->withViewControls([$sortation]);

        $array_vc = $secondary_panel->getViewControls();

        $this->assertEquals($array_vc[0], $sortation);
    }

    public function test_with_pagination_viewcontrol(): void
    {
        $legacy = $this->getUIFactory()->legacy("Legacy content");
        $pagination = $this->getUIFactory()->viewControl()->pagination()
            ->withTargetURL("http://ilias.de", 'page')
            ->withTotalEntries(98)
            ->withPageSize(10)
            ->withCurrentPage(1);

        $secondary_panel = $this->getUIFactory()->legacyPanel("title", $legacy)
            ->withViewControls([$pagination]);

        $array_vc = $secondary_panel->getViewControls();

        $this->assertEquals($array_vc[0], $pagination);
    }

    public function test_with_section_viewcontrol(): void
    {
        $legacy = $this->getUIFactory()->legacy("Legacy content");
        $back = $this->getUIFactory()->button()->standard("previous", "http://www.ilias.de");
        $next = $this->getUIFactory()->button()->standard("next", "http://www.github.com");
        $current = $this->getUIFactory()->button()->standard("current", "");
        $section = $this->getUIFactory()->viewControl()->section($back, $current, $next);

        $secondary_panel = $this->getUIFactory()->legacyPanel("title", $legacy)
            ->withViewControls([$section]);

        $array_vc = $secondary_panel->getViewControls();

        $this->assertEquals($array_vc[0], $section);
    }

    //RENDER

    public function test_render_with_actions(): void
    {
        $legacy = $this->getUIFactory()->legacy("Legacy content");
        $actions = $this->getUIFactory()->dropdown()->standard(array(
            $this->getUIFactory()->button()->shy("ILIAS", "https://www.ilias.de"),
            $this->getUIFactory()->button()->shy("Github", "https://www.github.com")
        ));

        $sec = $this->getUIFactory()->legacyPanel("Title", $legacy)->withActions($actions);

        $html = $this->getDefaultRenderer()->render($sec);

        $expected_html = <<<EOT
<div class="panel panel-secondary panel-flex">
	<div class="panel-heading ilHeader">
		<h2>Title</h2>
		<div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="id_3" aria-label="actions" aria-haspopup="true" aria-expanded="false" aria-controls="id_3_menu" > <span class="caret"></span></button>
			<ul id="id_3_menu" class="dropdown-menu">
				<li><button class="btn btn-link" data-action="https://www.ilias.de" id="id_1">ILIAS</button></li>
				<li><button class="btn btn-link" data-action="https://www.github.com" id="id_2">Github</button></li>
			</ul>
		</div>
	</div>
	<div class="panel-body">
		Legacy content
	</div>
</div>
EOT;
        $this->assertHTMLEquals(
            $this->cleanHTML($expected_html),
            $this->cleanHTML($html)
        );
    }

    public function test_render_with_sortation(): void
    {
        $legacy = $this->getUIFactory()->legacy("Legacy content");
        $sort_options = array(
            'a' => 'A',
            'b' => 'B'
        );
        $sortation = $this->getUIFactory()->viewControl()->sortation($sort_options);
        $sec = $this->getUIFactory()->legacyPanel("Title", $legacy)
            ->withViewControls([$sortation]);

        $html = $this->getDefaultRenderer()->render($sec);

        $expected_html = <<<EOT
<div class="panel panel-secondary panel-flex">
	<div class="panel-heading ilHeader">
		<h2>Title</h2>
		<div class="il-viewcontrol-sortation" id="id_1">
			<div class="dropdown">
				<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="id_4" aria-label="actions" aria-haspopup="true" aria-expanded="false" aria-controls="id_4_menu">
					<span class="caret"></span>
				</button>
				<ul id="id_4_menu" class="dropdown-menu">
					<li><button class="btn btn-link" data-action="?sortation=a" id="id_2">A</button></li>
					<li><button class="btn btn-link" data-action="?sortation=b" id="id_3">B</button></li>
				</ul>
			</div>
		</div>
	</div>
	<div class="panel-body">
		Legacy content
	</div>
</div>
EOT;
        $this->assertHTMLEquals(
            $this->cleanHTML($expected_html),
            $this->cleanHTML($html)
        );
    }

    public function test_render_with_pagination(): void
    {
        $legacy = $this->getUIFactory()->legacy("Legacy content");

        $pagination = $this->getUIFactory()->viewControl()->pagination()
            ->withTargetURL('http://ilias.de', 'page')
            ->withTotalEntries(10)
            ->withPageSize(2)
            ->withCurrentPage(1);

        $sec = $this->getUIFactory()->legacyPanel("Title", $legacy)
            ->withViewControls([$pagination]);

        $html = $this->getDefaultRenderer()->render($sec);

        $expected_html = <<<EOT
<div class="panel panel-secondary panel-flex">
	<div class="panel-heading ilHeader">
		<h2>Title</h2>
		<div class="il-viewcontrol-pagination">
			<span class="browse previous">
				<a class="glyph" href="http://ilias.de?page=0" aria-label="back">
					<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
				</a>
			</span>
			<button class="btn btn-link" data-action="http://ilias.de?page=0" id="id_1">1</button>
			<button class="btn btn-link engaged" aria-pressed="true" data-action="http://ilias.de?page=1" id="id_2">2</button>
			<button class="btn btn-link" data-action="http://ilias.de?page=2" id="id_3">3</button>
			<button class="btn btn-link" data-action="http://ilias.de?page=3" id="id_4">4</button>
			<button class="btn btn-link" data-action="http://ilias.de?page=4" id="id_5">5</button>
			<span class="browse next">
				<a class="glyph" href="http://ilias.de?page=2" aria-label="next">
					<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
				</a>
			</span>
		</div>
	</div>
	<div class="panel-body">
		Legacy content
	</div>
</div>
EOT;
        $this->assertHTMLEquals(
            $this->cleanHTML($expected_html),
            $this->cleanHTML($html)
        );
    }

    public function test_render_with_section(): void
    {
        $legacy = $this->getUIFactory()->legacy("Legacy content");
        $back = $this->getUIFactory()->button()->standard("previous", "http://www.ilias.de");
        $next = $this->getUIFactory()->button()->standard("next", "http://www.github.com");
        $current = $this->getUIFactory()->button()->standard("current", "");
        $section = $this->getUIFactory()->viewControl()->section($back, $current, $next);

        $secondary_panel = $this->getUIFactory()->legacyPanel("Title", $legacy)
            ->withViewControls([$section]);

        $html = $this->getDefaultRenderer()->render($secondary_panel);

        $expected_html = <<<EOT
<div class="panel panel-secondary panel-flex">
	<div class="panel-heading ilHeader">
		<h2>Title</h2>
		<div class="il-viewcontrol-section">
			<a class="btn btn-default " href="http://www.ilias.de" aria-label="previous" data-action="http://www.ilias.de" id="id_1">
				<span class="glyphicon glyphicon-chevron-left"></span>
			</a>
			<button class="btn btn-default" data-action="">
				current
			</button>
			<a class="btn btn-default " href="http://www.github.com" aria-label="next" data-action="http://www.github.com" id="id_2">
				<span class="glyphicon glyphicon-chevron-right"></span>
			</a>
		</div>
	</div>
	<div class="panel-body">
		Legacy content
	</div>
</div>
EOT;
        $this->assertHTMLEquals(
            $this->cleanHTML($expected_html),
            $this->cleanHTML($html)
        );
    }

    public function test_render_with_footer(): void
    {
        $legacy = $this->getUIFactory()->legacy("Legacy content");
        $footer_shy_button = $this->getUIFactory()->button()->shy("Action", "");

        $secondary_panel = $this->getUIFactory()->legacyPanel("Title", $legacy)
            ->withFooter($footer_shy_button);

        $html = $this->getDefaultRenderer()->render($secondary_panel);

        $expected_html = <<<EOT
<div class="panel panel-secondary panel-flex">
	<div class="panel-heading ilHeader">
		<h2>Title</h2>
	</div>
	<div class="panel-body">
		Legacy content
	</div>
	<div class="panel-footer ilBlockInfo">
		<button class="btn btn-link" data-action="">Action</button>
	</div>
</div>
EOT;
        $this->assertHTMLEquals(
            $this->cleanHTML($expected_html),
            $this->cleanHTML($html)
        );
    }

    public function test_render_with_no_header(): void
    {
        $legacy = $this->getUIFactory()->legacy("Legacy content");

        $secondary_panel = $this->getUIFactory()->legacyPanel("", $legacy);

        $html = $this->getDefaultRenderer()->render($secondary_panel);

        $expected_html = <<<EOT
<div class="panel panel-secondary panel-flex">
	<div class="panel-body">
		Legacy content
	</div>
</div>
EOT;
        $this->assertHTMLEquals(
            $this->cleanHTML($expected_html),
            $this->cleanHTML($html)
        );
    }
}
