<?php declare(strict_types=1);

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Component as C;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Implementation\Component\Symbol as S;

class DurationInputTest extends ILIAS_UI_TestBase
{
    protected DefNamesource $name_source;
    protected Data\Factory $data_factory;
    protected I\Input\Field\Factory $factory;
    protected ilLanguage $lng;

    public function setUp() : void
    {
        $this->name_source = new DefNamesource();
        $this->data_factory = new Data\Factory();
        $this->factory = $this->buildFactory();
    }

    protected function buildLanguage() : ilLanguage
    {
        if (!isset($this->lng)) {
            $this->lng = $this->createMock(ilLanguage::class);
        }
        return $this->lng;
    }

    protected function buildRefinery() : Refinery
    {
        return new Refinery($this->data_factory, $this->buildLanguage());
    }

    protected function buildFactory() : I\Input\Field\Factory
    {
        return new I\Input\Field\Factory(
            new SignalGenerator(),
            $this->data_factory,
            $this->buildRefinery(),
            $this->buildLanguage()
        );
    }

    public function getUIFactory() : NoUIFactory
    {
        return new class extends NoUIFactory {
            public function symbol() : C\Symbol\Factory
            {
                return new S\Factory(
                    new S\Icon\Factory(),
                    new S\Glyph\Factory(),
                    new S\Avatar\Factory()
                );
            }
        };
    }

    public function test_withFormat() : void
    {
        $format = $this->data_factory->dateFormat()->germanShort();
        $duration = $this->factory->duration('label', 'byline')
            ->withFormat($format);

        $this->assertEquals(
            $format,
            $duration->getFormat()
        );
    }

    public function test_withMinValue() : void
    {
        $dat = new DateTimeImmutable('2019-01-09');
        $duration = $this->factory->duration('label', 'byline')
            ->withMinValue($dat);

        $this->assertEquals(
            $dat,
            $duration->getMinValue()
        );
    }

    public function test_withMaxValue() : void
    {
        $dat = new DateTimeImmutable('2019-01-09');
        $duration = $this->factory->duration('label', 'byline')
            ->withMaxValue($dat);

        $this->assertEquals(
            $dat,
            $duration->getMaxValue()
        );
    }

    public function test_withUseTime() : void
    {
        $datetime = $this->factory->duration('label', 'byline');
        $this->assertFalse($datetime->getUseTime());
        $this->assertTrue($datetime->withUseTime(true)->getUseTime());
    }

    public function test_withTimeOnly() : void
    {
        $datetime = $this->factory->duration('label', 'byline');
        $this->assertFalse($datetime->getTimeOnly());
        $this->assertTrue($datetime->withTimeOnly(true)->getTimeOnly());
    }

    public function test_withTimeZone() : void
    {
        $datetime = $this->factory->duration('label', 'byline');
        $this->assertNull($datetime->getTimeZone());
        $tz = 'Europe/Moscow';
        $this->assertEquals(
            $tz,
            $datetime->withTimeZone($tz)->getTimeZone()
        );
    }

    public function test_withInvalidTimeZone() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $datetime = $this->factory->duration('label', 'byline');
        $tz = 'NOT/aValidTZ';
        $datetime->withTimeZone($tz);
    }

    public function testWithoutByline() : void
    {
        $datetime = $this->factory->duration('label');
        $this->assertInstanceOf(C\Input\Field\Duration::class, $datetime);
    }

    public function test_render()
    {
        $datetime = $this->factory->duration('label', 'byline');
        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($datetime));

        $expected = $this->brutallyTrimHTML('
        <div class="form-group row">
           <label for="id_1" class="control-label col-sm-3">label</label>
           <div class="col-sm-9">
              <div class="il-input-duration" id="id_1">
                 <div class="form-group row">
                    <label for="id_2" class="control-label col-sm-3">' . I\Input\Field\Duration::DEFAULT_START_LABEL . '</label>
                    <div class="col-sm-9">
                       <div class="input-group date il-input-datetime" id="id_2"><input type="text" name="" placeholder="YYYY-MM-DD" class="form-control form-control-sm" /><span class="input-group-addon"><a class="glyph" href="#" aria-label="calendar"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></a></span></div>
                    </div>
                 </div>
                 <div class="form-group row">
                    <label for="id_3" class="control-label col-sm-3">' . I\Input\Field\Duration::DEFAULT_END_LABEL . '</label>
                    <div class="col-sm-9">
                       <div class="input-group date il-input-datetime" id="id_3"><input type="text" name="" placeholder="YYYY-MM-DD" class="form-control form-control-sm" /><span class="input-group-addon"><a class="glyph" href="#" aria-label="calendar"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></a></span></div>
                    </div>
                 </div>
              </div>
              <div class="help-block">byline</div>
           </div>
        </div>
        ');
        $this->assertEquals($expected, $html);

        return $datetime;
    }

    /**
     * @depends test_render
     */
    public function testRenderwithDifferentLabels($datetime)
    {
        $other_start_label = 'other startlabel';
        $other_end_label = 'other endlabel';
        $datetime = $datetime->withLabels($other_start_label, $other_end_label);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($datetime));

        $expected = $this->brutallyTrimHTML('
            <div class="form-group row">
               <label for="id_1" class="control-label col-sm-3">label</label>
               <div class="col-sm-9">
                  <div class="il-input-duration" id="id_1">
                     <div class="form-group row">
                        <label for="id_2" class="control-label col-sm-3">' . $other_start_label . '</label>
                        <div class="col-sm-9">
                           <div class="input-group date il-input-datetime" id="id_2"><input type="text" name="" placeholder="YYYY-MM-DD" class="form-control form-control-sm" /><span class="input-group-addon"><a class="glyph" href="#" aria-label="calendar"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></a></span></div>
                        </div>
                     </div>
                     <div class="form-group row">
                        <label for="id_3" class="control-label col-sm-3">' . $other_end_label . '</label>
                        <div class="col-sm-9">
                           <div class="input-group date il-input-datetime" id="id_3"><input type="text" name="" placeholder="YYYY-MM-DD" class="form-control form-control-sm" /><span class="input-group-addon"><a class="glyph" href="#" aria-label="calendar"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></a></span></div>
                        </div>
                     </div>
                  </div>
                  <div class="help-block">byline</div>
               </div>
            </div>
        ');
        $this->assertEquals($expected, $html);
    }
}
