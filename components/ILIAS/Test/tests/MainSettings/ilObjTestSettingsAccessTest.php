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

class ilObjTestSettingsAccessTest extends ilTestBaseTestCase
{
    /**
     * @dataProvider getAndWithStartTimeEnabledDataProvider
     */
    public function testGetAndWithStartTimeEnabled(bool $IO): void
    {
        $ilObjTestSettingAccess = new ilObjTestSettingsAccess(0);
        $ilObjTestSettingAccess = $ilObjTestSettingAccess->withStartTimeEnabled($IO);

        $this->assertInstanceOf(ilObjTestSettingsAccess::class, $ilObjTestSettingAccess);
        $this->assertEquals($IO, $ilObjTestSettingAccess->getStartTimeEnabled());
    }

    public function getAndWithStartTimeEnabledDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider getAndWithStartTimeDataProvider
     */
    public function testGetAndWithStartTime(?DateTimeImmutable $IO): void
    {
        $ilObjTestSettingAccess = new ilObjTestSettingsAccess(0);
        $ilObjTestSettingAccess = $ilObjTestSettingAccess->withStartTime($IO);

        $this->assertInstanceOf(ilObjTestSettingsAccess::class, $ilObjTestSettingAccess);
        $this->assertEquals($IO, $ilObjTestSettingAccess->getStartTime());
    }

    public function getAndWithStartTimeDataProvider(): array
    {
        return [
            [new DateTimeImmutable()],
            [null],
        ];
    }

    /**
     * @dataProvider getAndWithEndTimeEnabledDataProvider
     */
    public function testGetAndWithEndTimeEnabled(bool $IO): void
    {
        $ilObjTestSettingAccess = new ilObjTestSettingsAccess(0);
        $ilObjTestSettingAccess = $ilObjTestSettingAccess->withEndTimeEnabled($IO);

        $this->assertInstanceOf(ilObjTestSettingsAccess::class, $ilObjTestSettingAccess);
        $this->assertEquals($IO, $ilObjTestSettingAccess->getEndTimeEnabled());
    }

    public function getAndWithEndTimeEnabledDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider getAndWithEndTimeDataProvider
     */
    public function testGetAndWithEndTime(?DateTimeImmutable $IO): void
    {
        $ilObjTestSettingAccess = new ilObjTestSettingsAccess(0);
        $ilObjTestSettingAccess = $ilObjTestSettingAccess->withEndTime($IO);

        $this->assertInstanceOf(ilObjTestSettingsAccess::class, $ilObjTestSettingAccess);
        $this->assertEquals($IO, $ilObjTestSettingAccess->getEndTime());
    }

    public function getAndWithEndTimeDataProvider(): array
    {
        return [
            [new DateTimeImmutable()],
            [null],
        ];
    }

    /**
     * @dataProvider getAndWithPasswordEnabledDataProvider
     */
    public function testGetAndWithPasswordEnabled(bool $IO): void
    {
        $ilObjTestSettingAccess = new ilObjTestSettingsAccess(0);
        $ilObjTestSettingAccess = $ilObjTestSettingAccess->withPasswordEnabled($IO);

        $this->assertInstanceOf(ilObjTestSettingsAccess::class, $ilObjTestSettingAccess);
        $this->assertEquals($IO, $ilObjTestSettingAccess->getPasswordEnabled());
    }

    public function getAndWithPasswordEnabledDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider getAndWithPasswordDataProvider
     */
    public function testGetAndWithPassword(?string $IO): void
    {
        $ilObjTestSettingAccess = new ilObjTestSettingsAccess(0);
        $ilObjTestSettingAccess = $ilObjTestSettingAccess->withPassword($IO);

        $this->assertInstanceOf(ilObjTestSettingsAccess::class, $ilObjTestSettingAccess);
        $this->assertEquals($IO, $ilObjTestSettingAccess->getPassword());
    }

    public function getAndWithPasswordDataProvider(): array
    {
        return [
            [null],
            [''],
            ['string'],
        ];
    }

    /**
     * @dataProvider getAndWithFixedParticipantsDataProvider
     */
    public function testGetAndWithFixedParticipants(bool $IO): void
    {
        $ilObjTestSettingAccess = new ilObjTestSettingsAccess(0);
        $ilObjTestSettingAccess = $ilObjTestSettingAccess->withFixedParticipants($IO);

        $this->assertInstanceOf(ilObjTestSettingsAccess::class, $ilObjTestSettingAccess);
        $this->assertEquals($IO, $ilObjTestSettingAccess->getFixedParticipants());
    }

    public function getAndWithFixedParticipantsDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }
}