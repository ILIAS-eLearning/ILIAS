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
require_once(__DIR__ . "/CommonFieldRendering.php");

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Component as C;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Implementation\Component\Symbol as S;
use ILIAS\Language\Language;

class DurationInputTest extends ILIAS_UI_TestBase
{
    use CommonFieldRendering;

    protected DefNamesource $name_source;
    protected Data\Factory $data_factory;
    protected I\Input\Field\Factory $factory;
    protected Language $lng;

    public function setUp(): void
    {
        $this->name_source = new DefNamesource();
        $this->data_factory = new Data\Factory();
        $this->factory = $this->buildFactory();
    }

    protected function buildLanguage(): Language
    {
        $this->lng = $this->createMock(Language::class);
        $this->lng->method("txt")
            ->will($this->returnArgument(0));

        return $this->lng;
    }

    protected function buildRefinery(): Refinery
    {
        return new Refinery($this->data_factory, $this->buildLanguage());
    }

    protected function buildFactory(): I\Input\Field\Factory
    {
        return new I\Input\Field\Factory(
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class),
            new SignalGenerator(),
            $this->data_factory,
            $this->buildRefinery(),
            $this->buildLanguage()
        );
    }

    public function getUIFactory(): NoUIFactory
    {
        return new class () extends NoUIFactory {
            public function symbol(): I\Symbol\Factory
            {
                return new S\Factory(
                    new S\Icon\Factory(),
                    new S\Glyph\Factory(),
                    new S\Avatar\Factory()
                );
            }
        };
    }

    public function testWithFormat(): void
    {
        $format = $this->data_factory->dateFormat()->germanShort();
        $duration = $this->factory->duration('label', 'byline')
            ->withFormat($format);

        $this->assertEquals(
            $format,
            $duration->getFormat()
        );
    }

    public function testWithMinValue(): void
    {
        $dat = new DateTimeImmutable('2019-01-09');
        $duration = $this->factory->duration('label', 'byline')
            ->withMinValue($dat);

        $this->assertEquals(
            $dat,
            $duration->getMinValue()
        );
    }

    public function testWithMaxValue(): void
    {
        $dat = new DateTimeImmutable('2019-01-09');
        $duration = $this->factory->duration('label', 'byline')
            ->withMaxValue($dat);

        $this->assertEquals(
            $dat,
            $duration->getMaxValue()
        );
    }

    public function testWithUseTime(): void
    {
        $datetime = $this->factory->duration('label', 'byline');
        $this->assertFalse($datetime->getUseTime());
        $this->assertTrue($datetime->withUseTime(true)->getUseTime());
    }

    public function testWithTimeOnly(): void
    {
        $datetime = $this->factory->duration('label', 'byline');
        $this->assertFalse($datetime->getTimeOnly());
        $this->assertTrue($datetime->withTimeOnly(true)->getTimeOnly());
    }

    public function testWithTimeZone(): void
    {
        $datetime = $this->factory->duration('label', 'byline');
        $this->assertNull($datetime->getTimeZone());
        $tz = 'Europe/Moscow';
        $this->assertEquals(
            $tz,
            $datetime->withTimeZone($tz)->getTimeZone()
        );
    }

    public function testWithInvalidTimeZone(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $datetime = $this->factory->duration('label', 'byline');
        $tz = 'NOT/aValidTZ';
        $datetime->withTimeZone($tz);
    }

    public function testWithoutByline(): void
    {
        $datetime = $this->factory->duration('label');
        $this->assertInstanceOf(C\Input\Field\Duration::class, $datetime);
    }

    public function testRender(): \ILIAS\UI\Component\Input\Field\Duration
    {
        $duration = $this->factory->duration('label', 'byline')
            ->withNameFrom($this->name_source);
        $label_start = 'duration_default_label_start';
        $label_end = 'duration_default_label_end';

        $f1 = $this->getFormWrappedHtml(
            'date-time-field-input',
            $label_start,
            '<div class="c-input-group">
                <input id="id_1" type="date" name="name_0/start_1" class="c-field-datetime" />
            </div>
            ',
            null,
            'id_1',
            null,
            'name_0/start_1'
        );
        $f2 = $this->getFormWrappedHtml(
            'date-time-field-input',
            $label_end,
            '<div class="c-input-group">
                <input id="id_2" type="date" name="name_0/end_2" class="c-field-datetime" />
            </div>
            ',
            null,
            'id_2',
            null,
            'name_0/end_2'
        );

        $expected = $this->getFormWrappedHtml(
            'duration-field-input',
            'label',
            '<div class="c-field-duration">' . $f1 . $f2 . '</div>',
            'byline',
        );
        $this->assertEquals($expected, $this->render($duration));
        return $duration;
    }

    /**
     * @depends testRender
     */
    public function testRenderWithDifferentLabels($duration): void
    {
        $other_start_label = 'other startlabel';
        $other_end_label = 'other endlabel';

        $duration = $duration->withLabels($other_start_label, $other_end_label);

        $f1 = $this->getFormWrappedHtml(
            'date-time-field-input',
            $other_start_label,
            '<div class="c-input-group">
                <input id="id_1" type="date" name="name_0/start_1" class="c-field-datetime" />
            </div>
            ',
            null,
            'id_1',
            null,
            'name_0/start_1'
        );
        $f2 = $this->getFormWrappedHtml(
            'date-time-field-input',
            $other_end_label,
            '<div class="c-input-group">
                <input id="id_2" type="date" name="name_0/end_2" class="c-field-datetime" />
            </div>
            ',
            null,
            'id_2',
            null,
            'name_0/end_2'
        );

        $expected = $this->getFormWrappedHtml(
            'duration-field-input',
            'label',
            '<div class="c-field-duration">' . $f1 . $f2 . '</div>',
            'byline'
        );
        $this->assertEquals($expected, $this->render($duration));
    }

    public function testCommonRendering(): void
    {
        $duration = $this->factory->duration('label')
            ->withNameFrom($this->name_source);
        $this->testWithError($duration);
        $this->testWithNoByline($duration);
        $this->testWithRequired($duration);
        $this->testWithDisabled($duration);
        $this->testWithAdditionalOnloadCodeRendersId($duration);
    }
}
