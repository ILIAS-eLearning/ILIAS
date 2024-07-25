<?php

namespace Administration;

use ILIAS\Test\Administration\TestLoggingSettings;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Field\Checkbox;
use ILIAS\UI\Factory;
use ILIAS\UI\Component\Input\Field\Section;
use ilTestBaseTestCase;

use function PHPUnit\Framework\once;

class TestLoggingSettingsTest extends ilTestBaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_refinery();
        $this->addGlobal_lng();

    }

    /**
     * @dataProvider provideLoggingSettings
     */
    public function test_toForm(TestLoggingSettings $testLoggingSettings, bool $logging, bool $IPLogging): void
    {
        global $DIC;
        $formInput = $this->createMock(FormInput::class);
        $formInput->expects($this->once())->method("withValue");
        $checkbox = $this->createMock(Checkbox::class);
        $checkbox->expects($this->once())->method("withByline")->willReturn($formInput);
        $checkbox->expects($this->once())->method("withValue");
        $erg = $this->createMock(Section::class);
        $section = $this->createMock(Section::class);
        $section->expects($this->once())->method("withAdditionalTransformation")->willReturn($erg);
        $fieldFactory = $this->createMock(\ILIAS\UI\Component\Input\Field\Factory::class);
        $fieldFactory->expects($this->exactly(2))->method("checkbox")->willReturn($checkbox);
        $fieldFactory->expects($this->once())->method("section")->willReturn($section);
        $inputFactory = $this->createMock(\ILIAS\UI\Component\Input\Factory::class);
        $inputFactory->expects($this->exactly(3))->method("field")->willReturn($fieldFactory);
        $uiFactory = $this->createMock(Factory::class);
        $uiFactory->expects($this->exactly(3))->method("input")->willReturn($inputFactory);

        $toForm = $testLoggingSettings->toForm($uiFactory, $DIC->refinery(), $DIC->language());
        $this->assertCount(1, $toForm);
        $this->assertInstanceOf(Section::class, $toForm["logging"]);
    }



    /**
     * @dataProvider provideLoggingSettings
     */
    public function test_Getter($testLoggingSettings, $logging, $IPLogging): void
    {
        $this->assertEquals($logging, $testLoggingSettings->isLoggingEnabled());
        $this->assertEquals($IPLogging, $testLoggingSettings->isIPLoggingEnabled(), );
    }

    /**
     * @dataProvider provideLoggingSettingsAndNewValue
     */
    public function test_withLoggingEnabled($testLoggingSettings, $newLogging): void
    {
        $newSettings = $testLoggingSettings->withLoggingEnabled($newLogging);
        $this->assertEquals($newLogging, $newSettings->isLoggingEnabled());
        $this->assertEquals($testLoggingSettings->isIPLoggingEnabled(), $newSettings->isIPLoggingEnabled());
    }

    /**
     * @dataProvider provideLoggingSettingsAndNewValue
     */
    public function test_withIPLoggingEnabled($testLoggingSettings, $newLogging): void
    {
        $newSettings = $testLoggingSettings->withIPLoggingEnabled($newLogging);
        $this->assertEquals($newLogging, $newSettings->isIPLoggingEnabled());
        $this->assertEquals($testLoggingSettings->isLoggingEnabled(), $newSettings->isLoggingEnabled());
    }

    public static function provideLoggingSettings(): array
    {
        return [
            "dataset 1: both enabled" => [
                "testLoggingSettings" => new TestLoggingSettings(true, true),
                "logging" => true,
                "IPLogging" => true
            ],
            "dataset 2: only logging enabled " => [
                "testLoggingSettings" => new TestLoggingSettings(true, false),
                "logging" => true,
                "IPLogging" => false
            ],
            "dataset 3: only ip logging enabled" => [
                "testLoggingSettings" => new TestLoggingSettings(false, true),
                "logging" => false,
                "IPLogging" => true
            ],
            "dataset 4: both disabled" => [
                "testLoggingSettings" => new TestLoggingSettings(false, false),
                "logging" => false,
                "IPLogging" => false
            ]
        ];
    }

    public static function provideLoggingSettingsAndNewValue(): array
    {
        return [
            "dataset 1: both enabled" => [
                "testLoggingSettings" => new TestLoggingSettings(true, true),
                "newLogging" => true,
            ],
            "dataset 2: only logging enabled " => [
                "testLoggingSettings" => new TestLoggingSettings(true, false),
                "newLogging" => false,
            ],
            "dataset 3: only ip logging enabled" => [
                "testLoggingSettings" => new TestLoggingSettings(false, true),
                "newLogging" => false,
            ],
            "dataset 4: both disabled" => [
                "testLoggingSettings" => new TestLoggingSettings(false, false),
                "newLogging" => false,
            ]
        ];
    }
}
