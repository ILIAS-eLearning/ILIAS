<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUserHistorizingHelper
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @version $Id$
 */

require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

class ilUserHistorizingHelper 
{
	/** @var int $variant Used to control predictable nonsense hash. Change to get alternative data for historizing */
	protected static $variant = 1;
	
	#region Singleton

	/** Defunct member for singleton */
	private function __clone() {}

	/** Defunct member for singleton */
	private function __construct() {}

	/** @var ilUserHistorizingHelper $instance */
	private static $instance;

	/**
	 * Singleton accessor
	 * 
	 * @static
	 * 
	 * @return ilUserHistorizingHelper
	 */
	public static function getInstance()
	{
		if(!self::$instance)
		{
			self::$instance = new self;
		}

		return self::$instance;
	}

	#endregion

	/**
	 * Returns the org-unit of the given user.
	 * 
	 * @param integer|ilObjUser $user
	 *
	 * @return string
	 */
	public static function getOrgUnitOf($user)
	{
		return gevUserUtils::getInstanceByObjOrId($user)->getOrgUnitTitle();
	}

	/**
	 * Returns the position key of the given user.
	 *
	 * @param integer|ilObjUser $user
	 *
	 * @return string
	 */
	public static function getPositionKeyOf($user)
	{
		return gevUserUtils::getInstanceByObjOrId($user)->getAgentKey();
	}

	/**
	 * Returns the exit date of the given user.
	 *
	 * @param integer|ilObjUser $user
	 *
	 * @return ilDate|null
	 */
	public static function getExitDateOf($user)
	{
		return gevUserUtils::getInstanceByObjOrId($user)->getExitDate();
	}

	/**
	 * Returns the entry date of the given user.
	 *
	 * @param integer|ilObjUser $user
	 *
	 * @return ilDate
	 */
	public static function getEntryDateOf($user)
	{
		return gevUserUtils::getInstanceByObjOrId($user)->getEntryDate();
	}

	/**
	 * Returns the BWV-ID of the given user.
	 *
	 * @param integer|ilObjUser $user
	 *
	 * @return string
	 */
	public static function getBWVIdOf($user)
	{
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		//return gevUserUtils::getInstanceByObjOrId($user)->getWBDBWVId();
	}

	/**
	 * Returns the entry date of the given user.
	 *
	 * @param integer|ilObjUser $user
	 *
	 * @return ilDate
	 */
	public static function getBeginOfCertificationPeriodOf($user)
	{
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		return gevUserUtils::getInstanceByObjOrId($user)->getWBDFirstCertificationPeriodBegin();
	}

	/**
	 * Returns the OKZ of the given user.
	 *
	 * @param integer|ilObjUser $user
	 *
	 * @return string
	 */
	public static function getOKZOf($user)
	{
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		return gevUserUtils::getInstanceByObjOrId($user)->getWBDOKZ();
	}

	/**
	 * Returns the Adress-data of the given user.
	 *
	 * @param integer|ilObjUser $user
	 *
	 * @return array
	 */
	public static function getAddressDataOf($user)
	{
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$uutils = gevUserUtils::getInstanceByObjOrId($user);
		//$uutils = gevUserUtils::getInstance($user->user_id);
		$ret = array(
			'street'			=> $uutils->getPrivateStreet(),
			'zipcode'			=> $uutils->getPrivateZipcode(),
			'city'				=> $uutils->getPrivateCity(),
			'phone_nr'			=> $uutils->getUser()->getPhoneOffice(),
			'mobile_phone_nr'	=> $uutils->getPrivatePhone()
		);
		return $ret;
	}

	/**
	 * Returns the email of the given user.
	 *
	 * @param integer|ilObjUser $user
	 *
	 * @return string
	 */
	public static function getEMailOf($user)
	{
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		return gevUserUtils::getInstanceByObjOrId($user)->getEMail();
	}
}