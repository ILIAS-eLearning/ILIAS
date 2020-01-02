<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/User/classes/class.ilUserRequestTargetAdjustmentCase.php';

/**
 * Class ilUserProfileIncompleteRequestTargetAdjustmentCase
 */
class ilUserProfileIncompleteRequestTargetAdjustmentCase extends ilUserRequestTargetAdjustmentCase
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
            strtolower($this->ctrl->getCmdClass()) == 'ilpersonalprofilegui' &&
            in_array(strtolower($this->ctrl->getCmd()), array('savepersonaldata', 'showpersonaldata', 'showprofile'))
        );
    }

    /**
     * @return boolean
     */
    public function shouldAdjustRequest()
    {
        if (!$this->isInFulfillment() && $this->user->getProfileIncomplete()) {
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
        // sm: directly redirect to personal desktop -> personal profile
        $this->ctrl->setTargetScript('ilias.php');
        ilUtil::redirect($this->ctrl->getLinkTargetByClass(array('ilpersonaldesktopgui', 'ilpersonalprofilegui'), 'showPersonalData', '', false, false));
    }
}
