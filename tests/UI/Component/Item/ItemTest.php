<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;


/**
 * Test items
 */
class ItemTest extends ILIAS_UI_TestBase {

	/**
	 * @return \ILIAS\UI\Implementation\Factory
	 */
	public function getFactory() {
		return new \ILIAS\UI\Implementation\Factory();
	}

	public function test_implements_factory_interface() {
		$f = $this->getFactory();

		$this->assertInstanceOf( "ILIAS\\UI\\Component\\Item\\Standard", $f->item()->standard("title"));
	}

	public function test_get_title() {
		$f = $this->getFactory();
		$c = $f->item()->standard("title");

		$this->assertEquals($c->getTitle(), "title");
	}

	public function test_with_description() {
		$f = $this->getFactory();

		$c = $f->item()->standard("title")->withDescription("description");

		$this->assertEquals($c->getDescription(), "description");
	}

	public function test_with_properties() {
		$f = $this->getFactory();

		$props = array("prop1" => "val1", "prop2" => "val2");
		$c = $f->item()->standard("title")->withProperties($props);

		$this->assertEquals($c->getProperties(), $props);
	}

	public function test_with_actions() {
		$f = $this->getFactory();

		$actions = $f->dropdown()->standard(array(
			$f->button()->shy("ILIAS", "https://www.ilias.de"),
			$f->button()->shy("GitHub", "https://www.github.com")
		));
		$c = $f->item()->standard("title")->withActions($actions);

		$this->assertEquals($c->getActions(), $actions);
	}

	public function test_with_color() {
		$f = $this->getFactory();
		$df = new \ILIAS\Data\Factory();

		$color = $df->color('#ff00ff');

		$c = $f->item()->standard("title")->withColor($color);

		$this->assertEquals($c->getColor(), $color);
	}

	public function test_with_lead_image() {
		$f = $this->getFactory();

		$image = $f->image()->standard("src","str");

		$c = $f->item()->standard("title")->withLeadImage($image);

		$this->assertEquals($c->getLead(), $image);
	}

	public function test_with_lead_text() {
		$f = $this->getFactory();

		$c = $f->item()->standard("title")->withLeadText("text");

		$this->assertEquals($c->getLead(), "text");
	}

	public function test_with_no_lead() {
		$f = $this->getFactory();

		$c = $f->item()->standard("title")->withLeadText("text")->withNoLead();

		$this->assertEquals($c->getLead(), null);
	}

	public function test_render_base() {
		$f = $this->getFactory();
		$r = $this->getDefaultRenderer();

		$actions = $f->dropdown()->standard(array(
			$f->button()->shy("ILIAS", "https://www.ilias.de"),
			$f->button()->shy("GitHub", "https://www.github.com")
		));
		$c = $f->item()->standard("Item Title")
			->withActions($actions)
			->withProperties(array(
				"Origin" => "Course Title 1",
				"Last Update" => "24.11.2011",
				"Location" => "Room 123, Main Street 44, 3012 Bern"))
			->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.");

		$html = $r->render($c);
		$expected = <<<EOT
		<div class="il-item il-std-item ">
			<h5>Item Title</h5>
			<div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-haspopup="true" aria-expanded="false" > <span class="caret"></span></button>
<ul class="dropdown-menu">
	<li><a class="btn btn-link" href="https://www.ilias.de" data-action="https://www.ilias.de"  >ILIAS</a></li>
	<li><a class="btn btn-link" href="https://www.github.com" data-action="https://www.github.com"  >GitHub</a></li>
</ul>
</div>
			<div class="il-item-description">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.</div>		
			<hr class="il-item-divider" />
			<div class="row">
				<div class="col-md-6">
					<div class="row">
						<div class="col-sm-5 il-item-property-name">Origin</div>
						<div class="col-sm-7 il-item-property-value il-multi-line-cap-3">Course Title 1</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="row">
						<div class="col-sm-5 il-item-property-name">Last Update</div>
						<div class="col-sm-7 il-item-property-value il-multi-line-cap-3">24.11.2011</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6">
					<div class="row">
						<div class="col-sm-5 il-item-property-name">Location</div>
						<div class="col-sm-7 il-item-property-value il-multi-line-cap-3">Room 123, Main Street 44, 3012 Bern</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="row">
						<div class="col-sm-5 il-item-property-name"></div>
						<div class="col-sm-7 il-item-property-value il-multi-line-cap-3"></div>
					</div>
				</div>
			</div>
</div>
EOT;

		$this->assertHTMLEquals($expected, $html);
	}

	public function test_render_lead_image() {
		$f = $this->getFactory();
		$r = $this->getDefaultRenderer();

		$image = $f->image()->standard("src","str");

		$c = $f->item()->standard("title")->withLeadImage($image);

		$html = $r->render($c);

		$expected = <<<EOT
<div class="il-item il-std-item ">
	<div class="row">
		<div class="col-sm-3">
			<img src="src" class="img-standard" alt="str" />
		</div>
		<div class="col-sm-9">
			<h5>title</h5>
		</div>
	</div>
</div>
EOT;

		$this->assertHTMLEquals($expected, $html);
	}

	public function test_render_lead_text_and_color() {
		$f = $this->getFactory();
		$r = $this->getDefaultRenderer();
		$df = new \ILIAS\Data\Factory();

		$color = $df->color('#ff00ff');

		$c = $f->item()->standard("title")->withColor($color)->withLeadText("lead");

		$html = $r->render($c);

		$expected = <<<EOT
<div class="il-item il-std-item il-item-marker " style="border-color:#ff00ff">
	<div class="row">
		<div class="col-sm-3">
			<h5>lead</h5>
		</div>
		<div class="col-sm-9">
			<h5>title</h5>
		</div>
	</div>
</div>
EOT;

		$this->assertHTMLEquals($expected, $html);
	}

	public function test_shy_title_and_property() {
		$f = $this->getFactory();
		$r = $this->getDefaultRenderer();
		$df = new \ILIAS\Data\Factory();

		$color = $df->color('#ff00ff');

		$c = $f->item()->standard($f->button()->shy("ILIAS", "https://www.ilias.de"))
			->withProperties(array("test" => $f->button()->shy("GitHub", "https://www.github.com")));

		$html = $r->render($c);
		$expected = <<<EOT
<div class="il-item il-std-item ">
			<h5><a class="btn btn-link" href="https://www.ilias.de" data-action="https://www.ilias.de"  >ILIAS</a></h5>
			<hr class="il-item-divider" />
			<div class="row">
				<div class="col-md-6">
					<div class="row">
						<div class="col-sm-5 il-item-property-name">test</div>
						<div class="col-sm-7 il-item-property-value il-multi-line-cap-3"><a class="btn btn-link" href="https://www.github.com" data-action="https://www.github.com"  >GitHub</a></div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="row">
						<div class="col-sm-5 il-item-property-name"></div>
						<div class="col-sm-7 il-item-property-value il-multi-line-cap-3"></div>
					</div>
				</div>
			</div>
</div>
EOT;

		$this->assertHTMLEquals($expected, $html);
	}

}
