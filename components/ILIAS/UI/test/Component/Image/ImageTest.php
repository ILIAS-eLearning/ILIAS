<?php

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

declare(strict_types=1);

require_once(__DIR__ . "/../../../../../../vendor/composer/vendor/autoload.php");
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
    public function getImageFactory(): Factory
    {
        return new Factory();
    }


    public function testImplementsFactoryInterface(): void
    {
        $f = $this->getImageFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Image\\Factory", $f);
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Image\\Image", $f->standard("source", "alt"));
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Image\\Image", $f->responsive("source", "alt"));
    }

    public function testGetType(): void
    {
        $f = $this->getImageFactory();
        $i = $f->standard("source", "alt");

        $this->assertEquals($i::STANDARD, $i->getType());
    }

    public function testGetSource(): void
    {
        $f = $this->getImageFactory();
        $i = $f->standard("source", "alt");

        $this->assertEquals("source", $i->getSource());
    }

    public function testGetAlt(): void
    {
        $f = $this->getImageFactory();
        $i = $f->standard("source", "alt");

        $this->assertEquals("alt", $i->getAlt());
    }

    public function testSetSource(): void
    {
        $f = $this->getImageFactory();
        $i = $f->standard("source", "alt");
        $i = $i->withSource("newSource");
        $this->assertEquals("newSource", $i->getSource());
    }

    public function testSetAlt(): void
    {
        $f = $this->getImageFactory();
        $i = $f->standard("source", "alt");
        $i = $i->withAlt("newAlt");
        $this->assertEquals("newAlt", $i->getAlt());
    }

    public function testSetStringAction(): void
    {
        $f = $this->getImageFactory();
        $i = $f->standard("source", "alt");
        $i = $i->withAction("newAction");
        $this->assertEquals("newAction", $i->getAction());
    }

    public function testSetSignalAction(): void
    {
        $f = $this->getImageFactory();
        $signal = $this->createMock(C\Signal::class);
        $i = $f->standard("source", "alt");
        $i = $i->withAction($signal);
        $this->assertEquals([$signal], $i->getAction());
    }

    public function testSetAdditionalHighResSources(): void
    {
        $additional_sources = [
            600 => 'image1',
            300 => 'image2'
        ];
        $f = $this->getImageFactory();
        $i = $f->standard("source", "alt");
        foreach($additional_sources as $min_width_in_pixels => $source) {
            $i = $i->withAdditionalHighResSource($source, $min_width_in_pixels);
        }
        $this->assertEquals($additional_sources, $i->getAdditionalHighResSources());
    }

    public function testInvalidSource(): void
    {
        $this->expectException(TypeError::class);
        $f = $this->getImageFactory();
        $f->standard(1, "alt");
    }

    public function testInvalidAlt(): void
    {
        $this->expectException(TypeError::class);
        $f = $this->getImageFactory();
        $f->standard("source", 1);
    }

    public function testInvalidAdditionalHighResSource(): void
    {
        $this->expectException(TypeError::class);
        $f = $this->getImageFactory();
        $f->standard("source", 1)->withAdditionalHighResSource(
            1,
            1
        );
    }

    public function testInvalidAdditionalHighResSourceSize(): void
    {
        $this->expectException(TypeError::class);
        $f = $this->getImageFactory();
        $f->standard("source", 1)->withAdditionalHighResSource(
            '#',
            '#'
        );
    }

    public function testRenderStandard(): void
    {
        $f = $this->getImageFactory();
        $r = $this->getDefaultRenderer();
        $i = $f->standard("source", "alt");

        $html = $this->normalizeHTML($r->render($i));

        $expected = "<img src=\"source\" class=\"img-standard\" alt=\"alt\" />";

        $this->assertEquals($expected, $html);
    }

    public function testRenderResponsive(): void
    {
        $f = $this->getImageFactory();
        $r = $this->getDefaultRenderer();
        $i = $f->responsive("source", "alt");

        $html = $this->normalizeHTML($r->render($i));

        $expected = "<img src=\"source\" class=\"img-responsive\" alt=\"alt\" />";

        $this->assertEquals($expected, $html);
    }

    public function testRenderAltEscaping(): void
    {
        $f = $this->getImageFactory();
        $r = $this->getDefaultRenderer();
        $i = $f->responsive("source", "\"=test;\")(blah\"");

        $html = $this->normalizeHTML($r->render($i));

        $expected = "<img src=\"source\" class=\"img-responsive\" alt=\"&quot;=test;&quot;)(blah&quot;\" />";

        $this->assertEquals($expected, $html);
    }

    public function testRenderWithStringAction(): void
    {
        $f = $this->getImageFactory();
        $r = $this->getDefaultRenderer();
        $i = $f->standard("source", "alt")->withAction("action");

        $html = $this->normalizeHTML($r->render($i));

        $expected = "<a href=\"action\"><img src=\"source\" class=\"img-standard\" alt=\"alt\" /></a>";

        $this->assertEquals($expected, $html);
    }

    public function testRenderWithSignalAction(): void
    {
        $f = $this->getImageFactory();
        $r = $this->getDefaultRenderer();
        $signal = $this->createMock(Signal::class);

        $i = $f->standard("source", "alt")->withAction($signal);

        $html = $this->normalizeHTML($r->render($i));

        $expected = "<a href=\"#\" id=\"id_1\"><img src=\"source\" class=\"img-standard\" alt=\"alt\" /></a>";

        $this->assertEquals($expected, $html);
    }

    public function testWithEmptyActionAndNoAdditionalOnLoadCode(): void
    {
        $f = $this->getImageFactory();
        $r = $this->getDefaultRenderer();

        $i = $f->standard("source", "alt")->withAction("#");

        $html = $this->normalizeHTML($r->render($i));

        $expected = "<a href=\"#\"><img src=\"source\" class=\"img-standard\" alt=\"alt\" /></a>";

        $this->assertEquals($expected, $html);
    }

    public function testWithAdditionalOnLoadCode(): void
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
