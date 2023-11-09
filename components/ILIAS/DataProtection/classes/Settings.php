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

namespace ILIAS\DataProtection;

use DateTimeImmutable;
use ILIAS\LegalDocuments\ConsumerToolbox\Settings as SettingsInterface;
use ILIAS\LegalDocuments\ConsumerToolbox\SelectSetting;
use ILIAS\LegalDocuments\ConsumerToolbox\Marshal;
use ILIAS\LegalDocuments\ConsumerToolbox\Setting;
use ILIAS\LegalDocuments\ConsumerToolbox\Convert;

final class Settings implements SettingsInterface
{
    public function __construct(private readonly SelectSetting $select)
    {
    }

    /**
     * @return Setting<bool>
     */
    public function enabled(): Setting
    {
        return $this->select->typed('dpro_enabled', $this->boolean(...));
    }

    /**
     * @return Setting<bool>
     */
    public function validateOnLogin(): Setting
    {
        return $this->select->typed('dpro_validate_on_login', $this->boolean(...));
    }

    /**
     * @return Setting<bool>
     */
    public function deleteUserOnWithdrawal(): Setting
    {
        return $this->select->typed('dpro_withdrawal_usr_deletion', $this->boolean(...));
    }

    /**
     * @return Setting<string>
     */
    public function adminEmail(): Setting
    {
        return $this->select->typed('admin_email', fn(Marshal $m) => $m->string());
    }

    /**
     * @return Setting<string>
     */
    public function authMode(): Setting
    {
        return $this->select->typed('auth_mode', fn(Marshal $m) => $m->string());
    }

    /**
     * @return Setting<DateTimeImmutable>
     */
    public function lastResetDate(): Setting
    {
        return $this->select->typed('dpro_last_reset_date', fn(Marshal $m) => $m->dateTime());
    }

    public function noAcceptance(): Setting
    {
        return $this->select->typed('dpro_no_acceptance', $this->boolean(...));
    }

    private function boolean(Marshal $m): Convert
    {
        return $m->boolean();
    }
}
