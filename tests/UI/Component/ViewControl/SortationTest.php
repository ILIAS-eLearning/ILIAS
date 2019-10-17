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
            $this->getSortationExpectedHTML(),
            $html
        );
    }

    protected function getSortationExpectedHTML()
    {
        $expected = <<<EOT
<div class="il-viewcontrol-sortation" id=""><div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-haspopup="true" aria-expanded="false" > <span class="caret"></span></button><ul class="dropdown-menu">
	<li><button class="btn btn-link" data-action="?sortation=internal_rating" id="id_1"  >Best</button></li>
	<li><button class="btn btn-link" data-action="?sortation=date_desc" id="id_2"  >Most Recent</button></li>
	<li><button class="btn btn-link" data-action="?sortation=date_asc" id="id_3"  >Oldest</button></li></ul></div>
</div>
EOT;
        return $this->normalizeHTML($expected);
    }

    public function getUIFactory()
    {
        return new \ILIAS\UI\Implementation\Factory(
            $this->createMock(C\Counter\Factory::class),
            $this->createMock(C\Glyph\Factory::class),
            new I\Component\Button\Factory,
            $this->createMock(C\Listing\Factory::class),
            $this->createMock(C\Image\Factory::class),
            $this->createMock(C\Panel\Factory::class),
            $this->createMock(C\Modal\Factory::class),
            $this->createMock(C\Dropzone\Factory::class),
            $this->createMock(C\Popover\Factory::class),
            $this->createMock(C\Divider\Factory::class),
            $this->createMock(C\Link\Factory::class),
            new I\Component\Dropdown\Factory,
            $this->createMock(C\Item\Factory::class),
            $this->createMock(C\Icon\Factory::class),
            $this->createMock(C\ViewControl\Factory::class),
            $this->createMock(C\Chart\Factory::class),
            $this->createMock(C\Input\Factory::class),
            $this->createMock(C\Table\Factory::class),
            $this->createMock(C\MessageBox\Factory::class),
            $this->createMock(C\Card\Factory::class)
        );
    }
}
