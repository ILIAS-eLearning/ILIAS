<?php

declare(strict_types=1);

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
    public const ICON_PATH = __DIR__ . '/../../../../../templates/default/images/';
    public const ICON_PATH_REL = './templates/default/images/';

    private function getIconFactory(): I\Component\Symbol\Icon\Factory
    {
        return new I\Component\Symbol\Icon\Factory();
    }

    public function testConstruction(): void
    {
        $f = $this->getIconFactory();
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Symbol\\Icon\\Factory", $f);

        $si = $f->standard('course', 'Kurs');
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Symbol\\Icon\\Standard", $si);

        $ci = $f->custom('course', 'Kurs');
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Symbol\\Icon\\Custom", $ci);
    }

    public function testAttributes(): void
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

    public function testSizeModification(): void
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

    public function testSizeModificationWrongParam(): void
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

    public function testDisabledModification(): void
    {
        $f = $this->getIconFactory();
        $ico = $f->standard('course', 'Kurs');

        $ico = $ico->withDisabled(false);
        $this->assertEquals(false, $ico->isDisabled());

        $ico = $ico->withDisabled(true);
        $this->assertEquals(true, $ico->isDisabled());
    }

    public function testDisabledModificationWrongParam(): void
    {
        $f = $this->getIconFactory();
        $ico = $f->standard('course', 'Kurs');
        $this->expectException(TypeError::class);
        $ico->withDisabled('true');
    }

    public function testCustomPath(): void
    {
        $f = $this->getIconFactory();

        $ico = $f->custom('/some/path/', 'Custom Icon');
        $this->assertEquals('/some/path/', $ico->getIconPath());
    }

    public function testRenderingStandard(): Standard
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
    public function testRenderingStandardDisabled(Standard $ico): void
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
    public function testRenderingStandardAbbreviation(Standard $ico): void
    {
        $ico = $ico->withAbbreviation('CRS');
        $html = $this->normalizeHTML($this->getDefaultRenderer()->render($ico));
        $expected = <<<imgtag
<img class="icon crs medium" src="data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgdmlld0JveD0iMCAwIDMyMCAzMjAiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDMyMCAzMjA7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtjbGlwLXBhdGg6dXJsKCNTVkdJRF8wMDAwMDA4MDE3OTA3MDMwNjQ3MTMxMjU3MDAwMDAwNzg1OTYxOTkxMTQ1NzE1MjM5MV8pO2ZpbGw6IzRDNjU4Njt9DQoJLnN0MXtmaWxsOm5vbmU7fQ0KPC9zdHlsZT4NCjxnPg0KCTxnPg0KCQk8ZGVmcz4NCgkJCTxyZWN0IGlkPSJTVkdJRF8xXyIgeD0iNTUiIHk9IjQwIiB3aWR0aD0iMjEwIiBoZWlnaHQ9IjIzNy41Ii8+DQoJCTwvZGVmcz4NCgkJPGNsaXBQYXRoIGlkPSJTVkdJRF8wMDAwMDE2NzM2MjYwODc4ODY3MDYzMDMwMDAwMDAwODg4OTk5NTQwMjk4MTcwOTk1M18iPg0KCQkJPHVzZSB4bGluazpocmVmPSIjU1ZHSURfMV8iICBzdHlsZT0ib3ZlcmZsb3c6dmlzaWJsZTsiLz4NCgkJPC9jbGlwUGF0aD4NCgkJPHBhdGggc3R5bGU9ImNsaXAtcGF0aDp1cmwoI1NWR0lEXzAwMDAwMTY3MzYyNjA4Nzg4NjcwNjMwMzAwMDAwMDA4ODg5OTk1NDAyOTgxNzA5OTUzXyk7ZmlsbDojNEM2NTg2OyIgZD0iTTI1MCwxNjVINzBWNTVoMTgwDQoJCQlWMTY1eiBNMTMyLDE4MGg1NmMwLjEsMC4yLDAuMSwwLjQsMC4yLDAuNkwyMDQsMjE1aC04OGwxNS44LTM0LjRDMTMxLjksMTgwLjQsMTMyLDE4MC4yLDEzMiwxODAgTTI2NSwxNzBWNTBjMC01LjUtNC41LTEwLTEwLTEwDQoJCQlINjVjLTUuNSwwLTEwLDQuNS0xMCwxMHYxMjBjMCw1LjUsNC41LDEwLDEwLDEwaDUwLjZsLTM5LjksODYuOWMtMS43LDMuOC0wLjEsOC4yLDMuNyw5LjljMSwwLjUsMi4xLDAuNywzLjEsMC43DQoJCQljMi44LDAsNS42LTEuNiw2LjgtNC40bDE3LjUtMzguMWgxMDYuM2wxNy41LDM4LjFjMS4zLDIuOCw0LDQuNCw2LjgsNC40YzEsMCwyLjEtMC4yLDMuMS0wLjdjMy44LTEuNyw1LjQtNi4yLDMuNy05LjlMMjA0LjQsMTgwDQoJCQlIMjU1QzI2MC41LDE4MCwyNjUsMTc1LjUsMjY1LDE3MCIvPg0KCTwvZz4NCjwvZz4NCjxyZWN0IGNsYXNzPSJzdDEiIHdpZHRoPSIzMjAiIGhlaWdodD0iMzIwIi8+DQo8dGV4dAogICBzdHlsZT0iCiAgICAgIGZvbnQtc3R5bGU6bm9ybWFsOwogICAgICBmb250LXdlaWdodDpub3JtYWw7CiAgICAgIGZvbnQtc2l6ZToxNHB4OwogICAgICBmb250LWZhbWlseTpzYW5zLXNlcmlmOwogICAgICBsZXR0ZXItc3BhY2luZzowcHg7CiAgICAgIGZpbGw6IzAwMDsKICAgICAgZmlsbC1vcGFjaXR5OjE7CiAgICAiCiAgICB4PSI1MCUiCiAgICB5PSI1NSUiCiAgICBkb21pbmFudC1iYXNlbGluZT0ibWlkZGxlIgogICAgdGV4dC1hbmNob3I9Im1pZGRsZSIKICA+Q1JTPC90ZXh0Pjwvc3ZnPg==" alt="Course" data-abbreviation="CRS"/>
imgtag;
        $this->assertEquals(trim($expected), trim($html));
    }

    public function testRenderingCustom(): Custom
    {
        $path = './templates/default/images/icon_fold.svg';
        $ico = $this->getIconFactory()->custom($path, 'Custom', 'medium');
        $html = $this->normalizeHTML($this->getDefaultRenderer()->render($ico));
        $expected = '<img class="icon custom medium" src="./templates/default/images/icon_fold.svg" alt="Custom"/>';
        $this->assertEquals($expected, $html);
        return $ico;
    }

    public function testAllStandardIconsExist(): void
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
    public function testRenderingStandardJSBindable($ico): void
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
