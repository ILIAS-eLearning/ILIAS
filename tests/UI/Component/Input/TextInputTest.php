<?php

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

class TextInputTest extends ILIAS_UI_TestBase {
	protected function buildFactory() {
		return new ILIAS\UI\Implementation\Component\Input\Factory;
	}

	public function test_implements_factory_interface() {
	    $f = $this->buildFactory();

		$text = $f->text("label", "byline");
	}

	public function test_render() {
	    $f = $this->buildFactory();
		$label = "label";
		$byline = "byline";
		$ids = [];
		$text = $f->text($label, $byline)
			->withOnLoadCode(function($id) use (&$ids) {
				$ids[] = $id;
				return "";
			});

		$r = $this->getDefaultRenderer();
		$html = $this->normalizeHTML($r->render($text));

		$this->assertCount(1, $ids);
		$name = $ids[0];

		$expected = "<div class=\"form-group row\" id=\"$name\">".
					"	<label for=\"$name\" class=\"control-label col-sm-3\">$label</label>".
					"	<div class=\"col-sm-9\">".
					"		<input type=\"text\" name=\"$name\" class=\"form-control form-control-sm\" />".
					"		<div class=\"help-block\">$byline</div>".
					"	</div>".
					"</div>";
		$this->assertEquals($expected, $html);
	}
}
