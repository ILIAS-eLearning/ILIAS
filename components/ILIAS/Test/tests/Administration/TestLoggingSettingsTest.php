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

namespace Administration;

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Test\Administration\TestLoggingSettings;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Component\Input\Field\Checkbox;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\Factory;
use ilLanguage;
use ilTestBaseTestCase;
use PHPUnit\Framework\MockObject\Exception;
use ReflectionException;

class TestLoggingSettingsTest extends ilTestBaseTestCase
{
    /**
     * @throws ReflectionException|Exception
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf(TestLoggingSettings::class, $this->createInstanceOf(TestLoggingSettings::class));
    }

    /**
     * @dataProvider toFormDataProvider
     * @throws \Exception|Exception
     */
    public function testToForm(array $input): void
    {
        $test_logging_settings = new TestLoggingSettings($input['logging'], $input['ip_logging']);

        $form_input = $this->createMock(FormInput::class);
        $form_input
            ->expects($this->once())
            ->method('withValue');

        $checkbox = $this->createMock(Checkbox::class);
        $checkbox
            ->expects($this->once())
            ->method('withByline')
            ->willReturn($form_input);
        $checkbox
            ->expects($this->once())
            ->method('withValue');

        $section = $this->createMock(Section::class);
        $section
            ->expects($this->once())
            ->method('withAdditionalTransformation')
            ->willReturn($this->createMock(Section::class));

        $field_factory = $this->createMock(FieldFactory::class);
        $field_factory
            ->expects($this->exactly(2))
            ->method('checkbox')
            ->willReturn($checkbox);
        $field_factory
            ->expects($this->once())
            ->method('section')
            ->willReturn($section);

        $input_factory = $this->createMock(InputFactory::class);
        $input_factory
            ->expects($this->exactly(3))
            ->method('field')
            ->willReturn($field_factory);

        $ui_factory = $this->createMock(Factory::class);
        $ui_factory
                ->expects($this->exactly(3))
                ->method('input')
                ->willReturn($input_factory);
        $refinery = $this->createMock(Refinery::class);
        $il_language = $this->createMock(ilLanguage::class);

        $actual = $test_logging_settings->toForm($ui_factory, $refinery, $il_language);

        $this->assertCount(1, $actual);
        $this->assertInstanceOf(Section::class, $actual['logging']);
    }

    public static function toFormDataProvider(): array
    {
        return [
            'true_true' => [[
                'logging' => true,
                'ip_logging' => true
            ]],
            'true_false' => [[
                'logging' => true,
                'ip_logging' => false
            ]],
            'false_true' => [[
                'logging' => false,
                'ip_logging' => true
            ]],
            'false_false' => [[
                'logging' => false,
                'ip_logging' => false
            ]]
        ];
    }

    /**
     * @dataProvider isAndWithLoggingEnabledDataProvider
     */
    public function testIsAndWithLoggingEnabled(bool $IO): void
    {
        $test_logging_settings = new TestLoggingSettings();

        $this->assertFalse($test_logging_settings->isLoggingEnabled());
        $this->assertInstanceOf(TestLoggingSettings::class, $actual = $test_logging_settings->withLoggingEnabled($IO));
        $this->assertEquals($IO, $actual->isLoggingEnabled());
    }

    public static function isAndWithLoggingEnabledDataProvider(): array
    {
        return [
            'true' => [true],
            'false' => [false]
        ];
    }

    /**
     * @dataProvider isAndWithIPLoggingEnabledDataProvider
     */
    public function testIsAndWithIPLoggingEnabled(bool $IO): void
    {
        $test_logging_settings = new TestLoggingSettings();

        $this->assertTrue($test_logging_settings->isIPLoggingEnabled());
        $this->assertInstanceOf(TestLoggingSettings::class, $actual = $test_logging_settings->withIPLoggingEnabled($IO));
        $this->assertEquals($IO, $actual->isIPLoggingEnabled());
    }

    public static function isAndWithIPLoggingEnabledDataProvider(): array
    {
        return [
            'true' => [true],
            'false' => [false]
        ];
    }
}
