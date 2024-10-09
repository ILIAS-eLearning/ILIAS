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

namespace ILIAS\TermsOfService\test;

use DateTimeImmutable;
use ILIAS\Refinery\ByTrying;
use ILIAS\LegalDocuments\ConsumerToolbox\SelectSetting;
use ILIAS\LegalDocuments\ConsumerToolbox\Marshal;
use ILIAS\LegalDocuments\ConsumerToolbox\Convert;
use ILIAS\LegalDocuments\ConsumerToolbox\Setting;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\Refinery\Factory as Refinery;
use ilObjUser;
use ILIAS\TermsOfService\UserSettings;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap.php';

class UserSettingsTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(UserSettings::class, new UserSettings(
            $this->mock(ilObjUser::class),
            $this->mock(SelectSetting::class),
            $this->mock(Refinery::class)
        ));
    }

    public function testWithdrawalRequested(): void
    {
        $setting = $this->mock(Setting::class);
        $convert = $this->mock(Convert::class);

        $marshal = $this->mockMethod(Marshal::class, 'boolean', [], $convert);

        $settings = $this->mock(SelectSetting::class);
        $settings->expects(self::once())->method('typed')->willReturnCallback(function (string $key, callable $select) use ($marshal, $convert, $setting) {
            $this->assertSame('consent_withdrawal_requested', $key);
            $this->assertSame($convert, $select($marshal));
            return $setting;
        });

        $instance = new UserSettings(
            $this->mock(ilObjUser::class),
            $settings,
            $this->mock(Refinery::class)
        );

        $this->assertSame($setting, $instance->withdrawalRequested());
    }

    public function testAgreeDate(): void
    {
        $date = new DateTimeImmutable();
        $return_date = new DateTimeImmutable();

        $by_trying = $this->mock(ByTrying::class);
        $consecutive = [
            ['agree date', $return_date],
            [$date, 'another date']
        ];
        $by_trying->expects(self::exactly(2))
            ->method('transform')
            ->willReturnCallback(
                function ($in) use (&$consecutive) {
                    [$expected, $ret] = array_shift($consecutive);
                    $this->assertEquals($expected, $in);
                    return $ret;
                }
            );

        $user = $this->mock(ilObjUser::class);
        $user->expects(self::once())->method('getAgreeDate')->willReturn('agree date');
        $user->expects(self::once())->method('setAgreeDate')->with('another date');
        $user->expects(self::once())->method('update');

        $refinery = $this->mockTree(Refinery::class, ['byTrying' => $by_trying]);

        $instance = new UserSettings(
            $user,
            $this->mock(SelectSetting::class),
            $refinery
        );

        $setting = $instance->agreeDate();
        $this->assertSame($return_date, $setting->value());
        $setting->update($date);
    }
}
