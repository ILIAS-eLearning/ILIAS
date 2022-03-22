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
        $expected = <<<imgtag
<img class="icon crs medium" src="data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxuczp4PSImbnNfZXh0ZW5kOyIgeG1sbnM6aT0iJm5zX2FpOyIgeG1sbnM6Z3JhcGg9IiZuc19ncmFwaHM7Ig0KCSB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIgd2lkdGg9IjMycHgiIGhlaWdodD0iMzJweCINCgkgdmlld0JveD0iMCAwIDMyIDMyIiBlbmFibGUtYmFja2dyb3VuZD0ibmV3IDAgMCAzMiAzMiIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+DQo8c3dpdGNoPg0KCTxnIGk6ZXh0cmFuZW91cz0ic2VsZiI+DQoJCTxyZWN0IHg9IjAiIGZpbGw9Im5vbmUiIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIvPg0KCQk8Zz4NCgkJCTxnPg0KCQkJCTxkZWZzPg0KCQkJCQk8cmVjdCBpZD0iU1ZHSURfMV8iIHg9IjYiIHk9IjQiIHdpZHRoPSIyMCIgaGVpZ2h0PSIxNCIvPg0KCQkJCTwvZGVmcz4NCgkJCQk8Y2xpcFBhdGggaWQ9IlNWR0lEXzJfIj4NCgkJCQkJPHVzZSB4bGluazpocmVmPSIjU1ZHSURfMV8iICBvdmVyZmxvdz0idmlzaWJsZSIvPg0KCQkJCTwvY2xpcFBhdGg+DQoJCQkJDQoJCQkJCTxsaW5lYXJHcmFkaWVudCBpZD0iU1ZHSURfM18iIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4MT0iLTM4OC41MTY1IiB5MT0iLTI5My40ODY3IiB4Mj0iLTM4Ny41MTY1IiB5Mj0iLTI5My40ODY3IiBncmFkaWVudFRyYW5zZm9ybT0ibWF0cml4KDIwIDAgMCAyMCA3Nzc2LjMzMDYgNTg4MC43MzM5KSI+DQoJCQkJCTxzdG9wICBvZmZzZXQ9IjAiIHN0eWxlPSJzdG9wLWNvbG9yOiM1MzgxMzIiLz4NCgkJCQkJPHN0b3AgIG9mZnNldD0iMC4wMTk4IiBzdHlsZT0ic3RvcC1jb2xvcjojNTM4MTMyIi8+DQoJCQkJCTxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiM3NEEwMjkiLz4NCgkJCQk8L2xpbmVhckdyYWRpZW50Pg0KCQkJCTxyZWN0IHg9IjYiIHk9IjQiIGNsaXAtcGF0aD0idXJsKCNTVkdJRF8yXykiIGZpbGw9InVybCgjU1ZHSURfM18pIiB3aWR0aD0iMjAiIGhlaWdodD0iMTQiLz4NCgkJCTwvZz4NCgkJPC9nPg0KCQk8cGF0aCBmaWxsPSIjNEMzMzI3IiBkPSJNMjYsMTZINnYyaDUuMjg0bC0zLjk0Myw4LjU4MmMtMC4yMywwLjUwMi0wLjAxMSwxLjA5NiwwLjQ5MSwxLjMyNkM3Ljk2OCwyNy45NzEsOC4xMSwyOCw4LjI1LDI4DQoJCQljMC4zNzgsMCwwLjc0MS0wLjIxNiwwLjkwOS0wLjU4MmwxLjQ5Ni0zLjI1NmgxMC42OTFsMS40OTYsMy4yNTZDMjMuMDEsMjcuNzg0LDIzLjM3MiwyOCwyMy43NTEsMjgNCgkJCWMwLjE0LDAsMC4yODItMC4wMjksMC40MTctMC4wOTJjMC41MDItMC4yMywwLjcyMi0wLjgyNCwwLjQ5MS0xLjMyNkwyMC43MTYsMThIMjZWMTZ6IE0xOC41OTEsMTguMTY4bDEuODM1LDMuOTk0aC04Ljg1Mw0KCQkJbDEuODM1LTMuOTk0YzAuMDI1LTAuMDU0LDAuMDI1LTAuMTEyLDAuMDQtMC4xNjhoNS4xMDRDMTguNTY2LDE4LjA1NiwxOC41NjYsMTguMTEzLDE4LjU5MSwxOC4xNjh6Ii8+DQoJPC9nPg0KPC9zd2l0Y2g+DQo8dGV4dAogICBzdHlsZT0iCiAgICAgIGZvbnQtc3R5bGU6bm9ybWFsOwogICAgICBmb250LXdlaWdodDpub3JtYWw7CiAgICAgIGZvbnQtc2l6ZToxNHB4OwogICAgICBmb250LWZhbWlseTpzYW5zLXNlcmlmOwogICAgICBsZXR0ZXItc3BhY2luZzowcHg7CiAgICAgIGZpbGw6IzAwMDsKICAgICAgZmlsbC1vcGFjaXR5OjE7CiAgICAiCiAgICB4PSI1MCUiCiAgICB5PSI1NSUiCiAgICBkb21pbmFudC1iYXNlbGluZT0ibWlkZGxlIgogICAgdGV4dC1hbmNob3I9Im1pZGRsZSIKICA+Q1JTPC90ZXh0Pjwvc3ZnPg==" alt="Course" data-abbreviation="CRS"/>
imgtag;
        $this->assertEquals(trim($expected), trim($html));
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
    
    /**
     * @depends testRenderingStandard
     */
    public function testRenderingStandardJSBindable($ico)
    {
        $ico = $ico->withAdditionalOnLoadCode(function ($id) {
            return 'alert();';
        });
        $html = $this->normalizeHTML($this->getDefaultRenderer()->render($ico));
        $expected = $this->normalizeHTML('<img id="id_1" class="icon crs medium" src="./templates/default/images/icon_crs.svg" alt="Course"/>');
        $this->assertEquals($expected, $html);
    }
}
