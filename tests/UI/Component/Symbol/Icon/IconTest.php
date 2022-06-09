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

use ILIAS\UI\Implementation as I;
use ILIAS\UI\Component\Symbol\Icon\Standard;
use ILIAS\UI\Component\Symbol\Icon\Custom;

/**
 * Test on icon implementation.
 */
class IconTest extends ILIAS_UI_TestBase
{
    const ICON_PATH = __DIR__ . '/../../../../../templates/default/images/';
    const ICON_PATH_REL = './templates/default/images/';

    private function getIconFactory() : I\Component\Symbol\Icon\Factory
    {
        return new I\Component\Symbol\Icon\Factory();
    }

    public function testConstruction() : void
    {
        $f = $this->getIconFactory();
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Symbol\\Icon\\Factory", $f);

        $si = $f->standard('course', 'Kurs');
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Symbol\\Icon\\Standard", $si);

        $ci = $f->custom('course', 'Kurs');
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Symbol\\Icon\\Custom", $ci);
    }

    public function testAttributes() : void
    {
        $f = $this->getIconFactory();

        $ico = $f->standard('course', 'Kurs');
        $this->assertEquals('Kurs', $ico->getLabel());
        $this->assertEquals('course', $ico->getName());
        $this->assertEquals('small', $ico->getSize());
        $this->assertEquals(false, $ico->isDisabled());

        $this->assertNull($ico->getAbbreviation());

        $ico = $ico->withAbbreviation('K');
        $this->assertEquals('K', $ico->getAbbreviation());
    }

    public function testSizeModification() : void
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

