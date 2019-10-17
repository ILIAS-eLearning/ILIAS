<?php

/* Copyright (c) 2018 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/InputTest.php");

use ILIAS\UI\Implementation\Component\SignalGenerator;
use \ILIAS\UI\Component\Input\Field;
use \ILIAS\Data;
use \ILIAS\Validation;
use \ILIAS\Transformation;

class TextareaTest extends ILIAS_UI_TestBase
{

    /**
     * @var DefNamesource
     */
    private $name_source;

    public function setUp()
    {
        $this->name_source = new DefNamesource();
    }


    protected function buildFactory()
    {
        $df = new Data\Factory();
        return new ILIAS\UI\Implementation\Component\Input\Field\Factory(
            new SignalGenerator(),
            $df,
            new Validation\Factory($df, $this->createMock(\ilLanguage::class)),
            new Transformation\Factory()
        );
    }


    public function test_implements_factory_interface()
    {
        $f = $this->buildFactory();
        $textarea = $f->textarea("label", "byline");
        $this->assertInstanceOf(Field\Input::class, $textarea);
        $this->assertInstanceOf(Field\Textarea::class, $textarea);
    }


    public function test_implements_factory_interface_without_byline()
    {
        $f = $this->buildFactory();
        $textarea = $f->textarea("label");
        $this->assertInstanceOf(Field\Input::class, $textarea);
        $this->assertInstanceOf(Field\Textarea::class, $textarea);
    }


    public function test_with_min_limit()
    {
        $f = $this->buildFactory();
        $limit = 5;
        $textarea = $f->textarea('label')->withMinLimit($limit);
        $this->assertInstanceOf(Field\Input::class, $textarea);
        $this->assertInstanceOf(Field\Textarea::class, $textarea);
        $this->assertEquals($textarea->getMinLimit(), $limit);
    }


    public function test_with_max_limit()
    {
        $f = $this->buildFactory();
        $limit = 15;
        $textarea = $f->textarea('label')->withMaxLimit($limit);
        $this->assertInstanceOf(Field\Input::class, $textarea);
        $this->assertInstanceOf(Field\Textarea::class, $textarea);
        $this->assertEquals($textarea->getMaxLimit(), $limit);
    }


    public function test_is_limited()
    {
        $f = $this->buildFactory();

        // with min limit
        $textarea = $f->textarea('label')->withMinLimit(5);
        $this->assertTrue($textarea->isLimited());

        // with max limit
        $textarea = $f->textarea('label')->withMaxLimit(5);
        $this->assertTrue($textarea->isLimited());

        // with min-max limit
        $textarea = $f->textarea('label')->withMinLimit(5)->withMaxLimit(20);
        $this->assertTrue($textarea->isLimited());

        // without limit
        $textarea = $f->textarea('label');
        $this->assertFalse($textarea->isLimited());
    }


    public function test_get_min_limit()
    {
        $f = $this->buildFactory();
        $limit = 5;
        $textarea = $f->textarea('label')->withMinLimit($limit);
        $this->assertEquals($textarea->getMinLimit(), $limit);
    }


    public function test_get_max_limit()
    {
        $f = $this->buildFactory();
        $limit = 15;
        $textarea = $f->textarea('label')->withMaxLimit($limit);
        $this->assertEquals($textarea->getMaxLimit(), $limit);
    }


    // RENDERER
    public function test_renderer()
    {
        $f = $this->buildFactory();
        $r = $this->getDefaultRenderer();
        $label = "label";
        $byline = "byline";
        $name = "name_0";
        $textarea = $f->textarea($label, $byline)->withNameFrom($this->name_source);

        $expected = "<div class=\"form-group row\">"
                . "<label for=\"$name\" class=\"control-label col-sm-3\">$label</label>"
                . "<div class=\"col-sm-9\">"
                . "<textarea name=\"$name\" class=\"form-control form-control-sm\" id=\"\"></textarea>"
                . "<div id=\"textarea_feedback_\" data-maxchars=\"\"></div>"
                . "<div class=\"help-block\">byline</div>"
                . "</div>"
                . "</div>";

        $html = $this->normalizeHTML($r->render($textarea));
        $this->assertHTMLEquals($expected, $html);
    }

    public function test_renderer_with_min_limit()
    {
        $f = $this->buildFactory();
        $r = $this->getDefaultRenderer();
        $name = "name_0";
        $id = "id_1";
        $label = "label";

        $min = 5;
        $byline = "This is just a byline Min: " . $min;
        $textarea = $f->textarea($label, $byline)->withMinLimit($min)->withNameFrom($this->name_source);

        $expected = "<div class=\"form-group row\">"
            . "<label for=\"$name\" class=\"control-label col-sm-3\">$label</label>"
            . "<div class=\"col-sm-9\">"
            . "<textarea name=\"$name\" class=\"form-control form-control-sm\" id=\"$id\"></textarea>"
            . "<div id=\"textarea_feedback_$id\" data-maxchars=\"\"></div>"
            . "<div class=\"help-block\">$byline</div>"
            . "</div>"
            . "</div>";

        $html = $this->normalizeHTML($r->render($textarea));
        $this->assertHTMLEquals($expected, $html);
    }

    public function test_renderer_with_max_limit()
    {
        $f = $this->buildFactory();
        $r = $this->getDefaultRenderer();
        $name = "name_0";
        $id = "id_1";
        $label = "label";
        $max = 20;
        $byline = "This is just a byline Max: " . $max;
        $textarea = $f->textarea($label, $byline)->withMaxLimit($max)->withNameFrom($this->name_source);

        $expected = "<div class=\"form-group row\">"
            . "<label for=\"$name\" class=\"control-label col-sm-3\">$label</label>"
            . "<div class=\"col-sm-9\">"
            . "<textarea name=\"$name\" class=\"form-control form-control-sm\" id=\"$id\"></textarea>"
            . "<div id=\"textarea_feedback_$id\" data-maxchars=\"$max\"></div>"
            . "<div class=\"help-block\">$byline</div>"
            . "</div>"
            . "</div>";

        $html = $this->normalizeHTML($r->render($textarea));
        $this->assertHTMLEquals($expected, $html);
    }

    public function test_renderer_with_min_and_max_limit()
    {
        $f = $this->buildFactory();
        $r = $this->getDefaultRenderer();
        $name = "name_0";
        $id = "id_1";
        $label = "label";
        $min = 5;
        $max = 20;
        $byline = "This is just a byline Min: " . $min . " Max: " . $max;
        $textarea = $f->textarea($label, $byline)->withMinLimit($min)->withMaxLimit($max)->withNameFrom($this->name_source);

        $expected = "<div class=\"form-group row\">"
            . "<label for=\"$name\" class=\"control-label col-sm-3\">$label</label>"
            . "<div class=\"col-sm-9\">"
            . "<textarea name=\"$name\" class=\"form-control form-control-sm\" id=\"$id\"></textarea>"
            . "<div id=\"textarea_feedback_$id\" data-maxchars=\"$max\"></div>"
            . "<div class=\"help-block\">$byline</div>"
            . "</div>"
            . "</div>";

        $html = $this->normalizeHTML($r->render($textarea));
        $this->assertHTMLEquals($expected, $html);
    }

    public function test_renderer_counter_with_value()
    {
        $f = $this->buildFactory();
        $r = $this->getDefaultRenderer();
        $label = "label";
        $byline = "byline";
        $name = "name_0";
        $value = "Lorem ipsum dolor sit";
        $textarea = $f->textarea($label, $byline)->withValue($value)->withNameFrom($this->name_source);

        $expected = "<div class=\"form-group row\">"
            . "<label for=\"$name\" class=\"control-label col-sm-3\">$label</label>"
            . "<div class=\"col-sm-9\">"
            . "<textarea name=\"$name\" class=\"form-control form-control-sm\" id=\"\">$value</textarea>"
            . "<div id=\"textarea_feedback_\" data-maxchars=\"\"></div>"
            . "<div class=\"help-block\">byline</div>"
            . "</div>"
            . "</div>";

        $html = $this->normalizeHTML($r->render($textarea));
        $this->assertHTMLEquals($expected, $html);
    }

    public function test_renderer_with_error()
    {
        $f = $this->buildFactory();
        $r = $this->getDefaultRenderer();
        $name = "name_0";
        $label = "label";
        $min = 5;
        $byline = "This is just a byline Min: " . $min;
        $error = "an_error";
        $textarea = $f->textarea($label, $byline)->withNameFrom($this->name_source)->withError($error);

        $expected = "<div class=\"form-group row\">"
            . "<label for=\"$name\" class=\"control-label col-sm-3\">$label</label>"
            . "<div class=\"col-sm-9\">"
            . "<textarea name=\"$name\" class=\"form-control form-control-sm\" id=\"\"></textarea>"
            . "<div id=\"textarea_feedback_\" data-maxchars=\"\"></div>"
            . "<div class=\"help-block\">$byline</div>"
            . "<div class=\"help-block alert alert-danger\" role=\"alert\">"
            . "<img border=\"0\" src=\"./templates/default/images/icon_alert.svg\" alt=\"alert\" />"
            . "$error</div></div></div>";

        $html = $this->normalizeHTML($r->render($textarea));
        $html = trim(preg_replace('/\t+/', '', $html));
        $expected = trim(preg_replace('/\t+/', '', $expected));
        $this->assertEquals($expected, $html);
    }

    public function test_stripsTags()
    {
        $f = $this->buildFactory();
        $name = "name_0";
        $text = $f->textarea("")
            ->withNameFrom($this->name_source)
            ->withInput(new DefPostData([$name => "<script>alert()</script>"]));

        $content = $text->getContent();
        $this->assertEquals("alert()", $content->value());
    }
}
