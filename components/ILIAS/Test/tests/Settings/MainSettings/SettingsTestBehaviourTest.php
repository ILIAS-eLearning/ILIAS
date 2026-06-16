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
    #[\PHPUnit\Framework\Attributes\DataProvider('getAndWithNumberOfTriesDataProvider')]
    public function testGetAndWithNumberOfTries(int $io): void
    {
        $Settings_test_behaviour = (new SettingsTestBehaviour())->withNumberOfTries($io);

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

    public function testGetAndWithBlockAfterPassedEnabled(): void
    {
        $Settings_test_behaviour = (new SettingsTestBehaviour())->withBlockAfterPassedEnabled(true);

        $this->assertInstanceOf(SettingsTestBehaviour::class, $Settings_test_behaviour);
        $this->assertTrue($Settings_test_behaviour->getBlockAfterPassedEnabled());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getAndWithPassWaitingDataProvider')]
    public function testGetAndWithPassWaiting(?string $io): void
    {
        $Settings_test_behaviour = (new SettingsTestBehaviour())->withPassWaiting($io);

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

    #[\PHPUnit\Framework\Attributes\DataProvider('getAndWithProcessingTimeEnabledDataProvider')]
    public function testGetAndWithProcessingTimeEnabled(bool $io): void
    {
        $Settings_test_behaviour = (new SettingsTestBehaviour())->withProcessingTimeEnabled($io);

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

    #[\PHPUnit\Framework\Attributes\DataProvider('getAndWithProcessingTimeDataProvider')]
    public function testGetAndWithProcessingTime(?string $io): void
    {
        $Settings_test_behaviour = (new SettingsTestBehaviour())->withProcessingTime($io);

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

    #[\PHPUnit\Framework\Attributes\DataProvider('getAndWithResetProcessingTimeDataProvider')]
    public function testGetAndWithResetProcessingTime(bool $io): void
    {
        $Settings_test_behaviour = (new SettingsTestBehaviour())->withResetProcessingTime($io);

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

    #[\PHPUnit\Framework\Attributes\DataProvider('getAndWithKioskModeDataProvider')]
    public function testGetAndWithKioskMode(int $io): void
    {
        $Settings_test_behaviour = (new SettingsTestBehaviour())->withKioskMode($io);

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
    #[\PHPUnit\Framework\Attributes\DataProvider('getAndWithExamIdInTestPassEnabledDataProvider')]
    public function testGetAndWithExamIdInTestPassEnabled(bool $io): void
    {
        $Settings_test_behaviour = (new SettingsTestBehaviour())->withExamIdInTestAttemptEnabled($io);

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
