<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/User/classes/class.ilUserRequestTargetAdjustmentCase.php';

/**
 * Class ilTermsOfServiceRequestTargetAdjustmentCase
 */
class ilTermsOfServiceRequestTargetAdjustmentCase extends ilUserRequestTargetAdjustmentCase
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
		return (
			strtolower($this->ctrl->getCmdClass()) == 'ilstartupgui' &&
			strtolower($this->ctrl->getCmd()) == 'getacceptance'
		);
	}

	/**
	 * @return boolean
	 */
	public function shouldAdjustRequest()
	{
		if($this->isInFulfillment())
		{
			return false;
		}

		if(
			$this->user->hasToAcceptTermsOfService() &&
			$this->user->checkTimeLimit() &&
			$this->user->hasToAcceptTermsOfServiceInSession()
		)
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
		ilUtil::redirect('ilias.php?baseClass=ilStartUpGUI&cmdClass=ilStartupGUI&cmd=getAcceptance');
	}
}
