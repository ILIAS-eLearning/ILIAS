<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");


use ILIAS\UI\Implementation\Component\SignalGenerator;
use \ILIAS\UI\Implementation\Component\Input\NameSource;
use \ILIAS\UI\Component\Input\Field;
use \ILIAS\Data;
use \ILIAS\Refinery\Validation;
use \ILIAS\Refinery\Transformation;
use ILIAS\UI\Implementation\Component\Symbol as S;

class DateTimeInputTest extends ILIAS_UI_TestBase
{
    public function setUp() : void
    {
        $this->name_source = new DefNamesource();
        $this->data_factory = new Data\Factory();
        $this->factory = $this->buildFactory();
    }

    public function getUIFactory()
    {
        $factory = new class extends NoUIFactory {
            public function symbol() : \ILIAS\UI\Component\Symbol\Factory
            {
                return new S\Factory(
                    new S\Icon\Factory(),
                    new S\Glyph\Factory(),
                    new S\Avatar\Factory()
                );
            }
        };
        return $factory;
    }

    public function getLanguage()
    {
        $languageMock = new class extends ilLanguageMock {
            public function getLangKey() : string
            {
                return 'en';
            }
        };
        return new $languageMock();
    }

    protected function buildFactory()
    {
        $df = new Data\Factory();
        $language = $this->createMock(\ilLanguage::class);

        return new ILIAS\UI\Implementation\Component\Input\Field\Factory(
            new SignalGenerator(),
            $this->data_factory,
            new \ILIAS\Refinery\Factory($df, $language),
            $language
        );
    }
 
    public function test_withFormat()
    {
        $format = $this->data_factory->dateFormat()->germanShort();
        $datetime = $this->factory->datetime('label', 'byline')
            ->withFormat($format);

        $this->assertEquals(
            $format,
            $datetime->getFormat()
        );
    }

    public function test_withMinValue()
    {
        $dat = new \DateTimeImmutable('2019-01-09');
        $datetime = $this->factory->datetime('label', 'byline')
            ->withMinValue($dat);

        $this->assertEquals(
            $dat,
            $datetime->getMinValue()
        );
    }

    public function test_withMaxValue()
    {
        $dat = new \DateTimeImmutable('2019-01-09');
        $datetime = $this->factory->datetime('label', 'byline')
            ->withMaxValue($dat);

        $this->assertEquals(
            $dat,
            $datetime->getMaxValue()
        );
    }

    public function test_withUseTime()
    {
        $datetime = $this->factory->datetime('label', 'byline');
        $this->assertFalse($datetime->getUseTime());
        $this->assertTrue($datetime->withUseTime(true)->getUseTime());
    }

    public function test_withTimeOnly()
    {
        $datetime = $this->factory->datetime('label', 'byline');
        $this->assertFalse($datetime->getTimeOnly());
        $this->assertTrue($datetime->withTimeOnly(true)->getTimeOnly());
    }

    public function test_withTimeZone()
    {
        $datetime = $this->factory->datetime('label', 'byline');
        $this->assertNull($datetime->getTimeZone());
        $tz = 'Europe/Moscow';
        $this->assertEquals(
            $tz,
            $datetime->withTimeZone($tz)->getTimeZone()
        );
    }

    public function test_withInvalidTimeZone()
    {
        $this->expectException(\InvalidArgumentException::class);
        $datetime = $this->factory->datetime('label', 'byline');
        $tz = 'NOT/aValidTZ';
        $datetime->withTimeZone($tz);
    }

    public function test_jsConfigRendering()
    {
        $datetime = $this->factory->datetime('label', 'byline');
        $js_binding = $this->getJavaScriptBinding();
        $html = $this->getDefaultRenderer($js_binding)->render($datetime);

        $expected = '$("#id_1").datetimepicker({'
            . '"showClear":true,'
            . '"sideBySide":true,'
            . '"format":"YYYY-MM-DD",'
            . '"locale":"en"'
            . '});';

        $onload_js = array_shift($js_binding->on_load_code);
        $this->assertEquals($expected, $onload_js);
    }
}
