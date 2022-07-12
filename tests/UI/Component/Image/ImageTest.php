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
 
require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\Signal;
use ILIAS\UI\Implementation\Component\Image\Factory;

/**
 * Test on button implementation.
 */
class ImageTest extends ILIAS_UI_TestBase
{
    /**
     * @return Factory
     */
    public function getImageFactory() : Factory
    {
        return new Factory();
    }


    public function test_implements_factory_interface() : void
    {
        $f = $this->getImageFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Image\\Factory", $f);
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Image\\Image", $f->standard("source", "alt"));
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Image\\Image", $f->responsive("source", "alt"));
    }

    public function test_get_type() : void
    {
        $f = $this->getImageFactory();
        $i = $f->standard("source", "alt");

        $this->assertEquals($i::STANDARD, $i->getType());
    }

    public function test_get_source() : void
    {
        $f = $this->getImageFactory();
        $i = $f->standard("source", "alt");

        $this->assertEquals("source", $i->getSource());
    }

    public function test_get_alt() : void
    {
        $f = $this->getImageFactory();
        $i = $f->standard("source", "alt");

        $this->assertEquals("alt", $i->getAlt());
    }

    public function test_set_source() : void
    {
        $f = $this->getImageFactory();
        $i = $f->standard("source", "alt");
        $i = $i->withSource("newSource");
        $this->assertEquals("newSource", $i->getSource());
    }

    public function test_set_alt() : void
    {
        $f = $this->getImageFactory();
        $i = $f->standard("source", "alt");
        $i = $i->withAlt("newAlt");
        $this->assertEquals("newAlt", $i->getAlt());
    }

    public function test_set_string_action() : void
    {
        $f = $this->getImageFactory();
        $i = $f->standard("source", "alt");
        $i = $i->withAction("newAction");
        $this->assertEquals("newAction", $i->getAction());
    }

    public function test_set_signal_action() : void
    {
        $f = $this->getImageFactory();
        $signal = $this->createMock(C\Signal::class);
        $i = $f->standard("source", "alt");
        $i = $i->withAction($signal);
        $this->assertEquals([$signal], $i->getAction());
    }

    public function test_invalid_source() : void
    {
        $this->expectException(TypeError::class);
        $f = $this->getImageFactory();
        $f->standard(1, "alt");
    }

    public function test_invalid_alt() : void
    {
        $this->expectException(TypeError::class);
        $f = $this->getImageFactory();
        $f->standard("source", 1);
    }

    public function test_render_standard() : void
    {
        $f = $this->getImageFactory();
        $r = $this->getDefaultRenderer();
        $i = $f->standard("source", "alt");

        $html = $this->normalizeHTML($r->render($i));

        $expected = "<img src=\"source\" class=\"img-standard\" alt=\"alt\" />";

        $this->assertEquals($expected, $html);
    }

    public function test_render_responsive() : void
    {
        $f = $this->getImageFactory();
        $r = $this->getDefaultRenderer();
        $i = $f->responsive("source", "alt");

        $html = $this->normalizeHTML($r->render($i));

        $expected = "<img src=\"source\" class=\"img-responsive\" alt=\"alt\" />";

        $this->assertEquals($expected, $html);
    }

    public function test_render_alt_escaping() : void
    {
        $f = $this->getImageFactory();
        $r = $this->getDefaultRenderer();
        $i = $f->responsive("source", "\"=test;\")(blah\"");

        $html = $this->normalizeHTML($r->render($i));

        $expected = "<img src=\"source\" class=\"img-responsive\" alt=\"&quot;=test;&quot;)(blah&quot;\" />";

        $this->assertEquals($expected, $html);
    }

    public function test_render_with_string_action() : void
    {
        $f = $this->getImageFactory();
        $r = $this->getDefaultRenderer();
        $i = $f->standard("source", "alt")->withAction("action");

        $html = $this->normalizeHTML($r->render($i));

        $expected = "<a href=\"action\"><img src=\"source\" class=\"img-standard\" alt=\"alt\" /></a>";

        $this->assertEquals($expected, $html);
    }

    public function test_render_with_signal_action() : void
    {
        $f = $this->getImageFactory();
        $r = $this->getDefaultRenderer();
        $signal = $this->createMock(Signal::class);

        $i = $f->standard("source", "alt")->withAction($signal);

        $html = $this->normalizeHTML($r->render($i));

        $expected = "<a href=\"#\" id=\"id_1\"><img src=\"source\" class=\"img-standard\" alt=\"alt\" /></a>";

        $this->assertEquals($expected, $html);
    }

    public function test_with_empty_action_and_no_additional_on_load_code() : void
    {
        $f = $this->getImageFactory();
        $r = $this->getDefaultRenderer();

        $i = $f->standard("source", "alt")->withAction("#");

        $html = $this->normalizeHTML($r->render($i));

        $expected = "<a href=\"#\"><img src=\"source\" class=\"img-standard\" alt=\"alt\" /></a>";

        $this->assertEquals($expected, $html);
    }

    public function test_with_additional_on_load_code() : void
    {
        $f = $this->getImageFactory();
        $r = $this->getDefaultRenderer();

        $i = $f->standard("source", "alt")->withAction("#")->withOnLoadCode(function ($id) {
            return "Something";
        });

        $html = $this->normalizeHTML($r->render($i));

        $expected = "<a href=\"#\"><img src=\"source\" class=\"img-standard\" id='id_1'  alt=\"alt\" /></a>";

        $this->assertEquals($expected, $html);
    }
}
