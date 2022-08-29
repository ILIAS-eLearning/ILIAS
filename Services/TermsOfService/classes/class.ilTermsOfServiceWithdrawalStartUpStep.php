<?php

declare(strict_types=1);

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

use ILIAS\DI\Container;
use ILIAS\Init\StartupSequence\StartUpSequenceStep;

/**
 * Class ilTermsOfServiceWithdrawalStartUpStep
 * @author Maximilian Becker <mbecker@databay.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceWithdrawalStartUpStep extends StartUpSequenceStep
{
    public function __construct(private Container $dic)
    {
    }

    public function shouldStoreRequestTarget(): bool
    {
        return true;
    }

    public function isInFulfillment(): bool
    {
        return (
            strtolower($this->dic->ctrl()->getCmdClass()) === strtolower(ilPersonalProfileGUI::class) && (
                strtolower($this->dic->ctrl()->getCmd()) === 'showuseragreement' ||
                strtolower($this->dic->ctrl()->getCmd()) === 'confirmwithdrawal' ||
                strtolower($this->dic->ctrl()->getCmd()) === 'showconsentwithdrawalconfirmation' ||
                strtolower($this->dic->ctrl()->getCmd()) === 'cancelwithdrawal' ||
                strtolower($this->dic->ctrl()->getCmd()) === 'withdrawacceptance' ||
                strtolower($this->dic->ctrl()->getCmd()) === 'rejectwithdrawal'
            )
        );
    }

    public function shouldInterceptRequest(): bool
    {
        if ($this->isInFulfillment()) {
            return false;
        }
        return (bool) $this->dic->user()->getPref('consent_withdrawal_requested');
    }

    public function execute(): void
    {
        $this->dic->ctrl()->redirectByClass(
            [ilDashboardGUI::class, ilPersonalProfileGUI::class],
            'showConsentWithdrawalConfirmation'
        );
    }
}
