<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../../Base.php");

use \ILIAS\UI\Component\Symbol\Glyph as G;

/**
 * Test on glyph implementation.
 */
class GlyphTest extends ILIAS_UI_TestBase {
	public function getGlyphFactory() {
		return new \ILIAS\UI\Implementation\Component\Symbol\Glyph\Factory();
	}

	public function getCounterFactory() {
		return new \ILIAS\UI\Implementation\Component\Counter\Factory();
	}

	static $canonical_css_classes = array
		( G\Glyph::SETTINGS			=> "glyphicon glyphicon-cog"
		, G\Glyph::EXPAND			=> "glyphicon glyphicon-triangle-right"
		, G\Glyph::COLLAPSE			=> "glyphicon glyphicon-triangle-bottom"
		, G\Glyph::ADD				=> "glyphicon glyphicon-plus-sign"
		, G\Glyph::REMOVE			=> "glyphicon glyphicon-minus-sign"
		, G\Glyph::UP				=> "glyphicon glyphicon-circle-arrow-up"
		, G\Glyph::DOWN				=> "glyphicon glyphicon-circle-arrow-down"
		, G\Glyph::BACK 			=> "glyphicon glyphicon-chevron-left"
		, G\Glyph::NEXT				=> "glyphicon glyphicon-chevron-right"
		, G\Glyph::SORT_ASCENDING	=> "glyphicon glyphicon-arrow-up"
		, G\Glyph::SORT_DESCENDING	=> "glyphicon glyphicon-arrow-down"
		, G\Glyph::USER				=> "glyphicon glyphicon-user"
		, G\Glyph::MAIL 			=> "glyphicon glyphicon-envelope"
		, G\Glyph::NOTIFICATION		=> "glyphicon glyphicon-bell"
		, G\Glyph::TAG				=> "glyphicon glyphicon-tag"
		, G\Glyph::NOTE				=> "glyphicon glyphicon-pushpin"
		, G\Glyph::COMMENT			=> "glyphicon glyphicon-comment"
		, G\Glyph::LIKE				=> "glyphicon il-glyphicon-like"
		, G\Glyph::LOVE				=> "glyphicon il-glyphicon-love"
		, G\Glyph::DISLIKE			=> "glyphicon il-glyphicon-dislike"
		, G\Glyph::LAUGH			=> "glyphicon il-glyphicon-laugh"
		, G\Glyph::ASTOUNDED		=> "glyphicon il-glyphicon-astounded"
		, G\Glyph::SAD				=> "glyphicon il-glyphicon-sad"
		, G\Glyph::ANGRY			=> "glyphicon il-glyphicon-angry"
		, G\Glyph::ATTACHMENT		=> "glyphicon glyphicon-paperclip"
		, G\Glyph::RESET			=> "glyphicon glyphicon-repeat"
		, G\Glyph::APPLY			=> "glyphicon glyphicon-ok"
		, G\Glyph::SEARCH			=> "glyphicon glyphicon-search"
		, G\Glyph::HELP				=> "glyphicon glyphicon-question-sign"
		, G\Glyph::CALENDAR			=> "glyphicon glyphicon-calendar"
		, G\Glyph::TIME				=> "glyphicon glyphicon-time"
		);

