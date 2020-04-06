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
    const ICON_OUTLINED_PATH = self::ICON_PATH . "outlined/";

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
    }

    public function testAttributes()
    {
        $f = $this->getIconFactory();

        $ico = $f->standard('course', 'Kurs');
        $this->assertEquals('Kurs', $ico->getLabel());
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
        $ico = $this->getIconFactory()->standard('crs', 'Course', 'medium');
        $html = $this->normalizeHTML($this->getDefaultRenderer()->render($ico));
        $expected = '<img class="icon crs medium" src="./templates/default/images/icon_crs.svg" alt="Course"/>';
        $this->assertEquals($expected, $html);
        return $ico;
    }

    /**
     * @depends testRenderingStandard
     */
    public function testRenderingStandardDisabled($ico)
    {
        $ico = $ico->withDisabled(true);
        $html = $this->normalizeHTML($this->getDefaultRenderer()->render($ico));
        $expected = '<img class="icon crs medium disabled" src="./templates/default/images/icon_crs.svg" alt="Course" aria-disabled="true"/>';
        $this->assertEquals($expected, $html);
    }

    /**
     * @depends testRenderingStandard
     */
    public function testRenderingStandardAbbreviation($ico)
    {
        $ico = $ico->withAbbreviation('CRS');
        $html = $this->normalizeHTML($this->getDefaultRenderer()->render($ico));
        $expected = '<img class="icon crs medium" src="./templates/default/images/icon_crs.svg" alt="Course" data-abbreviation="CRS"/>';
        $this->assertEquals($expected, $html);
    }

    /**
     * @depends testRenderingStandard
     */
    public function testRenderingStandardOutlined($ico)
    {
        $ico = $ico->withIsOutlined(true);
        $html = $this->normalizeHTML($this->getDefaultRenderer()->render($ico));
        $expected = '<img class="icon crs medium outlined" src="./templates/default/images/outlined/icon_crs.svg" alt="Course"/>';
        $this->assertEquals($expected, $html);
    }

    public function testRenderingCustom()
    {
        $path = './templates/default/images/icon_fold.svg';
        $ico = $this->getIconFactory()->custom($path, 'Custom', 'medium');
        $html = $this->normalizeHTML($this->getDefaultRenderer()->render($ico));
        $expected = '<img class="icon custom medium" src="./templates/default/images/icon_fold.svg" alt="Custom"/>';
        $this->assertEquals($expected, $html);
        return $ico;
    }

    public function testAllStandardIconsExist()
    {
        $f = $this->getIconFactory();
        $default_icons_abr = $f->standard("nothing", "nothing")->getAllStandardHandles();

        foreach ($default_icons_abr as $icon_abr) {
            $path = self::ICON_PATH . "icon_" . $icon_abr . ".svg";
            $this->assertTrue(file_exists($path), "Missing Standard Icon: " . $path);
        }
    }

    public function testAllOutlinedIconsExist()
    {
        $f = $this->getIconFactory();
        $default_icons_abr = $f->standard("nothing", "nothing")->getAllStandardHandles();

        foreach ($default_icons_abr as $icon_abr) {
            $path = self::ICON_OUTLINED_PATH . "icon_" . $icon_abr . ".svg";

            $this->assertTrue(file_exists($path), "Missing Outlined Icon: " . $path);
        }
    }
}
