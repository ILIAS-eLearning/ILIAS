<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/User/classes/class.ilUserRequestTargetAdjustmentCase.php';

/**
 * Class ilUserProfileIncompleteAndPasswordResetRequestTargetAdjustmentCase
 */
class ilUserPasswordResetRequestTargetAdjustmentCase extends ilUserRequestTargetAdjustmentCase
{
    /**
     * @return boolean
     */
    public function shouldStoreRequestTarget()
    {
        return true;
    }

    /**
     * @return boolean
     */
    public function isInFulfillment()
    {
        if (!isset($_GET['baseClass']) || strtolower($_GET['baseClass']) != 'ilpersonaldesktopgui') {
            return false;
        }

        return (
            strtolower($this->ctrl->getCmdClass()) == 'ilpersonalsettingsgui' &&
            in_array(strtolower($this->ctrl->getCmd()), array('showpassword', 'savepassword'))
        );
    }

    /**
     * @return boolean
     */
    public function shouldAdjustRequest()
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
    public function adjust()
    {
        $_GET['baseClass'] = 'ilpersonaldesktopgui';
        ilUtil::redirect($this->ctrl->getLinkTargetByClass(array('ilpersonaldesktopgui', 'ilpersonalsettingsgui'), 'showPassword', '', false, false));
    }
}
