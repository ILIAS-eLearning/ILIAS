<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUserRequestTargetAdjustmentCase
 */
abstract class ilUserRequestTargetAdjustmentCase
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
	 * @param ilObjUser  $user
	 * @param ilCtrl     $ctrl
	 * @param ilLanguage $lng
	 */
	final public function __construct($user, $ctrl, $lng)
	{
		$this->user   = $user;
		$this->ctrl   = $ctrl;
		$this->lng    = $lng;
	}
	/**
	 * @return mixed
	 */
	abstract public function shouldRequestTargetBeStored();

	/**
	 * @return boolean
	 */
	abstract public function shouldAdjustRequest();

	/**
	 * @return boolean
	 */
	abstract public function isInFulfillment();

	/**
	 * @return void
	 */
	abstract public function adjust();
}