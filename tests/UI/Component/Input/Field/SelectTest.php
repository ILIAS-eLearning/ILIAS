<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../Base.php");

use \ILIAS\Data;
use ILIAS\Refinery;
use ILIAS\UI\Implementation\Component\SignalGenerator;

class SelectForTest extends ILIAS\UI\Implementation\Component\Input\Field\Select
{
    public function _isClientSideValueOk($value)
    {
        return $this->isClientSideValueOk($value);
    }
}

class SelectInputTest extends ILIAS_UI_TestBase
{
    public function setUp() : void
    {
        $this->name_source = new DefNamesource();
    }

    protected function buildFactory()
    {
        $df = new Data\Factory();
        $language = $this->createMock(\ilLanguage::class);
        return new ILIAS\UI\Implementation\Component\Input\Field\Factory(
            new SignalGenerator(),
            $df,
            new Refinery\Factory($df, $language),
            $language
        );
    }

    public function testOnlyValuesFromOptionsAreAcceptableClientSideValues()
    {
        $options = ["one" => "Eins", "two" => "Zwei", "three" => "Drei"];
        $select = new SelectForTest(
            $this->createMock(ILIAS\Data\Factory::class),
            $this->createMock(ILIAS\Refinery\Factory::class),
            "",
            $options,
            ""
        );

        $this->assertTrue($select->_isClientSideValueOk("one"));
        $this->assertTrue($select->_isClientSideValueOk("two"));
        $this->assertTrue($select->_isClientSideValueOk("three"));
        $this->assertFalse($select->_isClientSideValueOk("four"));
    }

    public function testEmptyStringIsAcceptableClientSideValueIfSelectIsNotRequired()
    {
        $options = [];
        $select = new SelectForTest(
            $this->createMock(ILIAS\Data\Factory::class),
            $this->createMock(ILIAS\Refinery\Factory::class),
            "",
            $options,
            ""
        );

        $this->assertTrue($select->_isClientSideValueOk(""));
    }

    public function testEmptyStringIsAnAcceptableClientSideValueEvenIfSelectIsRequired()
    {
        $options = [];
        $select = (new SelectForTest(
            $this->createMock(ILIAS\Data\Factory::class),
            $this->createMock(ILIAS\Refinery\Factory::class),
            "",
            $options,
            ""
        ))->withRequired(true);

        $this->assertTrue($select->_isClientSideValueOk(""));
    }

    public function test_render()
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $options = ["one" => "One", "two" => "Two", "three" => "Three"];
        $select = $f->select($label, $options, $byline)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($select));

        $expected = $this->brutallyTrimHTML('
<div class="form-group row">
    <label for="id_1" class="control-label col-sm-3">label</label>
    <div class="col-sm-9">
        <select id="id_1" name="name_0">
            <option selected="selected" value="">-</option>
            <option value="one">One</option>
            <option value="two">Two</option>
            <option value="three">Three</option>
        </select>
        <div class="help-block">byline</div>
    </div>
</div>
');
        $this->assertEquals($expected, $html);
    }


    public function test_render_value()
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $options = ["one" => "One", "two" => "Two", "three" => "Three"];
        $select = $f->select($label, $options, $byline)->withNameFrom($this->name_source)->withValue("one");

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($select));

        $expected = $this->brutallyTrimHTML('
<div class="form-group row">
    <label for="id_1" class="control-label col-sm-3">label</label>
    <div class="col-sm-9">
        <select id="id_1" name="name_0">
            <option value="">-</option>
            <option selected="selected" value="one">One</option>
            <option value="two">Two</option>
            <option value="three">Three</option>
        </select>
        <div class="help-block">byline</div>
    </div>
</div>
');
        $this->assertEquals($expected, $html);
    }

    public function test_render_disabled()
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $options = ["one" => "One", "two" => "Two", "three" => "Three"];
        $select = $f->select($label, $options, $byline)->withNameFrom($this->name_source)->withDisabled(true);

        $r = $this->getDefaultRenderer();
        $html = $this->brutallyTrimHTML($r->render($select));

        $expected = $this->brutallyTrimHTML('
<div class="form-group row">
    <label for="id_1" class="control-label col-sm-3">label</label>
    <div class="col-sm-9">
        <select id="id_1" name="name_0" disabled="disabled">
            <option selected="selected" value="">-</option>
            <option value="one">One</option>
            <option value="two">Two</option>
            <option value="three">Three</option>
        </select>
        <div class="help-block">byline</div>
    </div>
</div>
');
        $this->assertEquals($expected, $html);
    }
}
