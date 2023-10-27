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
use ILIAS\LegalDocuments\ConsumerToolbox\UserSettings as UserSettingsInterface;
use ILIAS\LegalDocuments\ConsumerToolbox\SelectSetting;
use ILIAS\LegalDocuments\ConsumerToolbox\Setting;
use ILIAS\LegalDocuments\ConsumerToolbox\Marshal;

class UserSettings implements UserSettingsInterface
{
    public function __construct(private readonly SelectSetting $user_pref)
    {
    }

    /**
     * @return Setting<bool>
     */
    public function withdrawalRequested(): Setting
    {
        return $this->user_pref->typed('dpro_withdrawal_requested', fn(Marshal $m) => $m->boolean());
    }

    /**
     * @return Setting<?DateTimeImmutable>
     */
    public function agreeDate(): Setting
    {
        return $this->user_pref->typed('dpro_agree_date', fn(Marshal $m) => $m->nullable($m->dateTime()));
    }
}
