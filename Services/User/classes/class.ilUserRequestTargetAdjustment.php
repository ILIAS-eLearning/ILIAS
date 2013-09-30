<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUserAccountMaintenanceEnforcement
 */
class ilUserRequestTargetAdjustment
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilUserRequestTargetAdjustmentCase[]
	 */
	protected $cases = array();

	/**
	 * @param ilObjUser  $user
	 * @param ilCtrl     $ctrl
	 * @param ilLanguage $lng
	 */
	public function __construct($user, $ctrl, $lng)
	{
		$this->user = $user;
		$this->ctrl = $ctrl;
		$this->lng  = $lng;

		$this->initCases();
	}

	/**
	 *
	 */
	protected function initCases()
	{
		require_once 'Services/User/classes/class.ilUserTermsOfServiceRequestTargetAdjustmentCase.php';
		require_once 'Services/User/classes/class.ilUserProfileIncompleteRequestTargetAdjustmentCase.php';
		require_once 'Services/User/classes/class.ilUserPasswordResetRequestTargetAdjustmentCase.php';

		$this->cases   = array();
		$this->cases[] = new ilUserTermsOfServiceRequestTargetAdjustmentCase($this->user, $this->ctrl, $this->lng);
		$this->cases[] = new ilUserProfileIncompleteRequestTargetAdjustmentCase($this->user, $this->ctrl, $this->lng);
		$this->cases[] = new ilUserPasswordResetRequestTargetAdjustmentCase($this->user, $this->ctrl, $this->lng);
	}

	/**
	 *
	 */
	protected function storeRequest()
	{
		/**
		 * @var $http ilHTTPS
		 */
		global $https, $ilUser;

		if(!$this->user->getPref('org_request_target'))
		{
			$target_protocol = 'http';
			if($https->isDetected())
			{
				$target_protocol .= 's';
			}
			$host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'];
			$request_url = $_SERVER['REQUEST_URI']{0} == '/' ? $_SERVER['REQUEST_URI'] : '/'. $_SERVER['REQUEST_URI'];
			$url = $target_protocol . '://' . $host . $request_url;
	
			ilObjUser::_writePref($this->user->getId(), 'org_request_target', $url);
			$this->user->writePref('org_request_target', $url);
		}
	}

	/**
	 *
	 */
	public function adjust()
	{
		if(defined('IL_CERT_SSO'))
		{
			return;
		}
		else if(!ilContext::supportsRedirects())
		{
			return;
		}
		else if($this->ctrl->isAsynch())
		{
			return;
		}
		else if(in_array(basename($_SERVER['PHP_SELF']), array('logout.php')))
		{
			return;
		}

		$in_fulfillment = false;
		foreach($this->cases as $case)
		{
			if(!$in_fulfillment && $case->isInFulfillment())
			{
				$in_fulfillment = true;
			}

			if(!$in_fulfillment && $case->shouldAdjustRequest())
			{
				/**
				 * @var $case ilUserRequestTargetAdjustmentCase
				 */
				if($case->shouldRequestTargetBeStored())
				{
					$this->storeRequest();
				}
				$case->adjust();
			}
		}
	}
}
