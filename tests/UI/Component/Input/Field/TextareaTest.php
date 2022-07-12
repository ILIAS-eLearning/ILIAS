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
 
require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/InputTest.php");

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Component\Input\Field;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;

class TextareaTest extends ILIAS_UI_TestBase
{
    private DefNamesource $name_source;

    public function setUp() : void
    {
        $this->name_source = new DefNamesource();
    }

    protected function buildFactory() : I\Input\Field\Factory
    {
        $df = new Data\Factory();
        $language = $this->createMock(ilLanguage::class);
        return new I\Input\Field\Factory(
            new SignalGenerator(),
            $df,
            new Refinery($df, $language),
            $language
        );
    }

    public function test_implements_factory_interface() : void
    {
        $f = $this->buildFactory();
        $textarea = $f->textarea("label", "byline");
        $this->assertInstanceOf(Field\Input::class, $textarea);
        $this->assertInstanceOf(Field\Textarea::class, $textarea);
    }

    public function test_implements_factory_interface_without_byline() : void
    {
        $f = $this->buildFactory();
        $textarea = $f->textarea("label");
        $this->assertInstanceOf(Field\Input::class, $textarea);
        $this->assertInstanceOf(Field\Textarea::class, $textarea);
    }

    public function test_with_min_limit() : void
    {
        $f = $this->buildFactory();
        $limit = 5;
        $textarea = $f->textarea('label')->withMinLimit($limit);
        $this->assertInstanceOf(Field\Input::class, $textarea);
        $this->assertInstanceOf(Field\Textarea::class, $textarea);
        $this->assertEquals($textarea->getMinLimit(), $limit);
    }

    public function test_with_max_limit() : void
    {
        $f = $this->buildFactory();
        $limit = 15;
        $textarea = $f->textarea('label')->withMaxLimit($limit);
        $this->assertInstanceOf(Field\Input::class, $textarea);
        $this->assertInstanceOf(Field\Textarea::class, $textarea);
        $this->assertEquals($textarea->getMaxLimit(), $limit);
    }

    public function test_is_limited() : void
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

    public function test_get_min_limit() : void
    {
        $f = $this->buildFactory();
        $limit = 5;
        $textarea = $f->textarea('label')->withMinLimit($limit);
        $this->assertEquals($textarea->getMinLimit(), $limit);
    }

    public function test_get_max_limit() : void
    {
        $f = $this->buildFactory();
        $limit = 15;
        $textarea = $f->textarea('label')->withMaxLimit($limit);
        $this->assertEquals($textarea->getMaxLimit(), $limit);
    }

    // RENDERER
    public function test_renderer() : void
    {
        $f = $this->buildFactory();
        $r = $this->getDefaultRenderer();
        $label = "label";
        $byline = "byline";
        $name = "name_0";
        $textarea = $f->textarea($label, $byline)->withNameFrom($this->name_source);

        $expected = "<div class=\"form-group row\">"
                . "<label for=\"id_1\" class=\"control-label col-sm-4 col-md-3 col-lg-2\">$label</label>"
                . "<div class=\"col-sm-8 col-md-9 col-lg-10\">"
                . "<textarea id=\"id_1\" name=\"$name\" class=\"form-control form-control-sm\"></textarea>"
                . "<div class=\"help-block\">byline</div>"
                . "</div>"
                . "</div>";

        $html = $this->normalizeHTML($r->render($textarea));
        $this->assertHTMLEquals($expected, $html);
    }

    public function test_renderer_with_min_limit() : void
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
            . "<label for=\"id_1\" class=\"control-label col-sm-4 col-md-3 col-lg-2\">$label</label>"
            . "<div class=\"col-sm-8 col-md-9 col-lg-10\">"
            . "<textarea id=\"$id\" name=\"$name\" class=\"form-control form-control-sm\"></textarea>"
            . "<div id=\"textarea_feedback_$id\" data-maxchars=\"\"></div>"
            . "<div class=\"help-block\">$byline</div>"
            . "</div>"
            . "</div>";

