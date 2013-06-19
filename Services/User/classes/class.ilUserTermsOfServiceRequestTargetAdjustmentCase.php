<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/User/classes/class.ilUserRequestTargetAdjustmentCase.php';

/**
 * Class ilUserTermsOfServiceRequestTargetAdjustmentCase
 */
class ilUserTermsOfServiceRequestTargetAdjustmentCase extends ilUserRequestTargetAdjustmentCase
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

		if(strtolower($this->ctrl->getCmdClass()) == 'ilstartupgui' && strtolower($this->ctrl->getCmd()) == 'getacceptance')
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
		return (
			strtolower($this->ctrl->getCmdClass()) == 'ilstartupgui' &&
			strtolower($this->ctrl->getCmd()) == 'getacceptance'
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

		if($this->isInFulfillment())
		{
			return false;
		}

		if(!$this->user->hasAcceptedUserAgreement() && $this->user->checkTimeLimit())
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
		$url = 'ilias.php?baseClass=ilStartUpGUI&cmdClass=ilStartupGUI&cmd=getAcceptance';
		if(isset($_GET['target']))
		{
			$url .= '&target=' . $_GET['target'];
		}
		ilUtil::redirect($url);
	}
}