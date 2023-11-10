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

require_once(__DIR__ . "/../../../../../../../vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/InputTest.php");

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Component\Input\Field;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Implementation\Component\SignalGenerator;

class ColorPickerInputTest extends ILIAS_UI_TestBase
{
    protected DefNamesource $name_source;

    public function setUp(): void
    {
        $this->name_source = new DefNamesource();
    }

    protected function buildFactory(): I\Input\Field\Factory
    {
        $df = new Data\Factory();
        $language = $this->getLanguage();
        return new I\Input\Field\Factory(
            $this->createMock(
                \ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class
            ),
            new SignalGenerator(),
            $df,
            new Refinery($df, $language),
            $language
        );
    }

    public function testImplementsFactoryInterface(): void
    {
        $f = $this->buildFactory();
        $cp = $f->colorpicker("label", "byline");
        $this->assertInstanceOf(\ILIAS\UI\Component\Input\Container\Form\FormInput::class, $cp);
        $this->assertInstanceOf(Field\ColorPicker::class, $cp);
    }

    public function testRender(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $name = "name_0";
        $cp = $f->colorpicker($label, $byline)->withNameFrom($this->name_source);
        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($cp));

        $expected = $this->brutallyTrimHTML('
            <div class="form-group row">
            <label class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
            <div class="col-sm-8 col-md-9 col-lg-10">
            <input id="id_1" type="color" name="name_0" value=""/>
            <div class="help-block">byline</div>
            </div>
            </div>
            ');
        $this->assertHTMLEquals($expected, $html);
    }

    public function testRenderDisabled(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $name = "name_0";
        $cp = $f->colorpicker($label, $byline)
                ->withNameFrom($this->name_source)
                ->withDisabled(true);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($cp));

        $expected = $this->brutallyTrimHTML('
            <div class="form-group row">
            <label class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
            <div class="col-sm-8 col-md-9 col-lg-10">
            <input id="id_1" type="color" name="name_0" value=""/>
            <div class="help-block">byline</div>
            </div>
            </div>
            ');
        $this->assertHTMLEquals($expected, $html);
    }

    public function testRenderRequired(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $name = "name_0";
        $cp = $f->colorpicker($label, $byline)
                ->withNameFrom($this->name_source)
                ->withRequired(true);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($cp));

        $expected = $this->brutallyTrimHTML('
            <div class="form-group row">
            <label class="control-label col-sm-4 col-md-3 col-lg-2">label
            <span class="asterisk">*</span></label>
            <div class="col-sm-8 col-md-9 col-lg-10">
            <input id="id_1" type="color" name="name_0" value=""/>
            <div class="help-block">byline</div>
            </div>
            </div>
            ');
        $this->assertHTMLEquals($expected, $html);
    }

    public function testRenderValue(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $value = "value_0";
        $cp = $f->colorpicker($label, $byline)
                ->withValue($value)
                ->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($cp));

        $expected = $this->brutallyTrimHTML('
            <div class="form-group row">
            <label class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
            <div class="col-sm-8 col-md-9 col-lg-10">
            <input id="id_1" type="color" name="name_0" value="value_0"/>
            <div class="help-block">byline</div>
            </div>
            </div>
            ');
        $this->assertHTMLEquals($expected, $html);
    }

    public function testValueRequired(): void
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $name = "name_0";
        $cp = $f->colorpicker($label, $byline)
                ->withNameFrom($this->name_source)
                ->withRequired(true);

        $cp1 = $cp->withInput(new DefInputData([$name => "#FFF"]));
        $value1 = $cp1->getContent();
        $this->assertTrue($value1->isOk());

        $cp2 = $cp->withInput(new DefInputData([$name => "#00"]));
        $value2 = $cp2->getContent();
        $this->assertTrue($value2->isError());

        $cp3 = $cp->withInput(new DefInputData([$name => ""]));
        $value2 = $cp3->getContent();
        $this->assertTrue($value2->isError());
    }

    public function testNullValue(): void
    {
        $f = $this->buildFactory();
        $colorpicker = $f->colorpicker("label", "byline");
        $this->expectException(\InvalidArgumentException::class);
        $colorpicker->withValue(null);
    }
}
