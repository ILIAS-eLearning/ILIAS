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

use ILIAS\Test\Settings\MainSettings\SettingsFinishing;

class SettingsFinishingTest extends ilTestBaseTestCase
{
    /**
     * @dataProvider getAndWithConcludingRemarksEnabledDataProvider
     */
    public function testGetAndWithShowAnswerOverview(bool $io): void
    {
        $settings_finishing = (new SettingsFinishing(0))->withShowAnswerOverview($io);

        $this->assertInstanceOf(SettingsFinishing::class, $settings_finishing);
        $this->assertEquals($io, $settings_finishing->getShowAnswerOverview());
    }

    public static function getAndWithConcludingRemarksEnabledDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider getAndWithConcludingRemarksEnabledDataProvider
     */
    public function testGetAndWithConcludingRemarksEnabled(bool $io): void
    {
        $settings_finishing = (new SettingsFinishing(0))->withConcludingRemarksEnabled($io);

        $this->assertInstanceOf(SettingsFinishing::class, $settings_finishing);
        $this->assertEquals($io, $settings_finishing->getConcludingRemarksEnabled());
    }

    /**
     * @dataProvider getAndWithConcludingRemarksTextDataProvider
     */
    public function testGetAndWithConcludingRemarksText(?string $io): void
    {
        $settings_finishing = new SettingsFinishing(
            0,
            false,
            false,
            $io
        );

        $this->assertEquals($io, $settings_finishing->getConcludingRemarksText());
    }

    public static function getAndWithConcludingRemarksTextDataProvider(): array
    {
        return [
            [null],
            [''],
            ['string']
        ];
    }

    /**
     * @dataProvider getAndWithConcludingRemarksPageIdDataProvider
     */
    public function testGetAndWithConcludingRemarksPageId(?int $io): void
    {
        $settings_finishing = (new SettingsFinishing(0))->withConcludingRemarksPageId($io);

        $this->assertInstanceOf(SettingsFinishing::class, $settings_finishing);
        $this->assertEquals($io, $settings_finishing->getConcludingRemarksPageId());
    }

    public static function getAndWithConcludingRemarksPageIdDataProvider(): array
    {
        return [
            [null],
            [-1],
            [0],
            [1]
        ];
    }

    /**
     * @dataProvider getAndWithRedirectionModeDataProvider
     */
    public function testGetAndWithRedirectionMode(int $io): void
    {
        $settings_finishing = (new SettingsFinishing(0))->withRedirectionMode($io);

        $this->assertInstanceOf(SettingsFinishing::class, $settings_finishing);
        $this->assertEquals($io, $settings_finishing->getRedirectionMode());
    }

    public static function getAndWithRedirectionModeDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1]
        ];
    }

    /**
     * @dataProvider getAndWithRedirectionUrlDataProvider
     */
    public function testGetAndWithRedirectionUrl(?string $io): void
    {
        $settings_finishing = (new SettingsFinishing(0))->withRedirectionUrl($io);

        $this->assertInstanceOf(SettingsFinishing::class, $settings_finishing);
        $this->assertEquals($io, $settings_finishing->getRedirectionUrl());
    }

    public static function getAndWithRedirectionUrlDataProvider(): array
    {
        return [
            [null],
            [''],
            ['string']
        ];
    }

    /**
     * @dataProvider getAndWithMailNotificationContentTypeDataProvider
     */
    public function testGetAndWithMailNotificationContentType(int $io): void
    {
        $settings_finishing = (new SettingsFinishing(0))->withMailNotificationContentType($io);

        $this->assertInstanceOf(SettingsFinishing::class, $settings_finishing);
        $this->assertEquals($io, $settings_finishing->getMailNotificationContentType());
    }

    public static function getAndWithMailNotificationContentTypeDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1]
        ];
    }

    /**
     * @dataProvider getAndWithAlwaysSendMailNotificationDataProvider
     */
    public function testGetAndWithAlwaysSendMailNotification(bool $io): void
    {
        $settings_finishing = (new SettingsFinishing(0))->withAlwaysSendMailNotification($io);

        $this->assertInstanceOf(SettingsFinishing::class, $settings_finishing);
        $this->assertEquals($io, $settings_finishing->getAlwaysSendMailNotification());
    }

    public static function getAndWithAlwaysSendMailNotificationDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
