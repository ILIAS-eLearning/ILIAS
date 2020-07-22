<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;

/**
 * Test on glyph implementation.
 */
class GlyphTest extends ILIAS_UI_TestBase
{
    public function getGlyphFactory()
    {
        return new \ILIAS\UI\Implementation\Component\Glyph\Factory();
    }

    public function getCounterFactory()
    {
        return new \ILIAS\UI\Implementation\Component\Counter\Factory();
    }

    public static $canonical_css_classes = array( C\Glyph\Glyph::SETTINGS => "glyphicon glyphicon-cog"
        , C\Glyph\Glyph::EXPAND => "glyphicon glyphicon-triangle-right"
        , C\Glyph\Glyph::COLLAPSE => "glyphicon glyphicon-triangle-bottom"
        , C\Glyph\Glyph::ADD => "glyphicon glyphicon-plus-sign"
        , C\Glyph\Glyph::REMOVE => "glyphicon glyphicon-minus-sign"
        , C\Glyph\Glyph::UP => "glyphicon glyphicon-circle-arrow-up"
        , C\Glyph\Glyph::DOWN => "glyphicon glyphicon-circle-arrow-down"
        , C\Glyph\Glyph::BACK => "glyphicon glyphicon-chevron-left"
        , C\Glyph\Glyph::NEXT => "glyphicon glyphicon-chevron-right"
        , C\Glyph\Glyph::SORT_ASCENDING => "glyphicon glyphicon-arrow-up"
        , C\Glyph\Glyph::SORT_DESCENDING => "glyphicon glyphicon-arrow-down"
        , C\Glyph\Glyph::USER => "glyphicon glyphicon-user"
        , C\Glyph\Glyph::MAIL => "glyphicon glyphicon-envelope"
        , C\Glyph\Glyph::NOTIFICATION => "glyphicon glyphicon-bell"
        , C\Glyph\Glyph::TAG => "glyphicon glyphicon-tag"
        , C\Glyph\Glyph::NOTE => "glyphicon glyphicon-pushpin"
        , C\Glyph\Glyph::COMMENT => "glyphicon glyphicon-comment"
        , C\Glyph\Glyph::LIKE => "glyphicon il-glyphicon-like"
        , C\Glyph\Glyph::LOVE => "glyphicon il-glyphicon-love"
        , C\Glyph\Glyph::DISLIKE => "glyphicon il-glyphicon-dislike"
        , C\Glyph\Glyph::LAUGH => "glyphicon il-glyphicon-laugh"
        , C\Glyph\Glyph::ASTOUNDED => "glyphicon il-glyphicon-astounded"
        , C\Glyph\Glyph::SAD => "glyphicon il-glyphicon-sad"
        , C\Glyph\Glyph::ANGRY => "glyphicon il-glyphicon-angry"
        , C\Glyph\Glyph::ATTACHMENT => "glyphicon glyphicon-paperclip"
        , C\Glyph\Glyph::RESET => "glyphicon glyphicon-repeat"
        , C\Glyph\Glyph::APPLY => "glyphicon glyphicon-ok"
        );

    public static $aria_labels = array(
          C\Glyph\Glyph::SETTINGS => "settings"
        , C\Glyph\Glyph::EXPAND => "expand_content"
        , C\Glyph\Glyph::COLLAPSE => "collapse_content"
        , C\Glyph\Glyph::ADD => "add"
        , C\Glyph\Glyph::REMOVE => "remove"
        , C\Glyph\Glyph::UP => "up"
        , C\Glyph\Glyph::DOWN => "down"
        , C\Glyph\Glyph::BACK => "back"
        , C\Glyph\Glyph::NEXT => "next"
        , C\Glyph\Glyph::SORT_ASCENDING => "sort_ascending"
        , C\Glyph\Glyph::SORT_DESCENDING => "sort_descending"
        , C\Glyph\Glyph::USER => "show_who_is_online"
        , C\Glyph\Glyph::MAIL => "mail"
        , C\Glyph\Glyph::NOTIFICATION => "notifications"
        , C\Glyph\Glyph::TAG => "tags"
        , C\Glyph\Glyph::NOTE => "notes"
        , C\Glyph\Glyph::COMMENT => "comments"
        , C\Glyph\Glyph::LIKE => "like"
        , C\Glyph\Glyph::LOVE => "love"
        , C\Glyph\Glyph::DISLIKE => "dislike"
        , C\Glyph\Glyph::LAUGH => "laugh"
        , C\Glyph\Glyph::ASTOUNDED => "astounded"
        , C\Glyph\Glyph::SAD => "sad"
        , C\Glyph\Glyph::ANGRY => "angry"
        , C\Glyph\Glyph::ATTACHMENT => "attachment"
        , C\Glyph\Glyph::RESET => "reset"
        , C\Glyph\Glyph::APPLY => "apply"
    );

