<?php declare(strict_types=1);
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUserProfileIncompleteAndPasswordResetRequestTargetAdjustmentCase
 */
class ilUserPasswordResetRequestTargetAdjustmentCase extends ilUserRequestTargetAdjustmentCase
{
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
            strtolower($this->request->getQueryParams()['baseClass']) !== 'ilpersonaldesktopgui'
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
    public function shouldAdjustRequest() : bool
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
    public function adjust() : void
    {
        $this->ctrl->initBaseClass('ilpersonaldesktopgui');
        $this->ctrl->redirectByClass(
            ['ilpersonaldesktopgui', 'ilpersonalsettingsgui'],
            'showPassword'
        );
    }
}
