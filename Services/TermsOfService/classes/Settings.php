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

namespace ILIAS\TermsOfService;

use ILIAS\LegalDocuments\ConsumerToolbox\Settings as SettingsInterface;
use ILIAS\LegalDocuments\ConsumerToolbox\Setting;
use ILIAS\LegalDocuments\ConsumerToolbox\Marshal;
use ILIAS\LegalDocuments\ConsumerToolbox\SelectSetting;
use ILIAS\LegalDocuments\ConsumerToolbox\Convert;

class Settings implements SettingsInterface
{
    public function __construct(private readonly SelectSetting $select)
    {
    }

    /**
     * @return Setting<bool>
     */
    public function enabled(): Setting
    {
        return $this->select->typed('tos_status', $this->boolean(...));
    }

    /**
     * @return Setting<bool>
     */
    public function validateOnLogin(): Setting
    {
        return $this->select->typed('tos_reevaluate_on_login', $this->boolean(...));
    }

    /**
     * @return Setting<bool>
     */
    public function deleteUserOnWithdrawal(): Setting
    {
        return $this->select->typed('tos_withdrawal_usr_deletion', $this->boolean(...));
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
        return $this->select->typed('tos_last_reset', fn(Marshal $m) => $m->dateTime());
    }

    private function boolean(Marshal $m): Convert
    {
        return $m->boolean();
    }
}