	static $aria_labels = array(
		  G\Glyph::SETTINGS			=> "settings"
		, G\Glyph::EXPAND			=> "expand_content"
		, G\Glyph::COLLAPSE			=> "collapse_content"
		, G\Glyph::ADD				=> "add"
		, G\Glyph::REMOVE			=> "remove"
		, G\Glyph::UP				=> "up"
		, G\Glyph::DOWN				=> "down"
		, G\Glyph::BACK 			=> "back"
		, G\Glyph::NEXT				=> "next"
		, G\Glyph::SORT_ASCENDING	=> "sort_ascending"
		, G\Glyph::SORT_DESCENDING	=> "sort_descending"
		, G\Glyph::USER				=> "show_who_is_online"
		, G\Glyph::MAIL 			=> "mail"
		, G\Glyph::NOTIFICATION		=> "notifications"
		, G\Glyph::TAG				=> "tags"
		, G\Glyph::NOTE				=> "notes"
		, G\Glyph::COMMENT			=> "comments"
		, G\Glyph::LIKE				=> "like"
		, G\Glyph::LOVE				=> "love"
		, G\Glyph::DISLIKE			=> "dislike"
		, G\Glyph::LAUGH			=> "laugh"
		, G\Glyph::ASTOUNDED		=> "astounded"
		, G\Glyph::SAD				=> "sad"
		, G\Glyph::ANGRY			=> "angry"
		, G\Glyph::ATTACHMENT		=> "attachment"
		, G\Glyph::RESET			=> "reset"
		, G\Glyph::APPLY			=> "apply"
		, G\Glyph::SEARCH			=> "search"
		, G\Glyph::HELP				=> "help"
		, G\Glyph::CALENDAR			=> "calendar"
		, G\Glyph::TIME				=> "time"
	);

	/**
	 * @dataProvider glyph_type_provider
	 */
	public function test_implements_factory_interface($factory_method) {
		$f = $this->getGlyphFactory();

		$this->assertInstanceOf("ILIAS\\UI\\Component\\Symbol\\Glyph\\Factory", $f);
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Symbol\\Glyph\\Glyph", $f->$factory_method("http://www.ilias.de"));
	}

	/**
	 * @dataProvider glyph_type_provider
	 */
	public function test_glyph_types($factory_method) {
		$f = $this->getGlyphFactory();
		$g = $f->$factory_method();

		$this->assertNotNull($g);
		$this->assertEquals($factory_method, $g->getType());
	}

	/**
	 * @dataProvider glyph_type_provider
	 */
	public function test_glyph_action($factory_method) {
		$f = $this->getGlyphFactory();
		$g = $f->$factory_method("http://www.ilias.de");

		$this->assertNotNull($g);
		$this->assertEquals("http://www.ilias.de", $g->getAction());
	}

	/**
	 * @dataProvider glyph_type_provider
	 */
	public function test_glyph_no_action($factory_method) {
		$f = $this->getGlyphFactory();
		$g = $f->$factory_method();

		$this->assertNotNull($g);
		$this->assertEquals(null, $g->getAction());
	}

	/**
	 * @dataProvider glyph_type_provider
	 */
	public function test_with_unavailable_action($factory_method) {
		$f = $this->getGlyphFactory();
		$g = $f->$factory_method();
		$g2 = $f->$factory_method()->withUnavailableAction();

		$this->assertTrue($g->isActive());
		$this->assertFalse($g2->isActive());
	}

	/**
	 * @dataProvider counter_type_provider
	 */
	public function test_with_highlight($counter_type) {
		$gf = $this->getGlyphFactory();

		$g = $gf
			->mail()
			;
		$g2 = $g->withHighlight();

		$this->assertFalse($g->isHighlighted());
		$this->assertTrue($g2->isHighlighted());
	}

	/**
	 * @dataProvider glyph_type_provider
	 */
	public function test_no_counter($factory_method) {
		$f = $this->getGlyphFactory();
		$g = $f->$factory_method();

		$this->assertCount(0, $g->getCounters());
	}

	/**
	 * @dataProvider counter_type_provider
	 */
	public function test_one_counter($counter_type) {
		$gf = $this->getGlyphFactory();
		$cf = $this->getCounterFactory();
		$number = 1;

		$g = $gf
			->mail()
			->withCounter(
				$cf->$counter_type($number)
			);

		$counters = $g->getCounters();
		$this->assertCount(1, $counters);
		$c = $counters[0];
		$this->assertEquals($counter_type, $c->getType());
		$this->assertEquals($number, $c->getNumber());
	}

