<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;

/**
 * Test on Message Box implementation.
 */
class MessageBoxTest extends ILIAS_UI_TestBase {
	public function getMessageBoxFactory() {
		return new \ILIAS\UI\Implementation\Component\MessageBox\Factory();
	}

	public function messagebox_type_provider() {
		return array
		( array(C\MessageBox\MessageBox::FAILURE)
		, array(C\MessageBox\MessageBox::SUCCESS)
		, array(C\MessageBox\MessageBox::INFO)
		, array(C\MessageBox\MessageBox::CONFIRMATION)
		);
	}

	static $canonical_css_classes = array
	( C\MessageBox\MessageBox::FAILURE			=> "alert-danger"
	, C\MessageBox\MessageBox::SUCCESS			=> "alert-success"
	, C\MessageBox\MessageBox::INFO				=> "alert-info"
	, C\MessageBox\MessageBox::CONFIRMATION		=> "alert-warning"
	);


	/**
	 * @dataProvider messagebox_type_provider
	 */
	public function test_implements_factory_interface($factory_method) {
		$f = $this->getMessageBoxFactory();

		$this->assertInstanceOf("ILIAS\\UI\\Component\\MessageBox\\Factory", $f);
		$this->assertInstanceOf("ILIAS\\UI\\Component\\MessageBox\\MessageBox", $f->$factory_method("Lorem ipsum dolor sit amet."));
	}

	/**
	 * @dataProvider messagebox_type_provider
	 */
	public function test_messagebox_types($factory_method) {
		$f = $this->getMessageBoxFactory();
		$g = $f->$factory_method("Lorem ipsum dolor sit amet.");

		$this->assertNotNull($g);
		$this->assertEquals($factory_method, $g->getType());
	}

	/**
	 * @dataProvider messagebox_type_provider
	 */
	public function test_messagebox_messagetext($factory_method) {
		$f = $this->getMessageBoxFactory();
		$g = $f->$factory_method("Lorem ipsum dolor sit amet.");

		$this->assertNotNull($g);
		$this->assertEquals("Lorem ipsum dolor sit amet.", $g->getMessageText());
	}

	public function test_with_buttons() {
		$f = $this->getMessageBoxFactory();
		$g = $f->confirmation("Lorem ipsum dolor sit amet.");

		$b = new \ILIAS\UI\Implementation\Component\Button\Factory();
		$buttons = [$b->standard("Confirm", "#"), $b->standard("Cancel", "#")];
		$g2 = $g->withButtons($buttons);

		$this->assertFalse(count($g->getButtons()) > 0);
		$this->assertTrue(count($g2->getButtons()) > 0);
	}

	public function test_with_links() {
		$f = $this->getMessageBoxFactory();
		$g = $f->confirmation("Lorem ipsum dolor sit amet.");

		$l = new \ILIAS\UI\Implementation\Component\Link\Factory();
		$links = [
			$l->standard("Open Exercise Assignment", "#"),
			$l->standard("Open other screen", "#"),
		];
		$g2 = $g->withLinks($links);

		$this->assertFalse(count($g->getLinks()) > 0);
		$this->assertTrue(count($g2->getLinks()) > 0);
	}

	public function test_with_buttons_and_links() {
		$f = $this->getMessageBoxFactory();
		$g = $f->confirmation("Lorem ipsum dolor sit amet.");

		$b = new \ILIAS\UI\Implementation\Component\Button\Factory();
		$buttons = [$b->standard("Confirm", "#"), $b->standard("Cancel", "#")];
		$l = new \ILIAS\UI\Implementation\Component\Link\Factory();
		$links = [
			$l->standard("Open Exercise Assignment", "#"),
			$l->standard("Open other screen", "#"),
		];
		$g2 = $g->withButtons($buttons)->withLinks($links);

		$this->assertFalse(count($g->getButtons()) > 0 && count($g->getLinks()) > 0);
		$this->assertTrue(count($g2->getButtons()) > 0 && count($g2->getLinks()) > 0);
	}

	/**
	 * @dataProvider messagebox_type_provider
	 */
	public function test_render_simple($factory_method) {
		$f = $this->getMessageBoxFactory();
		$r = $this->getDefaultRenderer();
		$g = $f->$factory_method("Lorem ipsum dolor sit amet.");
		$css_classes = self::$canonical_css_classes[$factory_method];

		$html = $this->normalizeHTML($r->render($g));
		$expected = "<div class=\"alert $css_classes\" role=\"alert\">" .
					"<h5 class=\"ilAccHeadingHidden\"><a id=\"il_message_focus\" name=\"il_message_focus\">" .
					$g->getType() . "_message</a></h5>Lorem ipsum dolor sit amet.</div>";
		$this->assertHTMLEquals($expected, $html);
	}

	/**
	 * @dataProvider messagebox_type_provider
	 */
	public function test_render_with_buttons($factory_method) {
		$f = $this->getMessageBoxFactory();
		$r = $this->getDefaultRenderer();
		$css_classes = self::$canonical_css_classes[$factory_method];

		$b = new \ILIAS\UI\Implementation\Component\Button\Factory();
		$buttons = [$b->standard("Confirm", "#"), $b->standard("Cancel", "#")];

		$g = $f->$factory_method("Lorem ipsum dolor sit amet.")->withButtons($buttons);
		$html = $this->normalizeHTML($r->render($g));
		echo $html; exit;
		$expected = "";
		$this->assertHTMLEquals($expected, $html);
	}

	/**
	 * @dataProvider messagebox_type_provider
	 */
	public function test_render_with_links($factory_method) {
		$f = $this->getMessageBoxFactory();
		$r = $this->getDefaultRenderer();
		$css_classes = self::$canonical_css_classes[$factory_method];

		$l = new \ILIAS\UI\Implementation\Component\Link\Factory();
		$links = [
			$l->standard("Open Exercise Assignment", "#"),
			$l->standard("Open other screen", "#"),
		];

		$g = $f->$factory_method("Lorem ipsum dolor sit amet.")->withLinks($links);
		$html = $this->normalizeHTML($r->render($g));
		$expected = "";
		$this->assertHTMLEquals($expected, $html);
	}

	/**
	 * @dataProvider messagebox_type_provider
	 */
	public function test_render_with_buttons_and_links($factory_method) {
		$f = $this->getMessageBoxFactory();
		$r = $this->getDefaultRenderer();
		$g = $f->$factory_method("Lorem ipsum dolor sit amet.");
		$css_classes = self::$canonical_css_classes[$factory_method];

		$b = new \ILIAS\UI\Implementation\Component\Button\Factory();
		$buttons = [$b->standard("Confirm", "#"), $b->standard("Cancel", "#")];
		$l = new \ILIAS\UI\Implementation\Component\Link\Factory();
		$links = [
			$l->standard("Open Exercise Assignment", "#"),
			$l->standard("Open other screen", "#"),
		];

		$g = $f->$factory_method("Lorem ipsum dolor sit amet.")->withButtons($buttons)->withLinks($links);
		$html = $this->normalizeHTML($r->render($g));
		$expected = "";
		$this->assertHTMLEquals($expected, $html);
	}

}