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

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/InputTest.php");

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Component\Input\Field;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;

class UrlInputTest extends ILIAS_UI_TestBase
{
    private DefNamesource $name_source;

    public function setUp(): void
    {
        $this->name_source = new DefNamesource();
    }

    protected function buildFactory(): I\Input\Field\Factory
    {
        $data_factory = new Data\Factory();
        $language = $this->createMock(ilLanguage::class);
        return new I\Input\Field\Factory(
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class),
            new SignalGenerator(),
            $data_factory,
            new Refinery($data_factory, $language),
            $language
        );
    }

    public function testImplementsFactoryInterface(): void
    {
        $factory = $this->buildFactory();
        $url = $factory->url("Test Label", "Test Byline");

        $this->assertInstanceOf(\ILIAS\UI\Component\Input\Container\Form\FormInput::class, $url);
        $this->assertInstanceOf(Field\Url::class, $url);
    }

    public function testRendering(): void
    {
        $factory = $this->buildFactory();
        $renderer = $this->getDefaultRenderer();
        $label = "Test Label";
        $byline = "Test Byline";
        $id = "id_1";
        $name = "name_0";
        $url = $factory->url($label, $byline)->withNameFrom($this->name_source);
        $html = $this->normalizeHTML($renderer->render($url));

        $expected = "<div class=\"form-group row\">
                        <label for=\"$id\" class=\"control-label col-sm-4 col-md-3 col-lg-2\">$label</label>
                        <div class=\"col-sm-8 col-md-9 col-lg-10\">
                            <input id=\"$id\" type=\"url\" name=\"$name\" class=\"form-control form-control-sm\" />
                            <div class=\"help-block\">$byline</div>
                        </div>
                    </div>";
        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testRenderError(): void
    {
        $factory = $this->buildFactory();
        $renderer = $this->getDefaultRenderer();
        $label = "Test Label";
        $byline = "Test Byline";
        $id = "id_1";
        $name = "name_0";
        $error = "test_error";
        $url = $factory->url($label, $byline)->withNameFrom($this->name_source)
            ->withError($error);
        $html = $this->normalizeHTML($renderer->render($url));

        $expected = "<div class=\"form-group row\">
                        <label for=\"$id\" class=\"control-label col-sm-4 col-md-3 col-lg-2\">$label</label>
                        <div class=\"col-sm-8 col-md-9 col-lg-10\">
                            <div class=\"help-block alert alert-danger\" aria-describedby=\"id_1\" role=\"alert\">$error</div>
                            <input id=\"$id\" type=\"url\" name=\"$name\" class=\"form-control form-control-sm\" />
                            <div class=\"help-block\">$byline</div>
                        </div>
                    </div>";

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testRenderNoByline(): void
    {
        $factory = $this->buildFactory();
        $renderer = $this->getDefaultRenderer();
        $label = "Test Label";
        $id = "id_1";
        $name = "name_0";
        $url = $factory->url($label)->withNameFrom($this->name_source);
        $html = $this->normalizeHTML($renderer->render($url));

        $expected = "<div class=\"form-group row\">
                        <label for=\"$id\" class=\"control-label col-sm-4 col-md-3 col-lg-2\">$label</label>
                        <div class=\"col-sm-8 col-md-9 col-lg-10\">
                            <input id=\"$id\" type=\"url\" name=\"$name\" class=\"form-control form-control-sm\" />
                        </div>
                    </div>";
        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testRenderValue(): void
    {
        $factory = $this->buildFactory();
        $renderer = $this->getDefaultRenderer();
        $label = "Test Label";
        $value = "https://www.ilias.de/";
        $id = "id_1";
        $name = "name_0";
        $url = $factory->url($label)->withValue($value)
            ->withNameFrom($this->name_source);
        $html = $this->normalizeHTML($renderer->render($url));

        $expected = "<div class=\"form-group row\">
                        <label for=\"$id\" class=\"control-label col-sm-4 col-md-3 col-lg-2\">$label</label>
                        <div class=\"col-sm-8 col-md-9 col-lg-10\">
                           <input id=\"$id\" type=\"url\" value=\"$value\" name=\"$name\" class=\"form-control form-control-sm\" />
                        </div>
                     </div>";
        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testRenderRequired(): void
    {
        $factory = $this->buildFactory();
        $renderer = $this->getDefaultRenderer();
        $label = "Test Label";
        $id = "id_1";
        $name = "name_0";
        $url = $factory->url($label)->withNameFrom($this->name_source)
            ->withRequired(true);
        $html = $this->normalizeHTML($renderer->render($url));

        $expected = "<div class=\"form-group row\">
                        <label for=\"$id\" class=\"control-label col-sm-4 col-md-3 col-lg-2\">$label<span class=\"asterisk\">*</span></label>
                        <div class=\"col-sm-8 col-md-9 col-lg-10\">
                            <input id=\"$id\" type=\"url\" name=\"$name\" class=\"form-control form-control-sm\" />
                        </div>
                    </div>";
        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testRenderDisabled(): void
    {
        $factory = $this->buildFactory();
        $renderer = $this->getDefaultRenderer();
        $label = "Test Label";
        $id = "id_1";
        $name = "name_0";
        $url = $factory->url($label)->withNameFrom($this->name_source)
            ->withDisabled(true);
        $html = $this->normalizeHTML($renderer->render($url));

        $expected = "<div class=\"form-group row\">
                        <label for=\"$id\" class=\"control-label col-sm-4 col-md-3 col-lg-2\">$label</label>
                        <div class=\"col-sm-8 col-md-9 col-lg-10\">
                            <input id=\"$id\" type=\"url\" name=\"$name\" disabled=\"disabled\" class=\"form-control form-control-sm\" />
                        </div>
                    </div>";

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }
}
