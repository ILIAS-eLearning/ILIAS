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

use ILIAS\Test\Settings\MainSettings\SettingsTestBehaviour;

class SettingsTestBehaviourTest extends ilTestBaseTestCase
{
    /**
     * @dataProvider getAndWithNumberOfTriesDataProvider
     */
    public function testGetAndWithNumberOfTries(int $io): void
    {
        $Settings_test_behaviour = (new SettingsTestBehaviour(0))->withNumberOfTries($io);

        $this->assertInstanceOf(SettingsTestBehaviour::class, $Settings_test_behaviour);
        $this->assertEquals($io, $Settings_test_behaviour->getNumberOfTries());
    }

    public static function getAndWithNumberOfTriesDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1]
        ];
    }

    /**
     * @dataProvider getAndWithBlockAfterPassedEnabledDataProvider
     */
    public function testGetAndWithBlockAfterPassedEnabled(): void
    {
        $Settings_test_behaviour = (new SettingsTestBehaviour(0))->withBlockAfterPassedEnabled(true);

        $this->assertInstanceOf(SettingsTestBehaviour::class, $Settings_test_behaviour);
        $this->assertTrue($Settings_test_behaviour->getBlockAfterPassedEnabled());
    }

    public static function getAndWithBlockAfterPassedEnabledDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider getAndWithPassWaitingDataProvider
     */
    public function testGetAndWithPassWaiting(?string $io): void
    {
        $Settings_test_behaviour = (new SettingsTestBehaviour(0))->withPassWaiting($io);

        $this->assertInstanceOf(SettingsTestBehaviour::class, $Settings_test_behaviour);
        $this->assertEquals($io, $Settings_test_behaviour->getPassWaiting());
    }

    public static function getAndWithPassWaitingDataProvider(): array
    {
        return [
            [null],
            ['0:0:0']
        ];
    }

    /**
     * @dataProvider getAndWithProcessingTimeEnabledDataProvider
     */
    public function testGetAndWithProcessingTimeEnabled(bool $io): void
    {
        $Settings_test_behaviour = (new SettingsTestBehaviour(0))->withProcessingTimeEnabled($io);

        $this->assertInstanceOf(SettingsTestBehaviour::class, $Settings_test_behaviour);
        $this->assertEquals($io, $Settings_test_behaviour->getProcessingTimeEnabled());
    }

    public static function getAndWithProcessingTimeEnabledDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider getAndWithProcessingTimeDataProvider
     */
    public function testGetAndWithProcessingTime(?string $io): void
    {
        $Settings_test_behaviour = (new SettingsTestBehaviour(0))->withProcessingTime($io);

        $this->assertInstanceOf(SettingsTestBehaviour::class, $Settings_test_behaviour);
        $this->assertEquals($io, $Settings_test_behaviour->getProcessingTime());
    }

    public static function getAndWithProcessingTimeDataProvider(): array
    {
        return [
            [null],
            [''],
            ['string']
        ];
    }

    /**
     * @dataProvider getAndWithResetProcessingTimeDataProvider
     */
    public function testGetAndWithResetProcessingTime(bool $io): void
    {
        $Settings_test_behaviour = (new SettingsTestBehaviour(0))->withResetProcessingTime($io);

        $this->assertInstanceOf(SettingsTestBehaviour::class, $Settings_test_behaviour);
        $this->assertEquals($io, $Settings_test_behaviour->getResetProcessingTime());
    }

    public static function getAndWithResetProcessingTimeDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider getAndWithKioskModeDataProvider
     */
    public function testGetAndWithKioskMode(int $io): void
    {
        $Settings_test_behaviour = (new SettingsTestBehaviour(0))->withKioskMode($io);

        $this->assertInstanceOf(SettingsTestBehaviour::class, $Settings_test_behaviour);
        $this->assertEquals($io, $Settings_test_behaviour->getKioskMode());
    }

    public static function getAndWithKioskModeDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1]
        ];
    }

    // ExamIdInTestPassEnabled
    /**
     * @dataProvider getAndWithExamIdInTestPassEnabledDataProvider
     */
    public function testGetAndWithExamIdInTestPassEnabled(bool $io): void
    {
        $Settings_test_behaviour = (new SettingsTestBehaviour(0))->withExamIdInTestAttemptEnabled($io);

        $this->assertInstanceOf(SettingsTestBehaviour::class, $Settings_test_behaviour);
        $this->assertEquals($io, $Settings_test_behaviour->getExamIdInTestAttemptEnabled());
    }

    public static function getAndWithExamIdInTestPassEnabledDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
