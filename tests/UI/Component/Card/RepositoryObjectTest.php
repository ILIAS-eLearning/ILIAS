<?php

/* Copyright (c) 2018 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation as I;


/**
 * TODO change the custom icons to standard icons
 * Test on Repository Object card implementation.
 */
class RepositoryObjectTest extends ILIAS_UI_TestBase {

	/**
	 * @return \ILIAS\UI\Implementation\Factory
	 */
	public function getFactory() {
		return new \ILIAS\UI\Implementation\Factory(
			$this->createMock(C\Counter\Factory::class),
			$this->createMock(C\Glyph\Factory::class),
			$this->createMock(C\Button\Factory::class),
			$this->createMock(C\Listing\Factory::class),
			$this->createMock(C\Image\Factory::class),
			$this->createMock(C\Panel\Factory::class),
			$this->createMock(C\Modal\Factory::class),
			$this->createMock(C\Dropzone\Factory::class),
			$this->createMock(C\Popover\Factory::class),
			$this->createMock(C\Divider\Factory::class),
			$this->createMock(C\Link\Factory::class),
			$this->createMock(C\Dropdown\Factory::class),
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

	private function getCardFactory() {
		return new \ILIAS\UI\Implementation\Component\Card\Factory();
	}

	private function getBaseCard() {
		$cf = $this->getCardFactory();
		$image = new I\Component\Image\Image("standard", "src", "alt");

		return $cf->repositoryObject("Card Title", $image);
	}

	public function test_implements_factory_interface() {
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Card\\RepositoryObject", $this->getBaseCard());
	}

	public function test_with_object_icon() {
		//TODO swap these icons
		//$icon = new I\Component\Icon\Standard("crs", 'Course', 'responsive', false);
		$icon = new I\Component\Icon\Custom("templates/default/images/icon_crs.svg", 'Course', 'responsive',false);
		$card = $this->getBaseCard();
		$card = $card->withObjectIcon($icon);

		$this->assertEquals($card->getObjectIcon(), $icon);
	}

	public function test_with_progress() {
		$progressmeter = new I\Component\Chart\ProgressMeter\Mini(100,70);
		$card = $this->getBaseCard();
		$card = $card->withProgress($progressmeter);

		$this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\ProgressMeter\\Mini", $progressmeter);
		$this->assertEquals($progressmeter, $card->getProgress());
	}

	public function test_with_certificate_icon() {
		$card = $this->getBaseCard();
		$card_with_cert_true = $card->withCertificateIcon(true);
		$card_with_cert_false = $card->withCertificateIcon(false);

		$this->assertNull($card->getCertificateIcon());
		$this->assertTrue($card_with_cert_true->getCertificateIcon());
		$this->assertFalse($card_with_cert_false->getCertificateIcon());
	}

	public function test_with_actions()
	{
		$f = $this->getFactory();
		$items = array(
			$f->button()->shy("Go to Course", "#"),
			$f->button()->shy("Go to Portfolio", "#"),
			$f->divider()->horizontal(),
			$f->button()->shy("ilias.de", "http://www.ilias.de")
		);

		$dropdown = new I\Component\Dropdown\Standard($items);
		$card = $this->getBaseCard();
		$card = $card->withActions($dropdown);

		$this->assertInstanceOf("ILIAS\\UI\\Component\\Dropdown\\Standard", $dropdown);
		$this->assertEquals($card->getActions(), $dropdown);
	}

	public function test_render_with_object_icon() {
		$r = $this->getDefaultRenderer();
		//TODO swap these icons
		//$icon = new I\Component\Icon\Standard("crs", 'Course', 'responsive', false);
		$icon = new I\Component\Icon\Custom("templates/default/images/icon_crs.svg", 'Course', 'responsive',false);
		$c = $this->getBaseCard();
		$c = $c->withObjectIcon($icon);

		$html = $r->render($c);
		
		$expected_html = <<<EOT
<div class="il-card thumbnail">
	<div class="il-card-repository-head">
		<div class="row">
			<div class="col-xs-3 col-sm-3">
				<div class="icon custom responsive" aria-label="Course">
					<img src="templates/default/images/icon_crs.svg" />
				</div>
			</div>
			<div class="col-xs-3 col-sm-3">
				
			</div>
			<div class="col-xs-3 col-sm-3">
			</div>
			<div class="il-card-repository-dropdown col-xs-3 col-sm-3 text-right">
				
			</div>
		</div>
	</div>
	<img src="src" class="img-standard" alt="alt" />
	<div class="card-no-highlight"></div>
	<div class="caption">
		<h5 class="card-title">Card Title</h5>
	</div>
</div>
EOT;

		$this->assertHTMLEquals($expected_html, $html);
	}

	public function test_render_with_certificate_icon() {
		$r = $this->getDefaultRenderer();
		$c = $this->getBaseCard();

		//TODO get skin fail?
		$c = $c->withCertificateIcon(true);

		$html = $r->render($c);

		$expected_html = <<<EOT
<div class="il-card thumbnail">
	<div class="il-card-repository-head">
		<div class="row">
			<div class="col-xs-3 col-sm-3">
				
			</div>
			<div class="col-xs-3 col-sm-3">
				<div class="icon custom responsive" aria-label="Certificate">
					<img src="./templates/default/images/icon_cert.svg" />
				</div>
			</div>
			<div class="col-xs-3 col-sm-3">
			</div>
			<div class="il-card-repository-dropdown col-xs-3 col-sm-3 text-right">
				
			</div>
		</div>
	</div>
	<img src="src" class="img-standard" alt="alt" />
	<div class="card-no-highlight"></div>
	<div class="caption">
		<h5 class="card-title">Card Title</h5>
	</div>
</div>
EOT;

		$this->assertHTMLEquals($expected_html, $html);
	}

	public function test_render_with_progressmeter() {
		$r = $this->getDefaultRenderer();
		$c = $this->getBaseCard();
		$prg = new I\Component\Chart\ProgressMeter\Mini(100,80);
		$c = $c->withProgress($prg);

		$html = $r->render($c);

		$expected_html = <<<EOT
<div class="il-card thumbnail">
	<div class="il-card-repository-head">
		<div class="row">
			<div class="col-xs-3 col-sm-3">
				
			</div>
			<div class="col-xs-3 col-sm-3">
				<div class="il-chart-progressmeter-box il-chart-progressmeter-mini">
					<div class="il-chart-progressmeter-mini-container">
						<div class="il-chart-progressmeter-marker-box hidden" style="transform: rotate(0deg)">
							<div class="il-chart-progressmeter-mini-marker"></div>
						</div>
						<div class="il-chart-progressmeter-outerbox   il-chart-progressmeter-bar-no-success  ">
							<div class="il-chart-progressmeter-pointer pointer-1" style="transform: rotate(-25deg) skew(28.584deg)"></div>
							<div class="il-chart-progressmeter-pointer pointer-2" style="transform: rotate(35.416deg) skew(28.584deg)"></div>
							<div class="il-chart-progressmeter-pointer pointer-3" style="transform: rotate(95.832deg) skew(28.584deg)"></div>
							<div class="il-chart-progressmeter-cover">
								<div class="il-chart-progressmeter-text-container"></div>
							</div>
						</div>
					</div>
			</div>
			</div>
			<div class="col-xs-3 col-sm-3">
			</div>
			<div class="il-card-repository-dropdown col-xs-3 col-sm-3 text-right">
				
			</div>
		</div>
	</div>
	<img src="src" class="img-standard" alt="alt" />
	<div class="card-no-highlight"></div>
	<div class="caption">
		<h5 class="card-title">Card Title</h5>
	</div>
</div>
EOT;

		$this->assertHTMLEquals($expected_html, $html);
	}

	public function test_render_with_actions() {
		$f = $this->getFactory();
		$r = $this->getDefaultRenderer();
		$c = $this->getBaseCard();
		$items = array(
			new I\Component\Button\Shy("Visit ILIAS", "https://www.ilias.de")
		);
		$dropdown = new I\Component\Dropdown\Standard($items);
		$c = $c->withActions($dropdown);
		$html = $r->render($c);

		$expected_html = <<<EOT
<div class="il-card thumbnail">
	
	<div class="il-card-repository-head">
		<div class="row">
			<div class="col-xs-3 col-sm-3">
				
			</div>
			<div class="col-xs-3 col-sm-3">
				
			</div>
			<div class="col-xs-3 col-sm-3">
			</div>
			<div class="il-card-repository-dropdown col-xs-3 col-sm-3 text-right">
				<div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-haspopup="true" aria-expanded="false" > <span class="caret"></span></button>
<ul class="dropdown-menu">
	<li><button class="btn btn-link" data-action="https://www.ilias.de" id="id_1"  >Visit ILIAS</button>
</li>
</ul>
</div>
			</div>
		</div>
	</div>
<img src="src" class="img-standard" alt="alt" />
	<div class="card-no-highlight"></div>
	<div class="caption">
		<h5 class="card-title">Card Title</h5>
	</div>
	
</div>
EOT;

		$this->assertHTMLEquals($expected_html, $html);
	}

}