	public function test_two_counters() {
		$gf = $this->getGlyphFactory();
		$cf = $this->getCounterFactory();
		$number_s = 1;
		$number_n = 2;

		$g = $gf
			->mail()
			->withCounter(
				$cf->status($number_s)
			)
			->withCounter(
				$cf->novelty($number_n)
			);

		$counters = $g->getCounters();
		$this->assertCount(2, $counters);
		$vals = array_map(function($c) {
			return array($c->getType(), $c->getNumber());
		}, $counters);
		$this->assertContains(array("status", $number_s), $vals);
		$this->assertContains(array("novelty", $number_n), $vals);
	}

	public function test_only_two_counters() {
		$gf = $this->getGlyphFactory();
		$cf = $this->getCounterFactory();
		$number_s = 1;
		$number_n1 = 2;
		$number_n2 = 2;

		$g = $gf
			->mail()
			->withCounter(
				$cf->status($number_s)
			)
			->withCounter(
				$cf->novelty($number_n1)
			)
			->withCounter(
				$cf->novelty($number_n2)
			);

		$counters = $g->getCounters();
		$this->assertCount(2, $counters);
		$vals = array_map(function($c) {
			return array($c->getType(), $c->getNumber());
		}, $counters);
		$this->assertContains(array("status", $number_s), $vals);
		$this->assertContains(array("novelty", $number_n2), $vals);
	}

	public function test_immutability_withCounter() {
		$gf = $this->getGlyphFactory();
		$cf = $this->getCounterFactory();

		$g = $gf->mail();
		$g2 = $g
			->withCounter(
				$cf->novelty(0)
			);

		$counters = $g->getCounters();
		$this->assertCount(0, $counters);
	}

	public function test_known_glyphs_only() {
		$this->expectException(\InvalidArgumentException::class);
		new \ILIAS\UI\Implementation\Component\Symbol\Glyph\Glyph("FOO", "http://www.ilias.de");
	}

	public function glyph_type_provider() {
		return array
			( array(G\Glyph::SETTINGS)
			, array(G\Glyph::EXPAND)
			, array(G\Glyph::COLLAPSE)
			, array(G\Glyph::ADD)
			, array(G\Glyph::REMOVE)
			, array(G\Glyph::UP)
			, array(G\Glyph::DOWN)
			, array(G\Glyph::BACK)
			, array(G\Glyph::NEXT)
			, array(G\Glyph::SORT_ASCENDING)
			, array(G\Glyph::SORT_DESCENDING)
			, array(G\Glyph::USER)
			, array(G\Glyph::MAIL)
			, array(G\Glyph::NOTIFICATION)
			, array(G\Glyph::TAG)
			, array(G\Glyph::NOTE)
			, array(G\Glyph::COMMENT)
			, array(G\Glyph::LIKE)
			, array(G\Glyph::LOVE)
			, array(G\Glyph::DISLIKE)
			, array(G\Glyph::LAUGH)
			, array(G\Glyph::ASTOUNDED)
			, array(G\Glyph::SAD)
			, array(G\Glyph::ANGRY)
			, array(G\Glyph::ATTACHMENT)
			, array(G\Glyph::RESET)
			, array(G\Glyph::APPLY)
			, array(G\Glyph::SEARCH)
			, array(G\Glyph::HELP)
			, array(G\Glyph::CALENDAR)
			, array(G\Glyph::TIME)
			);
	}

	public function counter_type_provider() {
		return array
			( array("status")
			, array("novelty")
			);
	}

	/**
	 * @dataProvider glyph_type_provider
	 */
	public function test_render_simple($type) {
		$f = $this->getGlyphFactory();
		$r = $this->getDefaultRenderer();
		$c = $f->$type("http://www.ilias.de");

		$html = $this->normalizeHTML($r->render($c));

		$css_classes = self::$canonical_css_classes[$type];
		$aria_label = self::$aria_labels[$type];

		$expected = "<a class=\"glyph\" href=\"http://www.ilias.de\" aria-label=\"$aria_label\"><span class=\"$css_classes\" aria-hidden=\"true\"></span></a>";
		$this->assertEquals($expected, $html);
	}

