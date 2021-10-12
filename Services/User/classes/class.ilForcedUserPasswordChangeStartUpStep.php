<?php declare(strict_types=1);
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Init\StartupSequence\StartUpSequenceStep;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ilForcedUserPasswordChangeStartUpStep
 */
class ilForcedUserPasswordChangeStartUpStep extends StartUpSequenceStep
{
    /** @var ilObjUser */
    private $user;
    /** @var ilCtrl */
    private $ctrl;
    /** @var ServerRequestInterface */
    private $request;

    /**
     * ilForcedUserPasswordChangeStartUpStep constructor.
     * @param ilObjUser $user
     * @param ilCtrl $ctrl
     * @param ServerRequestInterface $request
     */
    public function __construct(ilObjUser $user, ilCtrl $ctrl, ServerRequestInterface $request)
    {
        $this->user = $user;
        $this->ctrl = $ctrl;
        $this->request = $request;
    }

    /**
     * @return boolean
     */
    public function shouldStoreRequestTarget() : bool
    {
        return true;
    }

    /**
     * @return boolean
     */
    public function isInFulfillment() : bool
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

    /**
     * @return boolean
     */
    public function shouldInterceptRequest() : bool
    {
        if (ilSession::get('used_external_auth')) {
            return false;
        }

        if (!$this->isInFulfillment() && ($this->user->isPasswordChangeDemanded() || $this->user->isPasswordExpired())) {
            return true;
        }

        return false;
    }

    /**
     * @return void
     */
    public function execute() : void
    {
        $this->ctrl->redirectByClass(
            ['ildashboardgui', 'ilpersonalsettingsgui'],
            'showPassword'
        );
    }
}
