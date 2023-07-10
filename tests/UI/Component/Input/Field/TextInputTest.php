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

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/InputTest.php");

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Component\Input\Field;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;

class TextInputTest extends ILIAS_UI_TestBase
{
    protected DefNamesource $name_source;

    public function setUp(): void
    {
        $this->name_source = new DefNamesource();
    }

    protected function buildFactory(): I\Input\Field\Factory
    {
        $df = new Data\Factory();
        $language = $this->createMock(ilLanguage::class);
        return new I\Input\Field\Factory(
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class),
            new SignalGenerator(),
            $df,
            new Refinery($df, $language),
            $language
        );
    }

    public function test_implements_factory_interface(): void
    {
        $f = $this->buildFactory();

        $text = $f->text("label", "byline");

        $this->assertInstanceOf(Field\Input::class, $text);
        $this->assertInstanceOf(Field\Text::class, $text);
    }

    public function test_render(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $text = $f->text($label, $byline)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($text));

        $expected = $this->brutallyTrimHTML('
<div class="form-group row">
   <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>	
   <div class="col-sm-8 col-md-9 col-lg-10">
      <input id="id_1" type="text" name="name_0" class="form-control form-control-sm" />		
      <div class="help-block">byline</div>
   </div>
</div>
');
        $this->assertEquals($expected, $html);
    }

    public function test_render_error(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $error = "an_error";
        $text = $f->text($label, $byline)->withNameFrom($this->name_source)->withError($error);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($text));

        $expected = $this->brutallyTrimHTML('
<div class="form-group row">
   <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
   <div class="col-sm-8 col-md-9 col-lg-10">
      <div class="help-block alert alert-danger" aria-describedby="id_1" role="alert">an_error</div>
      <input id="id_1" type="text" name="name_0" class="form-control form-control-sm" />
      <div class="help-block">byline</div>
   </div>
</div>
');
        $this->assertEquals($expected, $html);
    }

    public function test_render_no_byline(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $text = $f->text($label)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($text));

        $expected = $this->brutallyTrimHTML('
<div class="form-group row">
   <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>	
   <div class="col-sm-8 col-md-9 col-lg-10"><input id="id_1" type="text" name="name_0" class="form-control form-control-sm" /></div>
</div>
');
        $this->assertEquals($expected, $html);
    }

    public function test_render_value(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $value = "value";
        $text = $f->text($label)->withValue($value)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($text));

        $expected = $this->brutallyTrimHTML('
<div class="form-group row">
   <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>	
   <div class="col-sm-8 col-md-9 col-lg-10"><input id="id_1" type="text" value="value" name="name_0" class="form-control form-control-sm" /></div>
</div>
');
        $this->assertEquals($expected, $html);
    }

    public function test_render_value_0(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $value = "0";
        $text = $f->text($label)->withValue($value)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($text));

        $expected = $this->brutallyTrimHTML('
<div class="form-group row">
   <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>	
   <div class="col-sm-8 col-md-9 col-lg-10"><input id="id_1" type="text" value="0" name="name_0" class="form-control form-control-sm" /></div>
</div>
');
        $this->assertEquals($expected, $html);
    }

    public function test_render_required(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $text = $f->text($label)->withNameFrom($this->name_source)->withRequired(true);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($text));

        $expected = $this->brutallyTrimHTML('
<div class="form-group row">
   <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">label<span class="asterisk">*</span></label>	
   <div class="col-sm-8 col-md-9 col-lg-10"><input id="id_1" type="text" name="name_0" class="form-control form-control-sm" /></div>
</div>
');
        $this->assertEquals($expected, $html);
    }

    public function test_render_disabled(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $text = $f->text($label)->withNameFrom($this->name_source)->withDisabled(true);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($text));

        $expected = $this->brutallyTrimHTML('
<div class="form-group row">
   <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>	
   <div class="col-sm-8 col-md-9 col-lg-10"><input id="id_1" type="text" name="name_0" disabled="disabled" class="form-control form-control-sm" /></div>
</div>
');
        $this->assertEquals($expected, $html);
    }

    public function test_max_length(): void
    {
        $f = $this->buildFactory();

        $text = $f->text("")
        ->withMaxLength(4);

        $this->assertEquals(4, $text->getMaxLength());

        $text1 = $text->withValue("1234");
        $this->assertEquals("1234", $text1->getValue());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Argument 'value': Display value does not match input type.");
        $text->withValue("12345");
    }

    public function test_render_max_value(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $text = $f->text($label)->withNameFrom($this->name_source)->withMaxLength(8);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($text));

        $expected = $this->brutallyTrimHTML('
<div class="form-group row">
   <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>	
   <div class="col-sm-8 col-md-9 col-lg-10">				<input id="id_1" type="text" name="name_0" maxlength="8"  class="form-control form-control-sm" />					</div>
</div>
');
        $this->assertEquals($expected, $html);
    }

    public function test_value_required(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $name = "name_0";
        $text = $f->text($label)->withNameFrom($this->name_source)->withRequired(true);

        $text1 = $text->withInput(new DefInputData([$name => "0"]));
        $value1 = $text1->getContent();
        $this->assertTrue($value1->isOk());
        $this->assertEquals("0", $value1->value());

        $text2 = $text->withInput(new DefInputData([$name => ""]));
        $value2 = $text2->getContent();
        $this->assertTrue($value2->isError());
    }

    public function test_stripsTags(): void
    {
        $f = $this->buildFactory();
        $name = "name_0";
        $text = $f->text("")
            ->withNameFrom($this->name_source)
            ->withInput(new DefInputData([$name => "<script>alert()</script>"]));

        $content = $text->getContent();
        $this->assertEquals("alert()", $content->value());
    }
}
