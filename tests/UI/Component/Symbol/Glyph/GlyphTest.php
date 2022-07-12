<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Component\Symbol\Glyph as G;
use ILIAS\UI\Component\Counter as C;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\Symbol\Glyph\Glyph;
use ILIAS\UI\Implementation\Component\Symbol\Glyph\Renderer;

/**
 * Test on glyph implementation.
 */
class GlyphTest extends ILIAS_UI_TestBase
{
    public function getGlyphFactory() : G\Factory
    {
        return new I\Symbol\Glyph\Factory();
    }

    public function getCounterFactory() : C\Factory
    {
        return new I\Counter\Factory();
    }

    public static array $canonical_css_classes = array(
        G\Glyph::SETTINGS => "glyphicon glyphicon-cog",
        G\Glyph::EXPAND => "glyphicon glyphicon-triangle-right",
        G\Glyph::COLLAPSE => "glyphicon glyphicon-triangle-bottom",
        G\Glyph::ADD => "glyphicon glyphicon-plus-sign",
        G\Glyph::REMOVE => "glyphicon glyphicon-minus-sign",
        G\Glyph::UP => "glyphicon glyphicon-circle-arrow-up",
        G\Glyph::DOWN => "glyphicon glyphicon-circle-arrow-down",
        G\Glyph::BACK => "glyphicon glyphicon-chevron-left",
        G\Glyph::NEXT => "glyphicon glyphicon-chevron-right",
        G\Glyph::SORT_ASCENDING => "glyphicon glyphicon-arrow-up",
        G\Glyph::SORT_DESCENDING => "glyphicon glyphicon-arrow-down",
        G\Glyph::USER => "glyphicon glyphicon-user",
        G\Glyph::MAIL => "glyphicon glyphicon-envelope",
        G\Glyph::NOTIFICATION => "glyphicon glyphicon-bell",
        G\Glyph::TAG => "glyphicon glyphicon-tag",
        G\Glyph::NOTE => "glyphicon glyphicon-pushpin",
        G\Glyph::COMMENT => "glyphicon glyphicon-comment",
        G\Glyph::BRIEFCASE => "glyphicon glyphicon-briefcase",
        G\Glyph::LIKE => "glyphicon il-glyphicon-like",
        G\Glyph::LOVE => "glyphicon il-glyphicon-love",
        G\Glyph::DISLIKE => "glyphicon il-glyphicon-dislike",
        G\Glyph::LAUGH => "glyphicon il-glyphicon-laugh",
        G\Glyph::ASTOUNDED => "glyphicon il-glyphicon-astounded",
        G\Glyph::SAD => "glyphicon il-glyphicon-sad",
        G\Glyph::ANGRY => "glyphicon il-glyphicon-angry",
        G\Glyph::EYEOPEN => "glyphicon glyphicon-eye-open",
        G\Glyph::EYECLOSED => "glyphicon glyphicon-eye-close",
        G\Glyph::ATTACHMENT => "glyphicon glyphicon-paperclip",
        G\Glyph::RESET => "glyphicon glyphicon-repeat",
        G\Glyph::APPLY => "glyphicon glyphicon-ok",
        G\Glyph::SEARCH => "glyphicon glyphicon-search",
        G\Glyph::HELP => "glyphicon glyphicon-question-sign",
        G\Glyph::CALENDAR => "glyphicon glyphicon-calendar",
        G\Glyph::TIME => "glyphicon glyphicon-time",
        G\Glyph::CLOSE => "glyphicon glyphicon-remove",
        G\Glyph::MORE => "glyphicon glyphicon-option-horizontal",
        G\Glyph::DISCLOSURE => "glyphicon glyphicon-option-vertical",
        G\Glyph::LANGUAGE => "glyphicon glyphicon-lang",
        G\Glyph::LOGIN => "glyphicon glyphicon-login",
        G\Glyph::LOGOUT => "glyphicon glyphicon-logout",
        G\Glyph::BULLETLIST => "glyphicon glyphicon-bulletlist",
        G\Glyph::NUMBEREDLIST => "glyphicon glyphicon-numberedlist",
        G\Glyph::LISTINDENT => "glyphicon glyphicon-listindent",
        G\Glyph::LISTOUTDENT => "glyphicon glyphicon-listoutdent",
        G\Glyph::FILTER => "glyphicon glyphicon-filter",
        G\Glyph::COLLAPSE_HORIZONTAL => "glyphicon glyphicon-triangle-left"
    );