	/**
	 * @dataProvider glyph_type_provider
	 */
	public function test_render_with_unavailable_action($type) {
		$f = $this->getGlyphFactory();
		$r = $this->getDefaultRenderer();
		$c = $f->$type("http://www.ilias.de")->withUnavailableAction();

		$html = $this->normalizeHTML($r->render($c));

		$css_classes = self::$canonical_css_classes[$type];
		$aria_label = self::$aria_labels[$type];

		$expected = "<a class=\"glyph disabled\" aria-label=\"$aria_label\" ".
					"aria-disabled=\"true\"><span class=\"$css_classes\" aria-hidden=\"true\"></span></a>";
		$this->assertEquals($expected, $html);
	}

	/**
 	 * @dataProvider counter_type_provider
	 */
	public function test_render_withCounter($type) {
		$fg = $this->getGlyphFactory();
		$fc = $this->getCounterFactory();
		$r = $this->getDefaultRenderer();
		$c = $fg->mail("http://www.ilias.de")->withCounter($fc->$type(42));

		$html = $this->normalizeHTML($r->render($c));

		$css_classes = self::$canonical_css_classes[G\Glyph::MAIL];
		$aria_label = self::$aria_labels[G\Glyph::MAIL];

		$expected = "<a class=\"glyph\" href=\"http://www.ilias.de\" aria-label=\"$aria_label\">".
					"<span class=\"$css_classes\" aria-hidden=\"true\"></span>".
					"<span class=\"badge badge-notify il-counter-$type\">42</span>".
					"<span class=\"il-counter-spacer\">42</span>".
					"</a>";
		$this->assertEquals($expected, $html);
	}

	public function test_render_withTwoCounters() {
		$fg = $this->getGlyphFactory();
		$fc = $this->getCounterFactory();
		$r = $this->getDefaultRenderer();
		$c = $fg->mail("http://www.ilias.de")
				->withCounter($fc->novelty(42))
				->withCounter($fc->status(7));

		$html = $this->normalizeHTML($r->render($c));

		$css_classes = self::$canonical_css_classes[G\Glyph::MAIL];
		$aria_label = self::$aria_labels[G\Glyph::MAIL];
		$expected = "<a class=\"glyph\" href=\"http://www.ilias.de\" aria-label=\"$aria_label\">".
					"<span class=\"$css_classes\" aria-hidden=\"true\"></span>".
					"<span class=\"badge badge-notify il-counter-status\">7</span>".
					"<span class=\"badge badge-notify il-counter-novelty\">42</span>".
					"<span class=\"il-counter-spacer\">42</span>".
					"</a>";
		$this->assertEquals($expected, $html);
	}

	public function test_dont_render_counter() {
		$this->expectException(\LogicException::class);
		$r = new \ILIAS\UI\Implementation\Component\Symbol\Glyph\Renderer(
			$this->getUIFactory(),
			$this->getTemplateFactory(),
			$this->getLanguage(),
			$this->getJavaScriptBinding(),
			$this->getRefinery()
		);
		$f = $this->getCounterFactory();

		$r->render($f->status(0), $this->getDefaultRenderer());
	}

	/**
	 * @dataProvider glyph_type_provider
	 */
	public function test_render_with_on_load_code($type) {
		$f = $this->getGlyphFactory();
		$r = $this->getDefaultRenderer();
		$ids = array();
		$c = $f->$type("http://www.ilias.de")
				->withOnLoadCode(function($id) use (&$ids) {
					$ids[] = $id;
					return "";
				});

		$html = $this->normalizeHTML($r->render($c));

		$this->assertCount(1, $ids);

		$css_classes = self::$canonical_css_classes[$type];
		$aria_label = self::$aria_labels[$type];

		$id = $ids[0];
		$expected = "<a class=\"glyph\" href=\"http://www.ilias.de\" aria-label=\"$aria_label\" id=\"$id\"><span class=\"$css_classes\" aria-hidden=\"true\"></span></a>";
		$this->assertEquals($expected, $html);
	}
}