    /**
     * @dataProvider glyph_type_provider
     */
    public function test_implements_factory_interface($factory_method)
    {
        $f = $this->getGlyphFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Glyph\\Factory", $f);
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Glyph\\Glyph", $f->$factory_method("http://www.ilias.de"));
    }

    /**
     * @dataProvider glyph_type_provider
     */
    public function test_glyph_types($factory_method)
    {
        $f = $this->getGlyphFactory();
        $g = $f->$factory_method();

        $this->assertNotNull($g);
        $this->assertEquals($factory_method, $g->getType());
    }

    /**
     * @dataProvider glyph_type_provider
     */
    public function test_glyph_action($factory_method)
    {
        $f = $this->getGlyphFactory();
        $g = $f->$factory_method("http://www.ilias.de");

        $this->assertNotNull($g);
        $this->assertEquals("http://www.ilias.de", $g->getAction());
    }

    /**
     * @dataProvider glyph_type_provider
     */
    public function test_glyph_no_action($factory_method)
    {
        $f = $this->getGlyphFactory();
        $g = $f->$factory_method();

        $this->assertNotNull($g);
        $this->assertEquals(null, $g->getAction());
    }

    /**
     * @dataProvider glyph_type_provider
     */
    public function test_with_unavailable_action($factory_method)
    {
        $f = $this->getGlyphFactory();
        $g = $f->$factory_method();
        $g2 = $f->$factory_method()->withUnavailableAction();

        $this->assertTrue($g->isActive());
        $this->assertFalse($g2->isActive());
    }