    public static array $aria_labels = array(
        G\Glyph::SETTINGS => "settings",
        G\Glyph::EXPAND => "expand_content",
        G\Glyph::COLLAPSE => "collapse_content",
        G\Glyph::ADD => "add",
        G\Glyph::REMOVE => "remove",
        G\Glyph::UP => "up",
        G\Glyph::DOWN => "down",
        G\Glyph::BACK => "back",
        G\Glyph::NEXT => "next",
        G\Glyph::SORT_ASCENDING => "sort_ascending",
        G\Glyph::SORT_DESCENDING => "sort_descending",
        G\Glyph::USER => "show_who_is_online",
        G\Glyph::MAIL => "mail",
        G\Glyph::NOTIFICATION => "notifications",
        G\Glyph::TAG => "tags",
        G\Glyph::NOTE => "notes",
        G\Glyph::COMMENT => "comments",
        G\Glyph::BRIEFCASE => "briefcase",
        G\Glyph::LIKE => "like",
        G\Glyph::LOVE => "love",
        G\Glyph::DISLIKE => "dislike",
        G\Glyph::LAUGH => "laugh",
        G\Glyph::ASTOUNDED => "astounded",
        G\Glyph::SAD => "sad",
        G\Glyph::ANGRY => "angry",
        G\Glyph::EYEOPEN => "eyeopened",
        G\Glyph::EYECLOSED => "eyeclosed",
        G\Glyph::ATTACHMENT => "attachment",
        G\Glyph::RESET => "reset",
        G\Glyph::APPLY => "apply",
        G\Glyph::SEARCH => "search",
        G\Glyph::HELP => "help",
        G\Glyph::CALENDAR => "calendar",
        G\Glyph::TIME => "time",
        G\Glyph::CLOSE => "close",
        G\Glyph::MORE => "show_more",
        G\Glyph::DISCLOSURE => "disclose",
        G\Glyph::LANGUAGE => "switch_language",
        G\Glyph::LOGIN => "log_in",
        G\Glyph::LOGOUT => "log_out",
        G\Glyph::BULLETLIST => "bulletlist",
        G\Glyph::NUMBEREDLIST => "numberedlist",
        G\Glyph::LISTINDENT => "listindent",
        G\Glyph::LISTOUTDENT => "listoutdent",
        G\Glyph::FILTER => "filter",
        G\Glyph::COLLAPSE_HORIZONTAL => "collapse/back"
    );

    /**
     * @dataProvider glyph_type_provider
     */
    public function test_implements_factory_interface(string $factory_method) : void
    {
        $f = $this->getGlyphFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Symbol\\Glyph\\Factory", $f);
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Symbol\\Glyph\\Glyph", $f->$factory_method("http://www.ilias.de"));
    }

    /**
     * @dataProvider glyph_type_provider
     */
    public function test_glyph_types(string $factory_method) : void
    {
        $f = $this->getGlyphFactory();
        $g = $f->$factory_method();

        $this->assertNotNull($g);
        $this->assertEquals($factory_method, $g->getType());
    }

    /**
     * @dataProvider glyph_type_provider
     */
    public function test_glyph_action(string $factory_method) : void
    {
        $f = $this->getGlyphFactory();
        $g = $f->$factory_method("http://www.ilias.de");

        $this->assertNotNull($g);
        $this->assertEquals("http://www.ilias.de", $g->getAction());
    }

    /**
     * @dataProvider glyph_type_provider
     */
    public function test_glyph_no_action(string $factory_method) : void
    {
        $f = $this->getGlyphFactory();
        $g = $f->$factory_method();

        $this->assertNotNull($g);
        $this->assertEquals(null, $g->getAction());
    }

    /**
     * @dataProvider glyph_type_provider
     */
    public function test_with_unavailable_action(string $factory_method) : void
    {
        $f = $this->getGlyphFactory();
        $g = $f->$factory_method();
        $g2 = $f->$factory_method()->withUnavailableAction();

        $this->assertTrue($g->isActive());
        $this->assertFalse($g2->isActive());
    }

    public function test_with_highlight() : void
    {
        $gf = $this->getGlyphFactory();

        $g = $gf->mail();
        $g2 = $g->withHighlight();

        $this->assertFalse($g->isHighlighted());
        $this->assertTrue($g2->isHighlighted());
    }

    /**
     * @dataProvider glyph_type_provider
     */
    public function test_no_counter(string $factory_method) : void
    {
        $f = $this->getGlyphFactory();
        $g = $f->$factory_method();

        $this->assertCount(0, $g->getCounters());
    }

    /**
     * @dataProvider counter_type_provider
     */
    public function test_one_counter(string $counter_type) : void
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

    public function test_two_counters() : void
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

    public function test_only_two_counters() : void
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

