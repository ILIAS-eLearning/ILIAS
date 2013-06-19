<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/User/classes/class.ilUserRequestTargetAdjustmentCase.php';

/**
 * Class ilUserProfileIncompleteAndPasswordResetRequestTargetAdjustmentCase
 */
class ilUserPasswordResetRequestTargetAdjustmentCase extends ilUserRequestTargetAdjustmentCase
{
	/**
	 * @return mixed
	 */
	public function shouldRequestTargetBeStored()
	{
		if(!$this->user->getId() || $this->user->isAnonymous())
		{
			return false;
		}

		if(strtolower($this->ctrl->getCmdClass()) == 'ilpersonalsettingsgui')
		{
			return false;
		}

		return true;
	}

	/**
	 * @return bool
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
	 * @return void
	 */
	public function shouldAdjustRequest()
	{
		if(!$this->user->getId() || $this->user->isAnonymous())
		{
			return false;
		}

		if(defined('IL_CERT_SSO') || !ilContext::supportsRedirects())
		{
			return false;
		}

		if(($this->user->isPasswordChangeDemanded() || $this->user->isPasswordExpired()) && !$this->isInFulfillment())
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