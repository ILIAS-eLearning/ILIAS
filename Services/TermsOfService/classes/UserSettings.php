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

use ILIAS\LegalDocuments\ConsumerToolbox\UserSettings as UserSettingsInterface;
use ILIAS\LegalDocuments\ConsumerToolbox\Marshal;
use ilObjUser;
use ILIAS\LegalDocuments\ConsumerToolbox\SelectSetting;
use ILIAS\LegalDocuments\ConsumerToolbox\Convert;
use ILIAS\LegalDocuments\ConsumerToolbox\Setting;
use ILIAS\Refinery\Factory as Refinery;
use DateTimeImmutable;

class UserSettings implements UserSettingsInterface
{
    public function __construct(
        private readonly ilObjUser $user,
        private readonly SelectSetting $user_pref,
        private readonly Refinery $refinery
    ) {
    }

    /**
     * @return Setting<bool>
     */
    public function withdrawalRequested(): Setting
    {
        return $this->user_pref->typed('consent_withdrawal_requested', fn(Marshal $m) => $m->boolean());
    }

    /**
     * @return Setting<?DateTimeImmutable>
     */
    public function agreeDate(): Setting
    {
        return $this->setting($this->convert());
    }

    private function setting(Convert $convert): Setting
    {
        return new Setting(
            fn() => $convert->fromString()->transform($this->user->getAgreeDate()),
            function ($value) use ($convert): void {
                $this->user->setAgreeDate($convert->toString()->transform($value));
                $this->user->update();
            }
        );
    }

    private function convert(): Convert
    {
        $custom = $this->refinery->custom()->transformation(...);
        $null_or = fn($next) => $this->refinery->byTrying([$this->refinery->null(), $next]);

        return new Convert(
            $null_or($this->refinery->to()->dateTime()),
            $null_or($custom(fn(DateTimeImmutable $d): string => $d->format('Y-m-d H:i:s')))
        );
    }
}
