<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation as I;

/**
 * Test on icon implementation.
 */
class IconTest extends ILIAS_UI_TestBase
{
    private function getIconFactory()
    {
        return new I\Component\Icon\Factory();
    }

    public function testConstruction()
    {
        $f = $this->getIconFactory();
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Icon\\Factory", $f);

        $si = $f->standard('course', 'Kurs');
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Icon\\Standard", $si);

        $ci = $f->custom('course', 'Kurs');
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Icon\\Custom", $ci);
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
}
