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
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Component\Input\Field;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;

class CheckboxInputTest extends ILIAS_UI_TestBase
{
    protected DefNamesource $name_source;
    protected Refinery $refinery;

    public function setUp(): void
    {
        $this->name_source = new DefNamesource();
        $this->refinery = new Refinery($this->createMock(Data\Factory::class), $this->createMock(ilLanguage::class));
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

    public function testImplementsFactoryInterface(): void
    {
        $f = $this->buildFactory();

        $checkbox = $f->checkbox("label", "byline");

        $this->assertInstanceOf(Field\Input::class, $checkbox);
        $this->assertInstanceOf(Field\Checkbox::class, $checkbox);
    }

    public function testRender(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $checkbox = $f->checkbox($label, $byline)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($checkbox));

        $expected = $this->brutallyTrimHTML('
        <div class="form-group row">
           <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
           <div class="col-sm-8 col-md-9 col-lg-10">
              <input type="checkbox" id="id_1" value="checked" name="name_0" class="form-control form-control-sm"/>
              <div class="help-block">byline</div>
           </div>
        </div>
        ');
        $this->assertHTMLEquals($expected, $html);
    }

    public function testRenderError(): void
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
           <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
           <div class="col-sm-8 col-md-9 col-lg-10">
              <div class="help-block alert alert-danger" role="alert">an_error</div>
              <input type="checkbox" id="id_1" value="checked" name="name_0" class="form-control form-control-sm"/>
              <div class="help-block">byline</div>
           </div>
        </div>
        ');

        $this->assertHTMLEquals($expected, $html);
    }

    public function testRenderNoByline(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $checkbox = $f->checkbox($label)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($checkbox));

        $expected = $this->brutallyTrimHTML('
        <div class="form-group row">
           <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
           <div class="col-sm-8 col-md-9 col-lg-10">
              <input type="checkbox" id="id_1" value="checked" name="name_0" class="form-control form-control-sm" />
           </div>
        </div>
        ');
        $this->assertHTMLEquals($expected, $html);
    }

    public function testRenderValue(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $value = true;
        $checkbox = $f->checkbox($label)->withValue($value)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($checkbox));
        $expected = $this->brutallyTrimHTML('
            <div class="form-group row">
               <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
               <div class="col-sm-8 col-md-9 col-lg-10">
                  <input type="checkbox" id="id_1" value="checked" checked="checked" name="name_0" class="form-control form-control-sm" />
               </div>
            </div>
        ');
        $this->assertHTMLEquals($expected, $html);
    }

    public function testHandleInvalidValue(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $value = "invalid";
        try {
            $f->checkbox($label)->withValue($value);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testRenderRequired(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $checkbox = $f->checkbox($label)->withNameFrom($this->name_source)->withRequired(true);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($checkbox));

        $expected = $this->brutallyTrimHTML('
        <div class="form-group row">
           <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">label<span class="asterisk">*</span></label>
           <div class="col-sm-8 col-md-9 col-lg-10"><input type="checkbox" id="id_1" value="checked" name="name_0" class="form-control form-control-sm"/></div>
        </div>
        ');

        $this->assertHTMLEquals($expected, $html);
    }

    public function testRenderDisabled(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $checkbox = $f->checkbox($label)->withNameFrom($this->name_source)->withDisabled(true);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($checkbox));

        $expected = $this->brutallyTrimHTML('
            <div class="form-group row">
               <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
               <div class="col-sm-8 col-md-9 col-lg-10"><input type="checkbox" id="id_1" value="checked" name="name_0" disabled="disabled" class="form-control form-control-sm"/></div>
            </div>
        ');

        $this->assertHTMLEquals($expected, $html);
    }

    public function testTrueContent(): void
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

    public function testFalseContent(): void
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

    public function testDisabledContent(): void
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

    public function testTransformation(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $new_value = "NEW_VALUE";
        $checkbox = $f->checkbox($label)
            ->withNameFrom($this->name_source)
            ->withDisabled(true)
            ->withValue(true)
            ->withAdditionalTransformation($this->refinery->custom()->transformation(function ($v) use (&$called, $new_value): string {
                $called = $v;
                return $new_value;
            }))
            ->withInput($this->createMock(InputData::class))
        ;

        $this->assertIsString($checkbox->getContent()->value());
        $this->assertEquals($new_value, $checkbox->getContent()->value());
    }

    public function testNullValue(): void
    {
        $f = $this->buildFactory();
        $checkbox = $f->checkbox("label");
        $checkbox->withValue(null);
        $this->assertEquals(false, $checkbox->getValue());
    }
}
