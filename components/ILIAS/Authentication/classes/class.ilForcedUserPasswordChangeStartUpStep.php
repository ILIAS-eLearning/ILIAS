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

use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Init\StartupSequence\StartUpSequenceStep;

class ilForcedUserPasswordChangeStartUpStep extends StartUpSequenceStep
{
    private ilObjUser $user;
    private ilCtrl $ctrl;
    private ServerRequestInterface $request;

    public function __construct(
        ilObjUser $user,
        ilCtrl $ctrl,
        ServerRequestInterface $request
    ) {
        $this->user = $user;
        $this->ctrl = $ctrl;
        $this->request = $request;
    }

    public function shouldStoreRequestTarget(): bool
    {
        return true;
    }

    public function isInFulfillment(): bool
    {
        if (
            !isset($this->request->getQueryParams()['baseClass']) ||
            strtolower($this->request->getQueryParams()['baseClass']) !== strtolower(ilDashboardGUI::class)
        ) {
            return false;
        }

        return
            (
                strtolower($this->ctrl->getCmdClass()) === strtolower(ilLocalUserPasswordSettingsGUI::class)
            ) &&
            in_array(
                $this->ctrl->getCmd(),
                [
                    ilLocalUserPasswordSettingsGUI::CMD_SAVE_PASSWORD,
                    ilLocalUserPasswordSettingsGUI::CMD_SHOW_PASSWORD
                ],
                true
            )
        ;
    }

    public function shouldInterceptRequest(): bool
    {
        if (ilSession::get('used_external_auth_mode')) {
            return false;
        }

        if (!$this->isInFulfillment() && ($this->user->isPasswordChangeDemanded() || $this->user->isPasswordExpired(
        ))) {
            return true;
        }

        return false;
    }

    public function execute(): void
    {
        $this->ctrl->redirectToURL(
            $this->ctrl->getLinkTargetByClass(
                [
                    ilDashboardGUI::class,
                    ilPersonalSettingsGUI::class,
                    ilLocalUserPasswordSettingsGUI::class
                ],
                ilLocalUserPasswordSettingsGUI::CMD_SHOW_PASSWORD
            )
        );
    }
}