        $html = $this->normalizeHTML($r->render($textarea));
        $this->assertHTMLEquals($expected, $html);
    }

    public function test_renderer_with_max_limit() : void
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
            . "<label for=\"id_1\" class=\"control-label col-sm-4 col-md-3 col-lg-2\">$label</label>"
            . "<div class=\"col-sm-8 col-md-9 col-lg-10\">"
            . "<textarea id=\"$id\" name=\"$name\" class=\"form-control form-control-sm\"></textarea>"
            . "<div id=\"textarea_feedback_$id\" data-maxchars=\"$max\"></div>"
            . "<div class=\"help-block\">$byline</div>"
            . "</div>"
            . "</div>";

        $html = $this->normalizeHTML($r->render($textarea));
        $this->assertHTMLEquals($expected, $html);
    }

    public function test_renderer_with_min_and_max_limit() : void
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
            . "<label for=\"id_1\" class=\"control-label col-sm-4 col-md-3 col-lg-2\">$label</label>"
            . "<div class=\"col-sm-8 col-md-9 col-lg-10\">"
            . "<textarea id=\"$id\" name=\"$name\" class=\"form-control form-control-sm\"></textarea>"
            . "<div id=\"textarea_feedback_$id\" data-maxchars=\"$max\"></div>"
            . "<div class=\"help-block\">$byline</div>"
            . "</div>"
            . "</div>";

        $html = $this->normalizeHTML($r->render($textarea));
        $this->assertHTMLEquals($expected, $html);
    }

    public function test_renderer_counter_with_value() : void
    {
        $f = $this->buildFactory();
        $r = $this->getDefaultRenderer();
        $label = "label";
        $byline = "byline";
        $name = "name_0";
        $value = "Lorem ipsum dolor sit";
        $textarea = $f->textarea($label, $byline)->withValue($value)->withNameFrom($this->name_source);

        $expected = "<div class=\"form-group row\">"
            . "<label for=\"id_1\" class=\"control-label col-sm-4 col-md-3 col-lg-2\">$label</label>"
            . "<div class=\"col-sm-8 col-md-9 col-lg-10\">"
            . "<textarea id=\"id_1\" name=\"$name\" class=\"form-control form-control-sm\">$value</textarea>"
            . "<div class=\"help-block\">byline</div>"
            . "</div>"
            . "</div>";

        $html = $this->normalizeHTML($r->render($textarea));
        $this->assertHTMLEquals($expected, $html);
    }

    public function test_renderer_with_error() : void
    {
        $f = $this->buildFactory();
        $r = $this->getDefaultRenderer();
        $label = "label";
        $min = 5;
        $byline = "This is just a byline Min: " . $min;
        $error = "an_error";
        $textarea = $f->textarea($label, $byline)->withNameFrom($this->name_source)->withError($error);

        $expected = $this->brutallyTrimHTML('
<div class="form-group row">
   <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
   <div class="col-sm-8 col-md-9 col-lg-10">
      <div class="help-block alert alert-danger" role="alert">an_error</div>
      <textarea id="id_1" name="name_0" class="form-control form-control-sm"></textarea>
      <div class="help-block">This is just a byline Min: 5</div>
   </div>
</div>
');

        $html = $this->brutallyTrimHTML($r->render($textarea));
        $this->assertEquals($expected, $html);
    }

    public function test_renderer_with_disabled() : void
    {
        $f = $this->buildFactory();
        $r = $this->getDefaultRenderer();
        $label = "label";
        $byline = "byline";
        $name = "name_0";
        $textarea = $f->textarea($label, $byline)->withNameFrom($this->name_source)->withDisabled(true);

        $expected = "<div class=\"form-group row\">"
            . "<label for=\"id_1\" class=\"control-label col-sm-4 col-md-3 col-lg-2\">$label</label>"
            . "<div class=\"col-sm-8 col-md-9 col-lg-10\">"
            . "<textarea id=\"id_1\" name=\"$name\" disabled=\"disabled\" class=\"form-control form-control-sm\"></textarea>"
            . "<div class=\"help-block\">byline</div>"
            . "</div>"
            . "</div>";

        $html = $this->normalizeHTML($r->render($textarea));
        $this->assertHTMLEquals($expected, $html);
    }

    public function test_stripsTags() : void
    {
        $f = $this->buildFactory();
        $name = "name_0";
        $text = $f->textarea("")
            ->withNameFrom($this->name_source)
            ->withInput(new DefInputData([$name => "<script>alert()</script>"]));

        $content = $text->getContent();
        $this->assertEquals("alert()", $content->value());
    }
}
