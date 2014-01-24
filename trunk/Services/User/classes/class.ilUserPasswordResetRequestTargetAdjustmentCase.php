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
		if(!isset($_GET['baseClass']) || strtolower($_GET['baseClass']) != 'ilpersonaldesktopgui')
		{
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
		if(!$this->isInFulfillment() && ($this->user->isPasswordChangeDemanded() || $this->user->isPasswordExpired()))
		{
			return true;
		}

		return false;
	}

	/**
	 * @return void
	 */
	public function adjust()
	{
		if(isset($_GET['baseClass']) && strtolower($_GET['baseClass']) == 'ilpersonaldesktopgui')
		{
			$this->ctrl->setTargetScript('ilias.php');
			ilUtil::redirect($this->ctrl->getLinkTargetByClass(array('ilpersonaldesktopgui', 'ilpersonalsettingsgui'), 'showPassword', '', false, false));
		}
		else
		{
			ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
		}
	}
}
