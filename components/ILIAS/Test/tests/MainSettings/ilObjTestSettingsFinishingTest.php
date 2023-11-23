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

class ilObjTestSettingsFinishingTest extends ilTestBaseTestCase
{
    public function testToForm(): void
    {
        $this->markTestSkipped();
    }

    public function testGetRedirectionInputs(): void
    {
        $this->markTestSkipped();
    }

    public function testGetMailNotificationInputs(): void
    {
        $this->markTestSkipped();
    }

    public function testToStorage(): void
    {
        $this->markTestSkipped();
    }

    /**
     * @dataProvider getAndWithConcludingRemarksEnabledDataProvider
     */
    public function testGetAndWithShowAnswerOverview(bool $IO): void
    {
        $ilObjTestSettingsFinishing = new ilObjTestSettingsFinishing(0);
        $ilObjTestSettingsFinishing = $ilObjTestSettingsFinishing->withShowAnswerOverview($IO);

        $this->assertInstanceOf(ilObjTestSettingsFinishing::class, $ilObjTestSettingsFinishing);
        $this->assertEquals($IO, $ilObjTestSettingsFinishing->getShowAnswerOverview());
    }

    public function getAndWithConcludingRemarksEnabledDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider getAndWithConcludingRemarksEnabledDataProvider
     */
    public function testGetAndWithConcludingRemarksEnabled(bool $IO): void
    {
        $ilObjTestSettingsFinishing = new ilObjTestSettingsFinishing(0);
        $ilObjTestSettingsFinishing = $ilObjTestSettingsFinishing->withConcludingRemarksEnabled($IO);

        $this->assertInstanceOf(ilObjTestSettingsFinishing::class, $ilObjTestSettingsFinishing);
        $this->assertEquals($IO, $ilObjTestSettingsFinishing->getConcludingRemarksEnabled());
    }

    /**
     * @dataProvider getAndWithConcludingRemarksTextDataProvider
     */
    public function testGetAndWithConcludingRemarksText(?string $IO): void
    {
        $ilObjTestSettingsFinishing = new ilObjTestSettingsFinishing(
            0,
            false,
            false,
            $IO,
        );

        $this->assertEquals($IO, $ilObjTestSettingsFinishing->getConcludingRemarksText());
    }

    public function getAndWithConcludingRemarksTextDataProvider(): array
    {
        return [
            [null],
            [''],
            ['string'],
        ];
    }

    /**
     * @dataProvider getAndWithConcludingRemarksPageIdDataProvider
     */
    public function testGetAndWithConcludingRemarksPageId(?int $IO): void
    {
        $ilObjTestSettingsFinishing = new ilObjTestSettingsFinishing(0);
        $ilObjTestSettingsFinishing = $ilObjTestSettingsFinishing->withConcludingRemarksPageId($IO);

        $this->assertInstanceOf(ilObjTestSettingsFinishing::class, $ilObjTestSettingsFinishing);
        $this->assertEquals($IO, $ilObjTestSettingsFinishing->getConcludingRemarksPageId());
    }

    public function getAndWithConcludingRemarksPageIdDataProvider(): array
    {
        return [
            [null],
            [-1],
            [0],
            [1],
        ];
    }

    /**
     * @dataProvider getAndWithRedirectionModeDataProvider
     */
    public function testGetAndWithRedirectionMode(int $IO): void
    {
        $ilObjTestSettingsFinishing = new ilObjTestSettingsFinishing(0);
        $ilObjTestSettingsFinishing = $ilObjTestSettingsFinishing->withRedirectionMode($IO);

        $this->assertInstanceOf(ilObjTestSettingsFinishing::class, $ilObjTestSettingsFinishing);
        $this->assertEquals($IO, $ilObjTestSettingsFinishing->getRedirectionMode());
    }

    public function getAndWithRedirectionModeDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1],
        ];
    }

    /**
     * @dataProvider getAndWithRedirectionUrlDataProvider
     */
    public function testGetAndWithRedirectionUrl(?string $IO): void
    {
        $ilObjTestSettingsFinishing = new ilObjTestSettingsFinishing(0);
        $ilObjTestSettingsFinishing = $ilObjTestSettingsFinishing->withRedirectionUrl($IO);

        $this->assertInstanceOf(ilObjTestSettingsFinishing::class, $ilObjTestSettingsFinishing);
        $this->assertEquals($IO, $ilObjTestSettingsFinishing->getRedirectionUrl());
    }

    public function getAndWithRedirectionUrlDataProvider(): array
    {
        return [
            [null],
            [''],
            ['string'],
        ];
    }

    /**
     * @dataProvider getAndWithMailNotificationContentTypeDataProvider
     */
    public function testGetAndWithMailNotificationContentType(int $IO): void
    {
        $ilObjTestSettingsFinishing = new ilObjTestSettingsFinishing(0);
        $ilObjTestSettingsFinishing = $ilObjTestSettingsFinishing->withMailNotificationContentType($IO);

        $this->assertInstanceOf(ilObjTestSettingsFinishing::class, $ilObjTestSettingsFinishing);
        $this->assertEquals($IO, $ilObjTestSettingsFinishing->getMailNotificationContentType());
    }

    public function getAndWithMailNotificationContentTypeDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1],
        ];
    }

    /**
     * @dataProvider getAndWithAlwaysSendMailNotificationDataProvider
     */
    public function testGetAndWithAlwaysSendMailNotification(bool $IO): void
    {
        $ilObjTestSettingsFinishing = new ilObjTestSettingsFinishing(0);
        $ilObjTestSettingsFinishing = $ilObjTestSettingsFinishing->withAlwaysSendMailNotification($IO);

        $this->assertInstanceOf(ilObjTestSettingsFinishing::class, $ilObjTestSettingsFinishing);
        $this->assertEquals($IO, $ilObjTestSettingsFinishing->getAlwaysSendMailNotification());
    }

    public function getAndWithAlwaysSendMailNotificationDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }
}