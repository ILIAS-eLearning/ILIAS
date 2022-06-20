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
<img class="icon crs medium" src="data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgdmlld0JveD0iMCAwIDMyMCAzMjAiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDMyMCAzMjA7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtjbGlwLXBhdGg6dXJsKCNTVkdJRF8wMDAwMDA5NjA1OTgwMDU5MzMxMjc1Mzc1MDAwMDAxMjkxMzk3MDgwMzcyOTI5MzcyM18pO2ZpbGw6IzUyNjY4Qzt9DQoJLnN0MXtmaWxsOm5vbmU7fQ0KPC9zdHlsZT4NCjxnPg0KCTxkZWZzPg0KCQk8cmVjdCBpZD0iU1ZHSURfMV8iIHg9IjU1IiB5PSI0MCIgd2lkdGg9IjIxMCIgaGVpZ2h0PSIyMzcuNSIvPg0KCTwvZGVmcz4NCgk8Y2xpcFBhdGggaWQ9IlNWR0lEXzAwMDAwMTIxMjY3MDI1NDYyNDg4Nzc4MTMwMDAwMDA1MzE3MDExNzgwNTUwODMxNzU4XyI+DQoJCTx1c2UgeGxpbms6aHJlZj0iI1NWR0lEXzFfIiAgc3R5bGU9Im92ZXJmbG93OnZpc2libGU7Ii8+DQoJPC9jbGlwUGF0aD4NCgk8cGF0aCBzdHlsZT0iY2xpcC1wYXRoOnVybCgjU1ZHSURfMDAwMDAxMjEyNjcwMjU0NjI0ODg3NzgxMzAwMDAwMDUzMTcwMTE3ODA1NTA4MzE3NThfKTtmaWxsOiM1MjY2OEM7IiBkPSJNMjUwLDE2NUg3MFY1NWgxODBWMTY1DQoJCXogTTEzMiwxODBIMTg4YzAuMSwwLjIsMC4xLDAuNCwwLjIsMC42TDIwNCwyMTVoLTg4bDE1LjgtMzQuNEMxMzEuOSwxODAuNCwxMzIsMTgwLjIsMTMyLDE4MCBNMjY1LDE3MFY1MGMwLTUuNS00LjUtMTAtMTAtMTBINjUNCgkJYy01LjUsMC0xMCw0LjUtMTAsMTB2MTIwYzAsNS41LDQuNSwxMCwxMCwxMGg1MC42bC0zOS45LDg2LjljLTEuNywzLjgtMC4xLDguMiwzLjcsOS45YzEsMC41LDIuMSwwLjcsMy4xLDAuNw0KCQljMi44LDAsNS42LTEuNiw2LjgtNC40bDE3LjUtMzguMWgxMDYuM2wxNy41LDM4LjFjMS4zLDIuOCw0LDQuNCw2LjgsNC40YzEsMCwyLjEtMC4yLDMuMS0wLjdjMy44LTEuNyw1LjQtNi4yLDMuNy05LjlMMjA0LjQsMTgwDQoJCUgyNTVDMjYwLjUsMTgwLDI2NSwxNzUuNSwyNjUsMTcwIi8+DQo8L2c+DQo8cmVjdCBjbGFzcz0ic3QxIiB3aWR0aD0iMzIwIiBoZWlnaHQ9IjMyMCIvPg0KPHRleHQKICAgc3R5bGU9IgogICAgICBmb250LXN0eWxlOm5vcm1hbDsKICAgICAgZm9udC13ZWlnaHQ6bm9ybWFsOwogICAgICBmb250LXNpemU6MTRweDsKICAgICAgZm9udC1mYW1pbHk6c2Fucy1zZXJpZjsKICAgICAgbGV0dGVyLXNwYWNpbmc6MHB4OwogICAgICBmaWxsOiMwMDA7CiAgICAgIGZpbGwtb3BhY2l0eToxOwogICAgIgogICAgeD0iNTAlIgogICAgeT0iNTUlIgogICAgZG9taW5hbnQtYmFzZWxpbmU9Im1pZGRsZSIKICAgIHRleHQtYW5jaG9yPSJtaWRkbGUiCiAgPkNSUzwvdGV4dD48L3N2Zz4=" alt="Course" data-abbreviation="CRS"/>
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
