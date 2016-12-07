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
		if(!ilSession::get('orig_request_target'))
		{
			//#16324 don't use the complete REQUEST_URI
			$url = substr($_SERVER['REQUEST_URI'], (strrpos($_SERVER['REQUEST_URI'], '/') +1 ));

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
			$GLOBALS['DIC']->logger()->init()->debug('CERT SSO request. No adjustment.');
			return false;
		}
		else if(!ilContext::supportsRedirects())
		{
			$GLOBALS['DIC']->logger()->init()->debug('Context does not support redirects. No adjustment.');
			return false;
		}
		else if($this->ctrl->isAsynch())
		{
			$GLOBALS['DIC']->logger()->init()->debug('Async request. No adjustment.');
			return false;
		}
		else if(in_array(basename($_SERVER['PHP_SELF']), array('logout.php')))
		{
			$GLOBALS['DIC']->logger()->init()->debug('Logout request. No adjustment.');
			return false;
		}
		else if(!$this->user->getId() || $this->user->isAnonymous())
		{
			$GLOBALS['DIC']->logger()->init()->debug('Anyonymous request. No adjustment.');
			return false;
		}

		foreach($this->cases as $case)
		{
			if($case->isInFulfillment())
			{
				$GLOBALS['DIC']->logger()->init()->debug('Case is in fullfillment:' . get_class($case));
				return false;
			}

			if($case->shouldAdjustRequest())
			{
				
				$GLOBALS['DIC']->logger()->init()->debug('Case required adjustment:' . get_class($case));
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
