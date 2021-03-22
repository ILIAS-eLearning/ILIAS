<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation as I;
use ILIAS\UI\Implementation\Component\SignalGenerator;

/**
 * Test on icon implementation.
 */
class SortationTest extends ILIAS_UI_TestBase
{
    protected $options = array(
        'internal_rating' => 'Best',
        'date_desc' => 'Most Recent',
        'date_asc' => 'Oldest',
    );

    private function getFactory()
    {
        return new I\Component\ViewControl\Factory(
            new SignalGenerator()
        );
    }

    public function testConstruction()
    {
        $f = $this->getFactory();
        $sortation = $f->sortation($this->options);
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\ViewControl\\Sortation",
            $sortation
        );
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Signal",
            $sortation->getSelectSignal()
        );
    }

    public function testAttributes()
    {
        $f = $this->getFactory();
        $s = $f->sortation($this->options);

        $this->assertEquals($this->options, $s->getOptions());

        $this->assertEquals('label', $s->withLabel('label')->getLabel());

        $s = $s->withTargetURL('#', 'param');
        $this->assertEquals('#', $s->getTargetURL());
        $this->assertEquals('param', $s->getParameterName());

        $this->assertEquals(array(), $s->getTriggeredSignals());
        $generator = new SignalGenerator();
        $signal = $generator->create();
        $this->assertEquals(
            $signal,
            $s->withOnSort($signal)->getTriggeredSignals()[0]->getSignal()
        );
    }

    public function testRendering()
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();
        $s = $f->sortation($this->options);

        $html = $this->normalizeHTML($r->render($s));
        $this->assertEquals(
            $this->getSortationExpectedHTML(true),
            $html
        );
    }

    public function testRenderingWithJsBinding()
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();
        $s = $f->sortation($this->options)->withAdditionalOnLoadCode(function ($id) {
            return "";
        });

        $html = $this->normalizeHTML($r->render($s));
        $this->assertEquals(
            $this->getSortationExpectedHTML(true),
            $html
        );
    }

    protected function getSortationExpectedHTML(bool $with_id = false)
    {
        $id = "";
        $button1_id = "id_1";
        $button2_id = "id_2";
        $button3_id = "id_3";

        if ($with_id) {
            $id = "id=\"id_1\"";
            $button1_id = "id_2";
            $button2_id = "id_3";
            $button3_id = "id_4";
        }

        $expected = <<<EOT
<div class="il-viewcontrol-sortation" $id><div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-label="actions" aria-haspopup="true" aria-expanded="false" > <span class="caret"></span></button><ul class="dropdown-menu">
	<li><button class="btn btn-link" data-action="?sortation=internal_rating" id="$button1_id">Best</button></li>
	<li><button class="btn btn-link" data-action="?sortation=date_desc" id="$button2_id">Most Recent</button></li>
	<li><button class="btn btn-link" data-action="?sortation=date_asc" id="$button3_id">Oldest</button></li></ul></div>
</div>
EOT;
        return $this->normalizeHTML($expected);
    }

    public function getUIFactory()
    {
        $factory = new class extends NoUIFactory {
            public function button()
            {
                return new I\Component\Button\Factory();
            }
            public function dropdown()
            {
                return new I\Component\Dropdown\Factory();
            }
        };
        return $factory;
    }
}
