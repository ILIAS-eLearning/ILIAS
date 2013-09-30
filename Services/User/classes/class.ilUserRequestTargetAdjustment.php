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
	 * @var ilUserRequestTargetAdjustmentCase[]
	 */
	protected $cases = array();

	/**
	 * @param ilObjUser $user
	 * @param ilCtrl    $ctrl
	 */
	public function __construct(ilObjUser $user, ilCtrl $ctrl)
	{
		$this->user = $user;
		$this->ctrl = $ctrl;

		$this->initCases();
	}

	/**
	 *
	 */
	protected function initCases()
	{
		require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceRequestTargetAdjustmentCase.php';
		require_once 'Services/User/classes/class.ilUserProfileIncompleteRequestTargetAdjustmentCase.php';
		require_once 'Services/User/classes/class.ilUserPasswordResetRequestTargetAdjustmentCase.php';

		$this->cases = array(
			new ilTermsOfServiceRequestTargetAdjustmentCase($this->user, $this->ctrl),
			new ilUserProfileIncompleteRequestTargetAdjustmentCase($this->user, $this->ctrl),
			new ilUserPasswordResetRequestTargetAdjustmentCase($this->user, $this->ctrl)
		);
	}

	/**
	 *
	 */
	protected function storeRequest()
	{
		/**
		 * @var $http ilHTTPS
		 */
		global $https;

		if(!ilSession::get('orig_request_target'))
		{
			$target_protocol = 'http';
			if($https->isDetected())
			{
				$target_protocol .= 's';
			}
			$host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'];
			$request_url = $_SERVER['REQUEST_URI']{0} == '/' ? $_SERVER['REQUEST_URI'] : '/'. $_SERVER['REQUEST_URI'];
			$url = $target_protocol . '://' . $host . $request_url;
			ilSession::set('orig_request_target', $url);
		}
	}

	/**
	 * @return boolean
	 */
	public function adjust()
	{
		if(defined('IL_CERT_SSO'))
		{
			return false;
		}
		else if(!ilContext::supportsRedirects())
		{
			return false;
		}
		else if($this->ctrl->isAsynch())
		{
			return false;
		}
		else if(in_array(basename($_SERVER['PHP_SELF']), array('logout.php')))
		{
			return false;
		}
		else if(!$this->user->getId() || $this->user->isAnonymous())
		{
			return false;
		}

		foreach($this->cases as $case)
		{
			if($case->isInFulfillment())
			{
				return false;
			}

			if($case->shouldAdjustRequest())
			{
				if($case->shouldStoreRequestTarget())
				{
					$this->storeRequest();
				}
				$case->adjust();
				return true;
			}
		}

		return false;
	}
}
