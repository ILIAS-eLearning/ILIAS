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

use ILIAS\LegalDocuments\ConsumerToolbox\Marshal;
use ILIAS\LegalDocuments\ConsumerToolbox\Convert;
use ILIAS\LegalDocuments\ConsumerToolbox\Setting;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\ConsumerToolbox\SelectSetting;
use ILIAS\TermsOfService\Settings;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap.php';

class SettingsTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(Settings::class, new Settings($this->mock(SelectSetting::class)));
    }

    public function testEnabled(): void
    {
        $this->assertSelect('enabled', 'tos_status', 'boolean');
    }

    public function testValidateOnLogin(): void
    {
        $this->assertSelect('validateOnLogin', 'tos_reevaluate_on_login', 'boolean');
    }

    public function testDeleteUserOnWithdrawal(): void
    {
        $this->assertSelect('deleteUserOnWithdrawal', 'tos_withdrawal_usr_deletion', 'boolean');
    }

    public function testAdminEmail(): void
    {
        $this->assertSelect('adminEmail', 'admin_email', 'string');
    }

    public function testAuthMode(): void
    {
        $this->assertSelect('authMode', 'auth_mode', 'string');
    }

    public function testLastResetDate(): void
    {
        $this->assertSelect('lastResetDate', 'tos_last_reset', 'dateTime');
    }

    private function assertSelect(string $method, string $key, string $type): void
    {
        $setting = $this->mock(Setting::class);
        $convert = $this->mock(Convert::class);

        $marshal = $this->mockMethod(Marshal::class, $type, [], $convert);

        $settings = $this->mock(SelectSetting::class);
        $settings->expects(self::once())->method('typed')->willReturnCallback(function (string $k, callable $select) use ($marshal, $convert, $setting, $key) {
            $this->assertSame($key, $k);
            $this->assertSame($convert, $select($marshal));
            return $setting;
        });

        $this->assertSame($setting, (new Settings($settings))->$method());
    }
}
