<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use \ILIAS\UI\Implementation as I;

/**
 * Test on icon implementation.
 */
class IconTest extends ILIAS_UI_TestBase
{
    const ICON_PATH = __DIR__ . "/../../../../../templates/default/images/";
    const ICON_OUTLINED_PATH = self::ICON_PATH."outlined/";

    private function getIconFactory()
    {
        return new I\Component\Symbol\Icon\Factory();
    }

    public function testConstruction()
    {
        $f = $this->getIconFactory();
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Symbol\\Icon\\Factory", $f);

        $si = $f->standard('course', 'Kurs');
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Symbol\\Icon\\Standard", $si);

        $ci = $f->custom('course', 'Kurs');
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Symbol\\Icon\\Custom", $ci);

        $ci = $f->inline('course', 'image/svg+xml', 'Kurs');
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Symbol\\Icon\\Inline", $ci);
    }

    public function testAttributes()
    {
        $f = $this->getIconFactory();

        $ico = $f->standard('course', 'Kurs');
        $this->assertEquals('Kurs', $ico->getAriaLabel());
        $this->assertEquals('course', $ico->getName());
        $this->assertEquals('small', $ico->getSize());
        $this->assertEquals(false, $ico->isDisabled());
        $this->assertEquals(false, $ico->isOutlined());

        $this->assertNull($ico->getAbbreviation());

        $ico = $ico->withAbbreviation('K');
        $this->assertEquals('K', $ico->getAbbreviation());
    }

    public function testSizeModification()
    {
        $f = $this->getIconFactory();
        $ico = $f->standard('course', 'Kurs');

        $ico = $ico->withSize('medium');
        $this->assertEquals('medium', $ico->getSize());

        $ico = $ico->withSize('large');
        $this->assertEquals('large', $ico->getSize());

        $ico = $ico->withSize('small');
        $this->assertEquals('small', $ico->getSize());
    }

