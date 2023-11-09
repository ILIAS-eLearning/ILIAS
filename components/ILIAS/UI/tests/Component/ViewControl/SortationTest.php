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

require_once("vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation as I;
use ILIAS\UI\Implementation\Component\SignalGenerator;

/**
 * Test on icon implementation.
 */
class SortationTest extends ILIAS_UI_TestBase
{
    protected array $options = [
        'internal_rating' => 'Best',
        'date_desc' => 'Most Recent',
        'date_asc' => 'Oldest',
    ];

    private function getFactory(): I\Component\ViewControl\Factory
    {
        return new I\Component\ViewControl\Factory(
            new SignalGenerator()
        );
    }

    public function testConstruction(): void
    {
        $f = $this->getFactory();
        $sortation = $f->sortation($this->options, 'date_desc');
        $this->assertInstanceOf("ILIAS\\UI\\Component\\ViewControl\\Sortation", $sortation);
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Signal", $sortation->getSelectSignal());
    }

    public function testFaultyConstruction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $f = $this->getFactory();
        $sortation = $f->sortation($this->options, 'not_in_options');
    }

    public function testAttributes(): void
    {
        $f = $this->getFactory();
        $s = $f->sortation($this->options, 'date_desc');

        $this->assertEquals($this->options, $s->getOptions());

        $s = $s->withTargetURL('#', 'param');
        $this->assertEquals('#', $s->getTargetURL());
        $this->assertEquals('param', $s->getParameterName());

        $this->assertEquals(array(), $s->getTriggeredSignals());
        $generator = new SignalGenerator();
        $signal = $generator->create();
        $this->assertEquals($signal, $s->withOnSort($signal)->getTriggeredSignals()[0]->getSignal());
        $this->assertEquals('internal_rating', $s->withSelected('internal_rating')->getSelected());
    }

    public function testRendering(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();
        $s = $f->sortation($this->options, 'date_desc');

        $expected = <<<EOT
<div class="dropdown il-viewcontrol il-viewcontrol-sortation l-bar__element">
    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-label="sortation" aria-haspopup="true" aria-expanded="false" aria-controls="_ctrl">vc_sort Most Recent<span class="caret"></span></button>
    <ul id="_ctrl" class="dropdown-menu">
        <li><button class="btn btn-link" data-action="?sortation=internal_rating" id="id_1">Best</button></li>
        <li class="selected"><button class="btn btn-link" data-action="?sortation=date_desc" id="id_2">Most Recent</button></li>
        <li><button class="btn btn-link" data-action="?sortation=date_asc" id="id_3">Oldest</button></li>
    </ul>
</div>
EOT;
        $html = $this->brutallyTrimHTML($r->render($s));
        $this->assertEquals($this->brutallyTrimHTML($expected), $html);
    }

    public function testRenderingWithSelected(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();
        $s = $f->sortation($this->options, 'internal_rating')
            ->withSelected('date_desc');

        $expected = <<<EOT
<div class="dropdown il-viewcontrol il-viewcontrol-sortation l-bar__element">
    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-label="sortation" aria-haspopup="true" aria-expanded="false" aria-controls="_ctrl">vc_sort Most Recent<span class="caret"></span></button>
    <ul id="_ctrl" class="dropdown-menu">
        <li><button class="btn btn-link" data-action="?sortation=internal_rating" id="id_1">Best</button></li>
        <li class="selected"><button class="btn btn-link" data-action="?sortation=date_desc" id="id_2">Most Recent</button></li>
        <li><button class="btn btn-link" data-action="?sortation=date_asc" id="id_3">Oldest</button></li>
    </ul>
</div>
EOT;
        $html = $this->brutallyTrimHTML($r->render($s));
        $this->assertEquals($this->brutallyTrimHTML($expected), $html);
    }

    public function testRenderingWithJsBinding(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();
        $s = $f->sortation($this->options, current(array_keys($this->options)))
            ->withAdditionalOnLoadCode(
                function ($id) {
                    return "";
                }
            );

        $id = ' id="id_1"';
        $id_ctrl = "id_1_ctrl";
        $button1_id = "id_2";
        $button2_id = "id_3";
        $button3_id = "id_4";
        $dropdown_id = "id_5";
        $dropdown_menu_id = $dropdown_id . "_menu";

        $expected = <<<EOT
<div class="dropdown il-viewcontrol il-viewcontrol-sortation l-bar__element"$id>
    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-label="sortation" aria-haspopup="true" aria-expanded="false" aria-controls="{$id_ctrl}">vc_sort Best<span class="caret"></span></button>
    <ul id="{$id_ctrl}" class="dropdown-menu">
        <li class="selected"><button class="btn btn-link" data-action="?sortation=internal_rating" id="$button1_id">Best</button></li>
        <li><button class="btn btn-link" data-action="?sortation=date_desc" id="$button2_id">Most Recent</button></li>
        <li><button class="btn btn-link" data-action="?sortation=date_asc" id="$button3_id">Oldest</button></li>
    </ul>
</div>
EOT;
        $html = $this->brutallyTrimHTML($r->render($s));
        $this->assertEquals($this->brutallyTrimHTML($expected), $html);
    }

    public function getUIFactory(): NoUIFactory
    {
        return new class () extends NoUIFactory {
            public function button(): C\Button\Factory
            {
                return new I\Component\Button\Factory();
            }
            public function dropdown(): C\Dropdown\Factory
            {
                return new I\Component\Dropdown\Factory();
            }
        };
    }
}
