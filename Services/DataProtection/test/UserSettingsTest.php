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

namespace ILIAS\DataProtection\test;

use ILIAS\LegalDocuments\ConsumerToolbox\Convert;
use ILIAS\LegalDocuments\ConsumerToolbox\Setting;
use ILIAS\LegalDocuments\ConsumerToolbox\Marshal;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\ConsumerToolbox\SelectSetting;
use ILIAS\DataProtection\UserSettings;
use ILIAS\Tests\Refinery\TestCase;

require_once __DIR__ . '/bootstrap.php';

class UserSettingsTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(UserSettings::class, new UserSettings($this->mock(SelectSetting::class)));
    }

    public function testWithdrawalRequested(): void
    {
        $setting = $this->mock(Setting::class);
        $convert = $this->mock(Convert::class);

        $marshal = $this->mockMethod(Marshal::class, 'boolean', [], $convert);

        $settings = $this->mock(SelectSetting::class);
        $settings->expects(self::once())->method('typed')->willReturnCallback(function (string $key, callable $select) use ($marshal, $convert, $setting) {
            $this->assertSame('dpro_withdrawal_requested', $key);
            $this->assertSame($convert, $select($marshal));
            return $setting;
        });

        $this->assertSame($setting, (new UserSettings($settings))->withdrawalRequested());
    }

    public function testAgreeDate(): void
    {
        $setting = $this->mock(Setting::class);
        $convert = $this->mock(Convert::class);

        $date = $this->mock(Convert::class);

        $marshal = $this->mockMethod(Marshal::class, 'nullable', [$date], $convert);
        $marshal->expects(self::once())->method('dateTime')->willReturn($date);

        $settings = $this->mock(SelectSetting::class);
        $settings->expects(self::once())->method('typed')->willReturnCallback(function (string $key, callable $select) use ($marshal, $convert, $setting) {
            $this->assertSame('dpro_agree_date', $key);
            $this->assertSame($convert, $select($marshal));
            return $setting;
        });

        $this->assertSame($setting, (new UserSettings($settings))->agreeDate());
    }
}
