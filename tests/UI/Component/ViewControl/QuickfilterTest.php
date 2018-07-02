<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use ILIAS\UI\Implementation\Component\SignalGenerator;

/**
 * Test on icon implementation.
 */
class QuickfilterTest extends ILIAS_UI_TestBase {
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
		$quickfilter = $f->quickfilter($this->options);
		$this->assertInstanceOf(
			"ILIAS\\UI\\Component\\ViewControl\\Quickfilter",
			$quickfilter
		);
		$this->assertInstanceOf(
			"ILIAS\\UI\\Component\\Signal",
			$quickfilter->getSelectSignal()
		);
	}

	public function testAttributes() {
		$f = $this->getFactory();
		$quickfilter = $f->quickfilter($this->options);

		$this->assertEquals($this->options, $quickfilter->getOptions());
		$this->assertEquals('label', $quickfilter->withLabel('label')->getLabel());

		$quickfilter = $quickfilter->withTargetURL('#', 'param');
		$this->assertEquals('#', $quickfilter->getTargetURL());
		$this->assertEquals('param', $quickfilter->getParameterName());

		$this->assertEquals(array(), $quickfilter->getTriggeredSignals());
		$generator = new SignalGenerator();
		$signal = $generator->create();
		$this->assertEquals(
			$signal,
			$quickfilter->withOnSort($signal)->getTriggeredSignals()[0]->getSignal()
		);

		$quickfilter = $quickfilter->withDefaultValue('default');
		$this->assertEquals('default', $quickfilter->getDefaultValue());
	}

	public function testRendering() {
		$f = $this->getFactory();
		$r = $this->getDefaultRenderer();
		$quickfilter = $f->quickfilter($this->options);

		$html = $this->normalizeHTML($r->render($quickfilter));
		$this->assertEquals(
			$this->getQuickfilterExpectedHTML(),
			$html
		);
	}

	protected function getQuickfilterExpectedHTML()
	{
		$expected = <<<EOT
<div class="il-viewcontrol-quickfilter" id=""><div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-haspopup="true" aria-expanded="false" > <span class="caret"></span></button><ul class="dropdown-menu">
	<li><a class="btn btn-link" href="?quickfilter=internal_rating" data-action="?quickfilter=internal_rating"  >Best</a></li>
	<li><a class="btn btn-link" href="?quickfilter=date_desc" data-action="?quickfilter=date_desc"  >Most Recent</a></li>
	<li><a class="btn btn-link" href="?quickfilter=date_asc" data-action="?quickfilter=date_asc"  >Oldest</a></li></ul></div>
</div>
EOT;
		return $this->normalizeHTML($expected);
	}

}
