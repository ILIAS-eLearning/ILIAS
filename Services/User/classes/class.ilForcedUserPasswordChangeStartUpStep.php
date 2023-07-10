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

use ILIAS\Init\StartupSequence\StartUpSequenceStep;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ilForcedUserPasswordChangeStartUpStep
 */
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
            strtolower($this->request->getQueryParams()['baseClass']) !== 'ildashboardgui'
        ) {
            return false;
        }

        return (
            strtolower($this->ctrl->getCmdClass()) === 'ilpersonalsettingsgui' &&
            in_array(strtolower($this->ctrl->getCmd()), ['showpassword', 'savepassword'])
        );
    }

    public function shouldInterceptRequest(): bool
    {
        if (ilSession::get('used_external_auth')) {
            return false;
        }

        if (!$this->isInFulfillment() && ($this->user->isPasswordChangeDemanded() || $this->user->isPasswordExpired())) {
            return true;
        }

        return false;
    }

    public function execute(): void
    {
        $this->ctrl->redirectByClass(
            ['ildashboardgui', 'ilpersonalsettingsgui'],
            'showPassword'
        );
    }
}