    public function testSizeModificationWrongParam() : void
    {
        try {
            $f = $this->getIconFactory();
            $ico = $f->standard('course', 'Kurs');
            $ico->withSize('tiny');
            $this->assertFalse("This should not happen");
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testDisabledModification() : void
    {
        $f = $this->getIconFactory();
        $ico = $f->standard('course', 'Kurs');

        $ico = $ico->withDisabled(false);
        $this->assertEquals(false, $ico->isDisabled());

        $ico = $ico->withDisabled(true);
        $this->assertEquals(true, $ico->isDisabled());
    }

    public function testDisabledModificationWrongParam() : void
    {
        $f = $this->getIconFactory();
        $ico = $f->standard('course', 'Kurs');
        $this->expectException(TypeError::class);
        $ico->withDisabled('true');
    }

    public function testCustomPath() : void
    {
        $f = $this->getIconFactory();

        $ico = $f->custom('/some/path/', 'Custom Icon');
        $this->assertEquals('/some/path/', $ico->getIconPath());
    }

    public function testRenderingStandard() : Standard
    {
        $ico = $this->getIconFactory()->standard('crs', 'Course', 'medium');
        $html = $this->normalizeHTML($this->getDefaultRenderer()->render($ico));
        $path = self::ICON_PATH_REL . 'icon_crs.svg';
        $expected = "<img class=\"icon crs medium\" src=\"$path\" alt=\"Course\"/>";
        $this->assertEquals($expected, $html);
        return $ico;
    }

    /**
     * @depends testRenderingStandard
     */
    public function testRenderingStandardDisabled(Standard $ico) : void
    {
        $ico = $ico->withDisabled(true);
        $html = $this->normalizeHTML($this->getDefaultRenderer()->render($ico));
        $path = self::ICON_PATH_REL . 'icon_crs.svg';
        $expected = "<img class=\"icon crs medium disabled\" src=\"$path\" alt=\"Course\" aria-disabled=\"true\"/>";
        $this->assertEquals($expected, $html);
    }

    /**
     * @depends testRenderingStandard
     */
    public function testRenderingStandardAbbreviation(Standard $ico) : void
    {
        $ico = $ico->withAbbreviation('CRS');
        $html = $this->normalizeHTML($this->getDefaultRenderer()->render($ico));
        $expected = <<<imgtag
<img class="icon crs medium" src="data:image/svg+xml;base64,PHN2ZyBpZD0iTGF5ZXJfMSIgZGF0YS1uYW1lPSJMYXllciAxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2aWV3Qm94PSIwIDAgMjEwIDIzNy41Ij48ZGVmcz48c3R5bGU+LmNscy0xe2ZpbGw6bm9uZTt9LmNscy0ye2NsaXAtcGF0aDp1cmwoI2NsaXAtcGF0aCk7fS5jbHMtM3tmaWxsOiM1MjY2OGM7fTwvc3R5bGU+PGNsaXBQYXRoIGlkPSJjbGlwLXBhdGgiIHRyYW5zZm9ybT0idHJhbnNsYXRlKC01NSAtNDApIj48cmVjdCBjbGFzcz0iY2xzLTEiIHg9IjU1IiB5PSI0MCIgd2lkdGg9IjIxMCIgaGVpZ2h0PSIyMzcuNSIvPjwvY2xpcFBhdGg+PC9kZWZzPjxnIGNsYXNzPSJjbHMtMiI+PHBhdGggY2xhc3M9ImNscy0zIiBkPSJNMjUwLDE2NUg3MFY1NUgyNTBaTTEzMiwxODBIMTg4Yy4wNy4yMS4xMi40Mi4yMS42M0wyMDQsMjE1SDExNmwxNS43OS0zNC4zN0E1LjI1LDUuMjUsMCwwLDAsMTMyLDE4MG0xMzMtMTBWNTBhMTAsMTAsMCwwLDAtMTAtMTBINjVBMTAsMTAsMCwwLDAsNTUsNTBWMTcwYTEwLDEwLDAsMCwwLDEwLDEwaDUwLjZMNzUuNjgsMjY2Ljg3YTcuNSw3LjUsMCwwLDAsMy42OSw5Ljk0LDcuNCw3LjQsMCwwLDAsMy4xMi42OSw3LjUsNy41LDAsMCwwLDYuODItNC4zN0wxMDYuODMsMjM1SDIxMy4xN2wxNy41MSwzOC4xM2E3LjUxLDcuNTEsMCwwLDAsNi44Miw0LjM3LDcuNDEsNy40MSwwLDAsMCwzLjEzLS42OSw3LjUsNy41LDAsMCwwLDMuNjktOS45NEwyMDQuNCwxODBIMjU1YTEwLDEwLDAsMCwwLDEwLTEwIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgtNTUgLTQwKSIvPjwvZz48dGV4dAogICBzdHlsZT0iCiAgICAgIGZvbnQtc3R5bGU6bm9ybWFsOwogICAgICBmb250LXdlaWdodDpub3JtYWw7CiAgICAgIGZvbnQtc2l6ZToxNHB4OwogICAgICBmb250LWZhbWlseTpzYW5zLXNlcmlmOwogICAgICBsZXR0ZXItc3BhY2luZzowcHg7CiAgICAgIGZpbGw6IzAwMDsKICAgICAgZmlsbC1vcGFjaXR5OjE7CiAgICAiCiAgICB4PSI1MCUiCiAgICB5PSI1NSUiCiAgICBkb21pbmFudC1iYXNlbGluZT0ibWlkZGxlIgogICAgdGV4dC1hbmNob3I9Im1pZGRsZSIKICA+Q1JTPC90ZXh0Pjwvc3ZnPg==" alt="Course" data-abbreviation="CRS"/>
imgtag;
        $this->assertEquals(trim($expected), trim($html));
    }

    public function testRenderingCustom() : Custom
    {
        $path = './templates/default/images/icon_fold.svg';
        $ico = $this->getIconFactory()->custom($path, 'Custom', 'medium');
        $html = $this->normalizeHTML($this->getDefaultRenderer()->render($ico));
        $expected = '<img class="icon custom medium" src="./templates/default/images/icon_fold.svg" alt="Custom"/>';
        $this->assertEquals($expected, $html);
        return $ico;
    }

    public function testAllStandardIconsExist() : void
    {
        $f = $this->getIconFactory();
        $default_icons_abr = $f->standard("nothing", "nothing")->getAllStandardHandles();

        foreach ($default_icons_abr as $icon_abr) {
            $path = self::ICON_PATH . "icon_" . $icon_abr . ".svg";
            $this->assertTrue(file_exists($path), "Missing Standard Icon: " . $path);
        }
    }
    
    /**
     * @depends testRenderingStandard
     */
    public function testRenderingStandardJSBindable($ico) : void
    {
        $ico = $ico->withAdditionalOnLoadCode(function ($id) {
            return 'alert();';
        });
        $html = $this->normalizeHTML($this->getDefaultRenderer()->render($ico));
        $path = self::ICON_PATH_REL . 'icon_crs.svg';
        $expected = "<img  aria-disabled=\"true\"/>";
        $expected = $this->normalizeHTML("<img id=\"id_1\" class=\"icon crs medium\" src=\"$path\" alt=\"Course\"/>");
        $this->assertEquals($expected, $html);
    }
}
