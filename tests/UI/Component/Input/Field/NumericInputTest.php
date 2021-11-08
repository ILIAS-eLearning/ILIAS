<?php declare(strict_types=1);

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/InputTest.php");

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Component\Input\Field;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;

class NumericInputTest extends ILIAS_UI_TestBase
{
    protected DefNamesource $name_source;

    public function setUp() : void
    {
        $this->name_source = new DefNamesource();
    }

    protected function buildFactory() : I\Input\Field\Factory
    {
        $df = new Data\Factory();
        $language = $this->getLanguage();
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

        $numeric = $f->numeric("label", "byline");

        $this->assertInstanceOf(Field\Input::class, $numeric);
        $this->assertInstanceOf(Field\Numeric::class, $numeric);
    }


    public function test_render() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $numeric = $f->numeric($label, $byline)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($numeric));

        $expected = $this->brutallyTrimHTML('
<div class="form-group row">
   <label for="id_1" class="control-label col-sm-3">label</label>
   <div class="col-sm-9">
      <input id="id_1" type="number" name="name_0" class="form-control form-control-sm" />
      <div class="help-block">byline</div>
   </div>
</div>
');
        $this->assertEquals($expected, $html);
    }

    public function test_render_error() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $error = "an_error";
        $numeric = $f->numeric($label, $byline)->withNameFrom($this->name_source)->withError($error);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($numeric));

        $expected = $this->brutallyTrimHTML('
<div class="form-group row">
   <label for="id_1" class="control-label col-sm-3">label</label>	
   <div class="col-sm-9">
      <div class="help-block alert alert-danger" role="alert">an_error</div>
      <input id="id_1" type="number" name="name_0" class="form-control form-control-sm" />		
      <div class="help-block">byline</div>
   </div>
</div>');
        $this->assertEquals($expected, $html);
    }

    public function test_render_no_byline() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $numeric = $f->numeric($label)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($numeric));

        $expected = $this->brutallyTrimHTML('
<div class="form-group row">
   <label for="id_1" class="control-label col-sm-3">label</label>	
   <div class="col-sm-9">		<input id="id_1" type="number" name="name_0" class="form-control form-control-sm" />					</div>
</div>
');
        $this->assertEquals($expected, $html);
    }

    public function test_render_value() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $value = "10";
        $numeric = $f->numeric($label)->withValue($value)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($numeric));

        $expected = $this->brutallyTrimHTML('
<div class="form-group row">
   <label for="id_1" class="control-label col-sm-3">label</label>	
   <div class="col-sm-9">		<input id="id_1" type="number" value="10" name="name_0" class="form-control form-control-sm" />					</div>
</div>
');
        $this->assertEquals($expected, $html);
    }

    public function test_render_disabled() : void
    {
        $f = $this->buildFactory();
        $label = "label";
        $numeric = $f->numeric($label)->withNameFrom($this->name_source)->withDisabled(true);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($numeric));

        $expected = $this->brutallyTrimHTML('
<div class="form-group row">
   <label for="id_1" class="control-label col-sm-3">label</label>	
   <div class="col-sm-9">		<input id="id_1" type="number" name="name_0" disabled="disabled" class="form-control form-control-sm" />					</div>
</div>');
        $this->assertEquals($expected, $html);
    }

    public function testNullValue() : Field\Input
    {
        $f = $this->buildFactory();
        $post_data = new DefInputData(['name_0' => null]);
        $field = $f->numeric('')->withNameFrom($this->name_source);
        $field_required = $field->withRequired(true);

        $value = $field->withInput($post_data)->getContent();
        $this->assertTrue($value->isOk());
        $this->assertNull($value->value());

        $value = $field_required->withInput($post_data)->getContent();
        $this->assertTrue($value->isError());
        return $field;
    }

    /**
     * @depends testNullValue
     */
    public function testEmptyValue(Field\Input $field) : void
    {
        $post_data = new DefInputData(['name_0' => '']);
        $field_required = $field->withRequired(true);

        $value = $field->withInput($post_data)->getContent();
        $this->assertTrue($value->isOk());
        $this->assertNull($value->value());

        $field_required = $field_required->withInput($post_data);
        $value = $field_required->getContent();
        $this->assertTrue($value->isError());
    }

    /**
     * @depends testNullValue
     */
    public function testZeroIsValidValue(Field\Input $field) : void
    {
        $post_data = new DefInputData(['name_0' => 0]);
        $field_required = $field->withRequired(true);

        $value = $field->withInput($post_data)->getContent();
        $this->assertTrue($value->isOk());
        $this->assertEquals(0, $value->value());

        $value = $field_required->withInput($post_data)->getContent();
        $this->assertTrue($value->isOK());
        $this->assertEquals(0, $value->value());
    }
}
