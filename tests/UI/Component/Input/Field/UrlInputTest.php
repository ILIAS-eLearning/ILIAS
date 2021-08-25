<?php

/* Copyright (c) 2021 Luka Stocker <luka.stocker@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/InputTest.php");

use ILIAS\UI\Implementation\Component\SignalGenerator;
use \ILIAS\UI\Component\Input\Field;
use \ILIAS\Data;

class UrlInputTest extends ILIAS_UI_TestBase
{
    /**
     * @var DefNamesource
     */
    private $name_source;

    public function setUp() : void
    {
        $this->name_source = new DefNamesource();
    }

    protected function buildFactory()
    {
        $data_factory = new Data\Factory();
        $language = $this->createMock(\ilLanguage::class);
        return new ILIAS\UI\Implementation\Component\Input\Field\Factory(
            new SignalGenerator(),
            $data_factory,
            new ILIAS\Refinery\Factory($data_factory, $language),
            $language
        );
    }

    public function test_implements_factory_interface()
    {
        $factory = $this->buildFactory();
        $url = $factory->url("Test Label", "Test Byline");

        $this->assertInstanceOf(Field\Input::class, $url);
        $this->assertInstanceOf(Field\Url::class, $url);
    }

    public function test_rendering()
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
                        <label for=\"$id\" class=\"control-label col-sm-3\">$label</label>
                        <div class=\"col-sm-9\">
                            <input id=\"$id\" type=\"url\" name=\"$name\" class=\"form-control form-control-sm\" />
                            <div class=\"help-block\">$byline</div>
                        </div>
                    </div>";
        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function test_render_error()
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
                        <label for=\"$id\" class=\"control-label col-sm-3\">$label</label>
                        <div class=\"col-sm-9\">
                            <div class=\"help-block alert alert-danger\" role=\"alert\">$error</div>
                            <input id=\"$id\" type=\"url\" name=\"$name\" class=\"form-control form-control-sm\" />
                            <div class=\"help-block\">$byline</div>
                        </div>
                    </div>";

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function test_render_no_byline()
    {
        $factory = $this->buildFactory();
        $renderer = $this->getDefaultRenderer();
        $label = "Test Label";
        $id = "id_1";
        $name = "name_0";
        $url = $factory->url($label)->withNameFrom($this->name_source);
        $html = $this->normalizeHTML($renderer->render($url));

        $expected = "<div class=\"form-group row\">
                        <label for=\"$id\" class=\"control-label col-sm-3\">$label</label>
                        <div class=\"col-sm-9\">
                            <input id=\"$id\" type=\"url\" name=\"$name\" class=\"form-control form-control-sm\" />
                        </div>
                    </div>";
        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function test_render_value()
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
                        <label for=\"$id\" class=\"control-label col-sm-3\">$label</label>
                        <div class=\"col-sm-9\">
                           <input id=\"$id\" type=\"url\" value=\"$value\" name=\"$name\" class=\"form-control form-control-sm\" />
                        </div>
                     </div>";
        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function test_render_required()
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
                        <label for=\"$id\" class=\"control-label col-sm-3\">$label<span class=\"asterisk\">*</span></label>
                        <div class=\"col-sm-9\">
                            <input id=\"$id\" type=\"url\" name=\"$name\" class=\"form-control form-control-sm\" />
                        </div>
                    </div>";
        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function test_render_disabled()
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
                        <label for=\"$id\" class=\"control-label col-sm-3\">$label</label>
                        <div class=\"col-sm-9\">
                            <input id=\"$id\" type=\"url\" name=\"$name\" disabled=\"disabled\" class=\"form-control form-control-sm\" />
                        </div>
                    </div>";

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }
}
