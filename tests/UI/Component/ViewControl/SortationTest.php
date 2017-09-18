<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\SignalGenerator;

/**
 * Test on icon implementation.
 */
class SortationTest extends ILIAS_UI_TestBase {
	protected $options = array (
		'internal_rating' => 'Best',
		'date_desc' => 'Most Recent',
		'date_asc' => 'Oldest',
	);

	private function getFactory() {
		$f = new \ILIAS\UI\Implementation\Factory();
		return $f->viewControl();
	}

	public function testConstruction() {
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

	public function testAttributes() {
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

	public function testRendering() {
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
	<li><a class="btn btn-link" href="?sortation=internal_rating" data-action="?sortation=internal_rating"  >Best</a></li>
	<li><a class="btn btn-link" href="?sortation=date_desc" data-action="?sortation=date_desc"  >Most Recent</a></li>
	<li><a class="btn btn-link" href="?sortation=date_asc" data-action="?sortation=date_asc"  >Oldest</a></li></ul></div>
</div>
EOT;
		return $this->normalizeHTML($expected);
	}

}
