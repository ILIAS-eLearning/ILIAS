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
<img class="icon crs medium" src="data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgdmlld0JveD0iMCAwIDMyMCAzMjAiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDMyMCAzMjA7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtjbGlwLXBhdGg6dXJsKCNTVkdJRF8wMDAwMDA4MDE3OTA3MDMwNjQ3MTMxMjU3MDAwMDAwNzg1OTYxOTkxMTQ1NzE1MjM5MV8pO2ZpbGw6IzRDNjU4Njt9DQoJLnN0MXtmaWxsOm5vbmU7fQ0KPC9zdHlsZT4NCjxnPg0KCTxnPg0KCQk8ZGVmcz4NCgkJCTxyZWN0IGlkPSJTVkdJRF8xXyIgeD0iNTUiIHk9IjQwIiB3aWR0aD0iMjEwIiBoZWlnaHQ9IjIzNy41Ii8+DQoJCTwvZGVmcz4NCgkJPGNsaXBQYXRoIGlkPSJTVkdJRF8wMDAwMDE2NzM2MjYwODc4ODY3MDYzMDMwMDAwMDAwODg4OTk5NTQwMjk4MTcwOTk1M18iPg0KCQkJPHVzZSB4bGluazpocmVmPSIjU1ZHSURfMV8iICBzdHlsZT0ib3ZlcmZsb3c6dmlzaWJsZTsiLz4NCgkJPC9jbGlwUGF0aD4NCgkJPHBhdGggc3R5bGU9ImNsaXAtcGF0aDp1cmwoI1NWR0lEXzAwMDAwMTY3MzYyNjA4Nzg4NjcwNjMwMzAwMDAwMDA4ODg5OTk1NDAyOTgxNzA5OTUzXyk7ZmlsbDojNEM2NTg2OyIgZD0iTTI1MCwxNjVINzBWNTVoMTgwDQoJCQlWMTY1eiBNMTMyLDE4MGg1NmMwLjEsMC4yLDAuMSwwLjQsMC4yLDAuNkwyMDQsMjE1aC04OGwxNS44LTM0LjRDMTMxLjksMTgwLjQsMTMyLDE4MC4yLDEzMiwxODAgTTI2NSwxNzBWNTBjMC01LjUtNC41LTEwLTEwLTEwDQoJCQlINjVjLTUuNSwwLTEwLDQuNS0xMCwxMHYxMjBjMCw1LjUsNC41LDEwLDEwLDEwaDUwLjZsLTM5LjksODYuOWMtMS43LDMuOC0wLjEsOC4yLDMuNyw5LjljMSwwLjUsMi4xLDAuNywzLjEsMC43DQoJCQljMi44LDAsNS42LTEuNiw2LjgtNC40bDE3LjUtMzguMWgxMDYuM2wxNy41LDM4LjFjMS4zLDIuOCw0LDQuNCw2LjgsNC40YzEsMCwyLjEtMC4yLDMuMS0wLjdjMy44LTEuNyw1LjQtNi4yLDMuNy05LjlMMjA0LjQsMTgwDQoJCQlIMjU1QzI2MC41LDE4MCwyNjUsMTc1LjUsMjY1LDE3MCIvPg0KCTwvZz4NCjwvZz4NCjxyZWN0IGNsYXNzPSJzdDEiIHdpZHRoPSIzMjAiIGhlaWdodD0iMzIwIi8+DQo8dGV4dAogICBzdHlsZT0iCiAgICAgIGZvbnQtc3R5bGU6bm9ybWFsOwogICAgICBmb250LXdlaWdodDpub3JtYWw7CiAgICAgIGZvbnQtc2l6ZTo4cmVtOwogICAgICBmb250LWZhbWlseTpzYW5zLXNlcmlmOwogICAgICBsZXR0ZXItc3BhY2luZzowcHg7CiAgICAgIGZpbGw6IzAwMDsKICAgICAgZmlsbC1vcGFjaXR5OjE7CiAgICAiCiAgICB4PSI1MCUiCiAgICB5PSI1NSUiCiAgICBkb21pbmFudC1iYXNlbGluZT0ibWlkZGxlIgogICAgdGV4dC1hbmNob3I9Im1pZGRsZSIKICA+Q1JTPC90ZXh0Pjwvc3ZnPg==" alt="Course" data-abbreviation="CRS"/>
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

    public function testSetCustomLabel(): Custom
    {
        $path = './templates/default/images/icon_fold.svg';
        $ico = $this->getIconFactory()->custom($path, 'Custom', 'medium');
        $ico->setLabel("New Custom Icon Label");
        $html = $this->normalizeHTML($this->getDefaultRenderer()->render($ico));
        $expected = '<img class="icon custom medium" src="./templates/default/images/icon_fold.svg" alt="New Custom Icon Label"/>';
        $this->assertEquals($expected, $html);

        return $ico;
    }

    public function testHTMLInName(): void
    {
        $ico = $this->getIconFactory()->standard('<h1>name</h1>', 'label');
        $html = $this->brutallyTrimHTML($this->getDefaultRenderer()->render($ico));
        $expected = '<img class="icon &lt;h1&gt;name&lt;/h1&gt; small" src="./templates/default/images/icon_default.svg" alt="label"/>';
        $this->assertEquals($expected, $html);
    }

    public function testHTMLInLabel(): void
    {
        $ico = $this->getIconFactory()->standard('name', '<h1>label</h1>');
        $html = $this->brutallyTrimHTML($this->getDefaultRenderer()->render($ico));
        $expected = '<img class="icon name small" src="./templates/default/images/icon_default.svg" alt="&lt;h1&gt;label&lt;/h1&gt;"/>';
        $this->assertEquals($expected, $html);
    }

    /**
     * @depends testRenderingStandard
     */
    public function testHTMLInAbbreviation(): void
    {
        $ico = $this->getIconFactory()->standard('name', 'label')->withAbbreviation('<h1>abbreviation</h1>');
        $html = $this->brutallyTrimHTML($this->getDefaultRenderer()->render($ico));
        $expected = '<img class="icon name small" src="data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgdmlld0JveD0iMCAwIDMyMCAzMjAiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDMyMCAzMjA7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtjbGlwLXBhdGg6dXJsKCNTVkdJRF8wMDAwMDA0Nzc1MTgzNzAxNzcyMjkwMzQ5MDAwMDAwOTM5MjQyNjM5Mzc4Mzc4Mzg1N18pO2ZpbGw6IzRDNjU4Njt9DQo8L3N0eWxlPg0KPGc+DQoJPGc+DQoJCTxkZWZzPg0KCQkJPHJlY3QgaWQ9IlNWR0lEXzFfIiB3aWR0aD0iMzIwIiBoZWlnaHQ9IjMyMCIvPg0KCQk8L2RlZnM+DQoJCTxjbGlwUGF0aCBpZD0iU1ZHSURfMDAwMDAxNjU5Mjc3Njk0MzI5ODM0MDA2OTAwMDAwMTQyOTU5NjQxMjMxNjc4OTQ5MjBfIj4NCgkJCTx1c2UgeGxpbms6aHJlZj0iI1NWR0lEXzFfIiAgc3R5bGU9Im92ZXJmbG93OnZpc2libGU7Ii8+DQoJCTwvY2xpcFBhdGg+DQoJCTxwYXRoIHN0eWxlPSJjbGlwLXBhdGg6dXJsKCNTVkdJRF8wMDAwMDE2NTkyNzc2OTQzMjk4MzQwMDY5MDAwMDAxNDI5NTk2NDEyMzE2Nzg5NDkyMF8pO2ZpbGw6IzRDNjU4NjsiIGQ9Ik05MCw1MEg2MA0KCQkJYy01LjUsMC0xMCw0LjUtMTAsMTB2MjAwYzAsNS41LDQuNSwxMCwxMCwxMGgzMHYtMTVINjVWNjVoMjVWNTB6IE0yNzAsMjYwVjYwYzAtNS41LTQuNS0xMC0xMC0xMGgtMzB2MTVoMjV2MTkwaC0yNXYxNWgzMA0KCQkJQzI2NS41LDI3MCwyNzAsMjY1LjUsMjcwLDI2MCIvPg0KCTwvZz4NCjwvZz4NCjx0ZXh0CiAgIHN0eWxlPSIKICAgICAgZm9udC1zdHlsZTpub3JtYWw7CiAgICAgIGZvbnQtd2VpZ2h0Om5vcm1hbDsKICAgICAgZm9udC1zaXplOjhyZW07CiAgICAgIGZvbnQtZmFtaWx5OnNhbnMtc2VyaWY7CiAgICAgIGxldHRlci1zcGFjaW5nOjBweDsKICAgICAgZmlsbDojMDAwOwogICAgICBmaWxsLW9wYWNpdHk6MTsKICAgICIKICAgIHg9IjUwJSIKICAgIHk9IjU1JSIKICAgIGRvbWluYW50LWJhc2VsaW5lPSJtaWRkbGUiCiAgICB0ZXh0LWFuY2hvcj0ibWlkZGxlIgogID4mbHQ7aDEmZ3Q7YWJicmV2aWF0aW9uJmx0Oy9oMSZndDs8L3RleHQ+PC9zdmc+" alt="label" data-abbreviation="&lt;h1&gt;abbreviation&lt;/h1&gt;"/>';
        $this->assertEquals($expected, $html);
    }

    public function testHTMLInCustomImage(): void
    {
        $ico = $this->getIconFactory()->custom('<h1>path</h1>', 'label');
        $html = $this->brutallyTrimHTML($this->getDefaultRenderer()->render($ico));
        $expected = '<img class="icon custom small" src="&lt;h1&gt;path&lt;/h1&gt;" alt="label"/>';
        $this->assertEquals($expected, $html);
    }
}
