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
 * Class ilTermsOfServiceAcceptanceStartUpStep
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAcceptanceStartUpStep extends StartUpSequenceStep
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
            strtolower($this->dic->ctrl()->getCmdClass()) === strtolower(ilStartUpGUI::class) &&
            (
                strtolower($this->dic->ctrl()->getCmd()) === 'getacceptance' ||
                strtolower($this->dic->ctrl()->getCmd()) === 'confirmacceptance' ||
                strtolower($this->dic->ctrl()->getCmd()) === 'confirmwithdrawal'
            )
        );
    }

    public function shouldInterceptRequest(): bool
    {
        if ($this->isInFulfillment()) {
            return false;
        }

        if (!$this->dic->user()->hasToAcceptTermsOfServiceInSession()) {
            return false;
        }

        if ($this->dic->user()->checkTimeLimit()) {
            if ($this->dic->user()->hasToAcceptTermsOfService()) {
                return true;
            }

            /** @var ilTermsOfServiceHelper $tosService */
            $tosService = $this->dic['tos.service'];
            if ($tosService->hasToResignAcceptance($this->dic->user(), $this->dic->logger()->tos())) {
                $tosService->resetAcceptance($this->dic->user());
                return true;
            }
        }

        return false;
    }

    public function execute(): void
    {
        $this->dic->ctrl()->redirectToURL('ilias.php?baseClass=ilStartUpGUI&cmdClass=ilStartupGUI&cmd=getAcceptance');
    }
}