    /**
     * @dataProvider counter_type_provider
     */
    public function test_with_highlight($counter_type)
    {
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
    public function test_no_counter($factory_method)
    {
        $f = $this->getGlyphFactory();
        $g = $f->$factory_method();

        $this->assertCount(0, $g->getCounters());
    }

    /**
     * @dataProvider counter_type_provider
     */
    public function test_one_counter($counter_type)
    {
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

    public function test_two_counters()
    {
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
        $vals = array_map(function ($c) {
            return array($c->getType(), $c->getNumber());
        }, $counters);
        $this->assertContains(array("status", $number_s), $vals);
        $this->assertContains(array("novelty", $number_n), $vals);
    }

    public function test_only_two_counters()
    {
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
        $vals = array_map(function ($c) {
            return array($c->getType(), $c->getNumber());
        }, $counters);
        $this->assertContains(array("status", $number_s), $vals);
        $this->assertContains(array("novelty", $number_n2), $vals);
    }

    public function test_immutability_withCounter()
    {
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

    public function test_known_glyphs_only()
    {
        try {
            new \ILIAS\UI\Implementation\Component\Glyph\Glyph("FOO", "http://www.ilias.de");
            $this->assertFalse("We should not get here");
        } catch (\InvalidArgumentException $e) {
        }
    }

    public function glyph_type_provider()
    {
        return array( array(C\Glyph\Glyph::SETTINGS)
            , array(C\Glyph\Glyph::EXPAND)
            , array(C\Glyph\Glyph::COLLAPSE)
            , array(C\Glyph\Glyph::ADD)
            , array(C\Glyph\Glyph::REMOVE)
            , array(C\Glyph\Glyph::UP)
            , array(C\Glyph\Glyph::DOWN)
            , array(C\Glyph\Glyph::BACK)
            , array(C\Glyph\Glyph::NEXT)
            , array(C\Glyph\Glyph::SORT_ASCENDING)
            , array(C\Glyph\Glyph::SORT_DESCENDING)
            , array(C\Glyph\Glyph::USER)
            , array(C\Glyph\Glyph::MAIL)
            , array(C\Glyph\Glyph::NOTIFICATION)
            , array(C\Glyph\Glyph::TAG)
            , array(C\Glyph\Glyph::NOTE)
            , array(C\Glyph\Glyph::COMMENT)
            , array(C\Glyph\Glyph::LIKE)
            , array(C\Glyph\Glyph::LOVE)
            , array(C\Glyph\Glyph::DISLIKE)
            , array(C\Glyph\Glyph::LAUGH)
            , array(C\Glyph\Glyph::ASTOUNDED)
            , array(C\Glyph\Glyph::SAD)
            , array(C\Glyph\Glyph::ANGRY)
            , array(C\Glyph\Glyph::ATTACHMENT)
            , array(C\Glyph\Glyph::RESET)
            , array(C\Glyph\Glyph::APPLY)
            );
    }

    public function counter_type_provider()
    {
        return array( array("status")
            , array("novelty")
            );
    }

    /**
     * @dataProvider glyph_type_provider
     */
    public function test_render_simple($type)
    {
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
    public function test_render_with_unavailable_action($type)
    {
        $f = $this->getGlyphFactory();
        $r = $this->getDefaultRenderer();
        $c = $f->$type("http://www.ilias.de")->withUnavailableAction();

        $html = $this->normalizeHTML($r->render($c));

        $css_classes = self::$canonical_css_classes[$type];
        $aria_label = self::$aria_labels[$type];

        $expected = "<a class=\"glyph disabled\" aria-label=\"$aria_label\" " .
                    "aria-disabled=\"true\"><span class=\"$css_classes\" aria-hidden=\"true\"></span></a>";
        $this->assertEquals($expected, $html);
    }

    /**
     * @dataProvider counter_type_provider
     */
    public function test_render_withCounter($type)
    {
        $fg = $this->getGlyphFactory();
        $fc = $this->getCounterFactory();
        $r = $this->getDefaultRenderer();
        $c = $fg->mail("http://www.ilias.de")->withCounter($fc->$type(42));

        $html = $this->normalizeHTML($r->render($c));

        $css_classes = self::$canonical_css_classes[C\Glyph\Glyph::MAIL];
        $aria_label = self::$aria_labels[C\Glyph\Glyph::MAIL];

        $expected = "<a class=\"glyph\" href=\"http://www.ilias.de\" aria-label=\"$aria_label\">" .
                    "<span class=\"$css_classes\" aria-hidden=\"true\"></span>" .
                    "<span class=\"badge badge-notify il-counter-$type\">42</span>" .
                    "<span class=\"il-counter-spacer\">42</span>" .
                    "</a>";
        $this->assertEquals($expected, $html);
    }

    public function test_render_withTwoCounters()
    {
        $fg = $this->getGlyphFactory();
        $fc = $this->getCounterFactory();
        $r = $this->getDefaultRenderer();
        $c = $fg->mail("http://www.ilias.de")
                ->withCounter($fc->novelty(42))
                ->withCounter($fc->status(7));

        $html = $this->normalizeHTML($r->render($c));

        $css_classes = self::$canonical_css_classes[C\Glyph\Glyph::MAIL];
        $aria_label = self::$aria_labels[C\Glyph\Glyph::MAIL];
        $expected = "<a class=\"glyph\" href=\"http://www.ilias.de\" aria-label=\"$aria_label\">" .
                    "<span class=\"$css_classes\" aria-hidden=\"true\"></span>" .
                    "<span class=\"badge badge-notify il-counter-status\">7</span>" .
                    "<span class=\"badge badge-notify il-counter-novelty\">42</span>" .
                    "<span class=\"il-counter-spacer\">42</span>" .
                    "</a>";
        $this->assertEquals($expected, $html);
    }

    public function test_dont_render_counter()
    {
        $r = new \ILIAS\UI\Implementation\Component\Glyph\Renderer($this->getUIFactory(), $this->getTemplateFactory(), $this->getLanguage(), $this->getJavaScriptBinding());
        $f = $this->getCounterFactory();

        try {
            $r->render($f->status(0), $this->getDefaultRenderer());
            $this->assertFalse("This should not happen!");
        } catch (\LogicException $e) {
        }
    }

    /**
     * @dataProvider glyph_type_provider
     */
    public function test_render_with_on_load_code($type)
    {
        $f = $this->getGlyphFactory();
        $r = $this->getDefaultRenderer();
        $ids = array();
        $c = $f->$type("http://www.ilias.de")
                ->withOnLoadCode(function ($id) use (&$ids) {
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
