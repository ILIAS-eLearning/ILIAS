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

use ILIAS\Test\Settings\MainSettings\SettingsAccess;

class SettingsAccessTest extends ilTestBaseTestCase
{
    /**
     * @dataProvider getAndWithStartTimeEnabledDataProvider
     */
    public function testGetAndWithStartTimeEnabled(bool $io): void
    {
        $settings_access = (new SettingsAccess(0))->withStartTimeEnabled($io);

        $this->assertInstanceOf(SettingsAccess::class, $settings_access);
        $this->assertEquals($io, $settings_access->getStartTimeEnabled());
    }

    public static function getAndWithStartTimeEnabledDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider getAndWithStartTimeDataProvider
     */
    public function testGetAndWithStartTime(?DateTimeImmutable $io): void
    {
        $settings_access = (new SettingsAccess(0))->withStartTime($io);

        $this->assertInstanceOf(SettingsAccess::class, $settings_access);
        $this->assertEquals($io, $settings_access->getStartTime());
    }

    public static function getAndWithStartTimeDataProvider(): array
    {
        return [
            [new DateTimeImmutable()],
            [null]
        ];
    }

    /**
     * @dataProvider getAndWithEndTimeEnabledDataProvider
     */
    public function testGetAndWithEndTimeEnabled(bool $io): void
    {
        $settings_access = (new SettingsAccess(0))->withEndTimeEnabled($io);

        $this->assertInstanceOf(SettingsAccess::class, $settings_access);
        $this->assertEquals($io, $settings_access->getEndTimeEnabled());
    }

    public static function getAndWithEndTimeEnabledDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider getAndWithEndTimeDataProvider
     */
    public function testGetAndWithEndTime(?DateTimeImmutable $io): void
    {
        $settings_access = (new SettingsAccess(0))->withEndTime($io);

        $this->assertInstanceOf(SettingsAccess::class, $settings_access);
        $this->assertEquals($io, $settings_access->getEndTime());
    }

    public static function getAndWithEndTimeDataProvider(): array
    {
        return [
            [new DateTimeImmutable()],
            [null]
        ];
    }

    /**
     * @dataProvider getAndWithPasswordEnabledDataProvider
     */
    public function testGetAndWithPasswordEnabled(bool $io): void
    {
        $settings_access = (new SettingsAccess(0))->withPasswordEnabled($io);

        $this->assertInstanceOf(SettingsAccess::class, $settings_access);
        $this->assertEquals($io, $settings_access->getPasswordEnabled());
    }

    public static function getAndWithPasswordEnabledDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider getAndWithPasswordDataProvider
     */
    public function testGetAndWithPassword(?string $io): void
    {
        $settings_access = (new SettingsAccess(0))->withPassword($io);

        $this->assertInstanceOf(SettingsAccess::class, $settings_access);
        $this->assertEquals($io, $settings_access->getPassword());
    }

    public static function getAndWithPasswordDataProvider(): array
    {
        return [
            [null],
            [''],
            ['string']
        ];
    }

    /**
     * @dataProvider getAndWithFixedParticipantsDataProvider
     */
    public function testGetAndWithFixedParticipants(bool $io): void
    {
        $settings_access = (new SettingsAccess(0))->withFixedParticipants($io);

        $this->assertInstanceOf(SettingsAccess::class, $settings_access);
        $this->assertEquals($io, $settings_access->getFixedParticipants());
    }

    public static function getAndWithFixedParticipantsDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