    public function testSizeModificationWrongParam()
    {
        try {
            $f = $this->getIconFactory();
            $ico = $f->standard('course', 'Kurs');
            $ico = $ico->withSize('tiny');
            $this->assertFalse("This should not happen");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testDisabledModification()
    {
        $f = $this->getIconFactory();
        $ico = $f->standard('course', 'Kurs', 'small');

        $ico = $ico->withDisabled(false);
        $this->assertEquals(false, $ico->isDisabled());

        $ico = $ico->withDisabled(true);
        $this->assertEquals(true, $ico->isDisabled());
    }

    public function testDisabledModificationWrongParam()
    {
        try {
            $f = $this->getIconFactory();
            $ico = $f->standard('course', 'Kurs', 'small');
            $ico = $ico->withDisabled('true');
            $this->assertFalse("This should not happen");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testOutlinedModification()
    {
        $f = $this->getIconFactory();
        $ico = $f->standard('course', 'Kurs', 'small');

        $ico = $ico->withIsOutlined(true);
        $this->assertEquals(true, $ico->isOutlined());

        $ico = $ico->withIsOutlined(false);
        $this->assertEquals(false, $ico->isOutlined());
    }

    public function testCustomPath()
    {
        $f = $this->getIconFactory();

        $ico = $f->custom('/some/path/', 'Custom Icon');
        $this->assertEquals('/some/path/', $ico->getIconPath());
    }


    public function testInlineData()
    {
        $f = $this->getIconFactory();

        $content = 'raw_content';

        $ico = $f->inline(base64_encode($content), 'image/svg+xml', 'Test');
        $this->assertEquals(base64_encode($content), $ico->getBase64Data());
    }


    public function testRenderingInline()
    {
        $f = $this->getIconFactory();
        $r = $this->getDefaultRenderer();
        $content = 'raw_content';
        $mime_type = 'image/svg+xml';
        $aria_label = 'Test';
        $ico = $f->inline(base64_encode($content), $mime_type, $aria_label);
        $html = $this->normalizeHTML($r->render($ico));
        $expected = '<div class="icon inline small" aria-label="' . $aria_label . '">	<img src="data:' . $mime_type . ';base64,' . base64_encode($content) . '" /></div>';

        $this->assertEquals($expected, $html);
    }

    public function testRenderingStandard()
    {
        $f = $this->getIconFactory();
        $r = $this->getDefaultRenderer();

        $ico = $ico = $f->standard('crs', 'Course', 'medium');
        $html = $this->normalizeHTML($r->render($ico));
        $expected = '<div class="icon crs medium" aria-label="Course"></div>';
        $this->assertEquals($expected, $html);

        //with disabled
        $ico = $ico->withDisabled(true);
        $html = $this->normalizeHTML($r->render($ico));
        $expected = '<div class="icon crs medium disabled" aria-label="Course"></div>';
        $this->assertEquals($expected, $html);

        //with abbreviation
        $ico = $ico->withAbbreviation('CRS');
        $html = $this->normalizeHTML($r->render($ico));
        $expected = '<div class="icon crs medium disabled" aria-label="Course">'
                    . '	<div class="abbreviation">CRS</div>'
                    . '</div>';
        $this->assertEquals($expected, $html);
    }

    public function testRenderingStandardOutlined()
    {
        $f = $this->getIconFactory();
        $r = $this->getDefaultRenderer();

        $ico = $ico = $f->standard('crs', 'Course', 'medium')->withIsOutlined(true);
        $html = $this->normalizeHTML($r->render($ico));
        $expected = '<div class="icon crs medium outlined" aria-label="Course"></div>';
        $this->assertEquals($expected, $html);

        //with disabled
        $ico = $ico->withDisabled(true);
        $html = $this->normalizeHTML($r->render($ico));
        $expected = '<div class="icon crs medium disabled outlined" aria-label="Course"></div>';
        $this->assertEquals($expected, $html);

        //with abbreviation
        $ico = $ico->withAbbreviation('CRS');
        $html = $this->normalizeHTML($r->render($ico));
        $expected = '<div class="icon crs medium disabled outlined" aria-label="Course">'
            . '	<div class="abbreviation">CRS</div>'
            . '</div>';
        $this->assertEquals($expected, $html);
    }

    public function testRenderingCustom()
    {
        $f = $this->getIconFactory();
        $r = $this->getDefaultRenderer();
        $path = './templates/default/images/icon_fold.svg';

        $ico = $ico = $f->custom($path, 'Custom', 'medium');
        $html = $this->normalizeHTML($r->render($ico));
        $expected = '<div class="icon custom medium" aria-label="Custom">'
                    . '	<img src="./templates/default/images/icon_fold.svg" />'
                    . '</div>';
        $this->assertEquals($expected, $html);

        //with disabled
        $ico = $ico->withDisabled(true);
        $html = $this->normalizeHTML($r->render($ico));
        $expected = '<div class="icon custom medium disabled" aria-label="Custom">'
            . '	<img src="./templates/default/images/icon_fold.svg" />'
            . '</div>';
        $this->assertEquals($expected, $html);

        //with abbreviation
        $ico = $ico->withAbbreviation('CS');
        $html = $this->normalizeHTML($r->render($ico));
        $expected = '<div class="icon custom medium disabled" aria-label="Custom">'
                    . '	<img src="./templates/default/images/icon_fold.svg" />'
                    . '	<div class="abbreviation">CS</div>'
                    . '</div>';

        $this->assertEquals($expected, $html);
    }

    public function testAllStandardIconsExist(){
        $f = $this->getIconFactory();
        $default_icons_abr = $f->standard("nothing","nothing")->getAllStandardHandles();

        foreach ($default_icons_abr as $icon_abr) {
            $this->assertTrue(file_exists(self::ICON_PATH."icon_".$icon_abr.".svg"));
        }
    }

    public function testAllOutlinedIconsExist(){
        $f = $this->getIconFactory();
        $default_icons_abr = $f->standard("nothing","nothing")->getAllStandardHandles();

        foreach ($default_icons_abr as $icon_abr) {
            $this->assertTrue(file_exists(self::ICON_OUTLINED_PATH."icon_".$icon_abr.".svg"));
        }
    }
}
