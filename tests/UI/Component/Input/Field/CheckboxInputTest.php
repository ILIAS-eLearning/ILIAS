<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/InputTest.php");

use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Implementation\Component\Input\InputData;
use \ILIAS\UI\Component\Input\Field;
use \ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;

class CheckboxInputTest extends ILIAS_UI_TestBase
{
    public function setUp() : void
    {
        $this->name_source = new DefNamesource();
        $this->refinery = new Refinery($this->createMock(Data\Factory::class), $this->createMock(\ilLanguage::class));
    }


    protected function buildFactory()
    {
        $df = new Data\Factory();
        $language = $this->createMock(\ilLanguage::class);
        return new ILIAS\UI\Implementation\Component\Input\Field\Factory(
            new SignalGenerator(),
            $df,
            new \ILIAS\Refinery\Factory($df, $language),
            $language
        );
    }


    public function testImplementsFactoryInterface()
    {
        $f = $this->buildFactory();

        $checkbox = $f->checkbox("label", "byline");

        $this->assertInstanceOf(Field\Input::class, $checkbox);
        $this->assertInstanceOf(Field\Checkbox::class, $checkbox);
    }


    public function testRender()
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $checkbox = $f->checkbox($label, $byline)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($checkbox));

        $expected = $this->brutallyTrimHTML('
        <div class="form-group row">
           <label for="id_1" class="control-label col-sm-3">label</label>
           <div class="col-sm-9">
              <input type="checkbox" id="id_1" value="checked" name="name_0" class="form-control form-control-sm"/>
              <div class="help-block">byline</div>
           </div>
        </div>
        ');
        $this->assertHTMLEquals($expected, $html);
    }


    public function testRenderError()
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $error = "an_error";
        $checkbox = $f->checkbox($label, $byline)->withNameFrom($this->name_source)->withError($error);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($checkbox));
        $expected = $this->brutallyTrimHTML('
        <div class="form-group row">
           <label for="id_1" class="control-label col-sm-3">label</label>
           <div class="col-sm-9">
              <div class="help-block alert alert-danger" role="alert">an_error</div>
              <input type="checkbox" id="id_1" value="checked" name="name_0" class="form-control form-control-sm"/>
              <div class="help-block">byline</div>
           </div>
        </div>
        ');

        $this->assertHTMLEquals($expected, $html);
    }


    public function testRenderNoByline()
    {
        $f = $this->buildFactory();
        $label = "label";
        $checkbox = $f->checkbox($label)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($checkbox));

        $expected = $this->brutallyTrimHTML('
        <div class="form-group row">
           <label for="id_1" class="control-label col-sm-3">label</label>
           <div class="col-sm-9">
              <input type="checkbox" id="id_1" value="checked" name="name_0" class="form-control form-control-sm" />
           </div>
        </div>
        ');
        $this->assertHTMLEquals($expected, $html);
    }


    public function testRenderValue()
    {
        $f = $this->buildFactory();
        $label = "label";
        $value = true;
        $checkbox = $f->checkbox($label)->withValue($value)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($checkbox));
        $expected = $this->brutallyTrimHTML('
            <div class="form-group row">
               <label for="id_1" class="control-label col-sm-3">label</label>
               <div class="col-sm-9">
                  <input type="checkbox" id="id_1" value="checked" checked="checked" name="name_0" class="form-control form-control-sm" />
               </div>
            </div>
        ');
        $this->assertHTMLEquals($expected, $html);
    }

    public function testHandleInvalidValue()
    {
        $f = $this->buildFactory();
        $label = "label";
        $value = "invalid";
        try {
            $f->checkbox($label)->withValue($value);
            $this->assertFalse(true);
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }


    public function testRenderRequired()
    {
        $f = $this->buildFactory();
        $label = "label";
        $checkbox = $f->checkbox($label)->withNameFrom($this->name_source)->withRequired(true);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($checkbox));

        $expected = $this->brutallyTrimHTML('
        <div class="form-group row">
           <label for="id_1" class="control-label col-sm-3">label<span class="asterisk">*</span></label>
           <div class="col-sm-9"><input type="checkbox" id="id_1" value="checked" name="name_0" class="form-control form-control-sm"/></div>
        </div>
        ');

        $this->assertHTMLEquals($expected, $html);
    }

    public function testRenderDisabled()
    {
        $f = $this->buildFactory();
        $label = "label";
        $checkbox = $f->checkbox($label)->withNameFrom($this->name_source)->withDisabled(true);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($checkbox));

        $expected = $this->brutallyTrimHTML('
            <div class="form-group row">
               <label for="id_1" class="control-label col-sm-3">label</label>
               <div class="col-sm-9"><input type="checkbox" id="id_1" value="checked" name="name_0" disabled="disabled" class="form-control form-control-sm"/></div>
            </div>
        ');

        $this->assertHTMLEquals($expected, $html);
    }

    public function testTrueContent()
    {
        $f = $this->buildFactory();
        $label = "label";
        $checkbox = $f->checkbox($label)->withNameFrom($this->name_source);

        $input_data = $this->createMock(InputData::class);
        $input_data
            ->expects($this->atLeastOnce())
            ->method("getOr")
            ->with("name_0", "")
            ->willReturn("checked");

        $checkbox_true = $checkbox->withInput($input_data);

        $this->assertIsBool($checkbox_true->getContent()->value());
        $this->assertTrue($checkbox_true->getContent()->value());
    }

    public function testFalseContent()
    {
        $f = $this->buildFactory();
        $label = "label";
        $checkbox = $f->checkbox($label)->withNameFrom($this->name_source);

        $input_data = $this->createMock(InputData::class);
        $input_data
            ->expects($this->atLeastOnce())
            ->method("getOr")
            ->with("name_0", "")
            ->willReturn("");

        $checkbox_false = $checkbox->withInput($input_data);

        $this->assertIsBool($checkbox_false->getContent()->value());
        $this->assertFalse($checkbox_false->getContent()->value());
    }

    public function testDisabledContent()
    {
        $f = $this->buildFactory();
        $label = "label";
        $checkbox = $f->checkbox($label)
            ->withNameFrom($this->name_source)
            ->withDisabled(true)
            ->withValue(true)
            ->withInput($this->createMock(InputData::class))
            ;

        $this->assertIsBool($checkbox->getContent()->value());
        $this->assertTrue($checkbox->getContent()->value());
    }

    public function testTransformation()
    {
        $f = $this->buildFactory();
        $label = "label";
        $called = false;
        $new_value = "NEW_VALUE";
        $checkbox = $f->checkbox($label)
            ->withNameFrom($this->name_source)
            ->withDisabled(true)
            ->withValue(true)
            ->withAdditionalTransformation($this->refinery->custom()->transformation(function ($v) use (&$called, $new_value) {
                $called = $v;
                return $new_value;
            }))
            ->withInput($this->createMock(InputData::class))
            ;

        $this->assertIsString($checkbox->getContent()->value());
        $this->assertEquals($new_value, $checkbox->getContent()->value());
    }

    public function testNullValue() : void
    {
        $f = $this->buildFactory();
        $checkbox = $f->checkbox("label");
        $checkbox->withValue(null);
        $this->assertEquals(false, $checkbox->getValue());
    }
}