    public function test_immutability_withCounter() : void
    {
        $gf = $this->getGlyphFactory();
        $cf = $this->getCounterFactory();

        $g = $gf->mail();
        $g
            ->withCounter(
                $cf->novelty(0)
            );

        $counters = $g->getCounters();
        $this->assertCount(0, $counters);
    }

    public function test_known_glyphs_only() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new Glyph("FOO", "http://www.ilias.de");
    }

    public function glyph_type_provider() : array
    {
        $glyph_reflection = new ReflectionClass(G\Glyph::class);
        $constant_values = array_values($glyph_reflection->getConstants());
        return array_map(function ($val) {
            return [$val];
        }, $constant_values);
    }

    public function counter_type_provider() : array
    {
        return [
            ["status"],
            ["novelty"]
        ];
    }

    /**
     * @dataProvider glyph_type_provider
     */
    public function test_render_simple(string $type) : void
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
    public function test_render_with_unavailable_action(string $type) : void
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
    public function test_render_withCounter(string $type) : void
    {
        $fg = $this->getGlyphFactory();
        $fc = $this->getCounterFactory();
        $r = $this->getDefaultRenderer();
        $c = $fg->mail("http://www.ilias.de")->withCounter($fc->$type(42));

        $html = $this->normalizeHTML($r->render($c));

        $css_classes = self::$canonical_css_classes[G\Glyph::MAIL];
        $aria_label = self::$aria_labels[G\Glyph::MAIL];

        $expected = "<a class=\"glyph\" href=\"http://www.ilias.de\" aria-label=\"$aria_label\">" .
                    "<span class=\"$css_classes\" aria-hidden=\"true\"></span>" .
                    "<span class=\"il-counter\"><span class=\"badge badge-notify il-counter-$type\">42</span></span>" .
                    "<span class=\"il-counter-spacer\">42</span>" .
                    "</a>";
        $this->assertHTMLEquals($expected, $html);
    }

    public function test_render_withTwoCounters() : void
    {
        $fg = $this->getGlyphFactory();
        $fc = $this->getCounterFactory();
        $r = $this->getDefaultRenderer();
        $c = $fg->mail("http://www.ilias.de")
                ->withCounter($fc->novelty(42))
                ->withCounter($fc->status(7));

        $html = $this->normalizeHTML($r->render($c));

        $css_classes = self::$canonical_css_classes[G\Glyph::MAIL];
        $aria_label = self::$aria_labels[G\Glyph::MAIL];
        $expected = "<a class=\"glyph\" href=\"http://www.ilias.de\" aria-label=\"$aria_label\">" .
                    "<span class=\"$css_classes\" aria-hidden=\"true\"></span>" .
                    "<span class=\"il-counter\"><span class=\"badge badge-notify il-counter-status\">7</span></span>" .
                    "<span class=\"il-counter\"><span class=\"badge badge-notify il-counter-novelty\">42</span></span>" .
                    "<span class=\"il-counter-spacer\">42</span>" .
                    "</a>";
        $this->assertHTMLEquals($expected, $html);
    }

    public function test_dont_render_counter() : void
    {
        $this->expectException(LogicException::class);
        $r = new Renderer(
            $this->getUIFactory(),
            $this->getTemplateFactory(),
            $this->getLanguage(),
            $this->getJavaScriptBinding(),
            $this->getRefinery(),
            new ilImagePathResolver()
        );
        $f = $this->getCounterFactory();

        $r->render($f->status(0), $this->getDefaultRenderer());
    }

    /**
     * @dataProvider glyph_type_provider
     */
    public function test_render_with_on_load_code(string $type) : void
    {
        $f = $this->getGlyphFactory();
        $r = $this->getDefaultRenderer();
        $ids = array();
        $c = $f->$type("http://www.ilias.de")
                ->withOnLoadCode(function ($id) use (&$ids) : string {
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

    /**
     * @dataProvider glyph_type_provider
     */
    public function test_render_with_action(string $type) : void
    {
        $f = $this->getGlyphFactory();
        $r = $this->getDefaultRenderer();
        $c = $f->$type("http://www.ilias.de");
        $c = $c->withAction("http://www.ilias.de/open-source-lms-ilias/");

        $html = $this->normalizeHTML($r->render($c));

        $css_classes = self::$canonical_css_classes[$type];
        $aria_label = self::$aria_labels[$type];

        $expected = "<a class=\"glyph\" href=\"http://www.ilias.de/open-source-lms-ilias/\" aria-label=\"$aria_label\"><span class=\"$css_classes\" aria-hidden=\"true\"></span></a>";
        $this->assertEquals($expected, $html);
    }
}
