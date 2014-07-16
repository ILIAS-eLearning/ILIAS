<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUserHistorizingHelper
 * 
 * This is a MOCK, full of HokumTech predictable nonsense rocket-science.
 *
 */
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
		/* Hokum-Tech predictable nonsense rocket science:
		 *
		 * In order to have a meaningful behaviour, we need to get the same random result during
		 * subsequent calls to the method. So here's a hash over the $user and we return a substring 
		 * of the hash as a suffix to the units name.
		 */
		$ou_suffix = substr(self::getNumericHash($user),4,3);

		return 'Hokum Department ' . $ou_suffix;
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
		/* Hokum-Tech predictable nonsense rocket science:
		 *
		 * In order to have a meaningful behaviour, we need to get the same random result during
		 * subsequent calls to the method. So here's a hash over the $user and we return a substring 
		 * of the hash as a suffix to the post-key.
		 */
		$pos_suffix = substr(self::getNumericHash($user),2,4);

		return 'Hokum Post Type ' . $pos_suffix;
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
		// TODO: See the renaming of the method for consistency.

		/* Hokum-Tech predictable nonsense rocket science:
		 *
		 * In order to have a meaningful behaviour, we need to get the same random result during
		 * subsequent calls to the method. So here's a hash over the $user and we return null or 
		 * or a date made up from the hash.
		 */
		$numeric_hash = self::getNumericHash($user);

		if ( $numeric_hash % 2 == 0)
		{
			$date = null;
		} 
		else 
		{
			$date = new ilDate(substr($numeric_hash,0,10), IL_CAL_UNIX);
		}

		return $date;
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
		/* Hokum-Tech predictable nonsense rocket science:
		 *
		 * In order to have a meaningful behaviour, we need to get the same random result during
		 * subsequent calls to the method. So here's a hash over the $user and we return a date made up 
		 * from the hash.
		 */
		$date = new ilDate(substr(self::getNumericHash($user),1,10), IL_CAL_UNIX);

		return $date;
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
		/* Hokum-Tech predictable nonsense rocket science:
		 *
		 * In order to have a meaningful behaviour, we need to get the same random result during
		 * subsequent calls to the method. So here's a hash over the $user and we return a substring 
		 * of the hash as a professional looking ID.
		 */
		$bwv_id = substr(self::getNumericHash($user),10,5);

		return (string) $bwv_id;
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
		// TODO: See the renaming of the method for consistency.

		/* Hokum-Tech predictable nonsense rocket science:
		 *
		 * In order to have a meaningful behaviour, we need to get the same random result during
		 * subsequent calls to the method. So here's a hash over the $user and we return a date made up 
		 * from the hash.
		 */
		$date = new ilDate(substr( self::getNumericHash($user),1,10), IL_CAL_UNIX);

		return $date;
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
		/* Hokum-Tech predictable nonsense rocket science:
		 *
		 * In order to have a meaningful behaviour, we need to get the same random result during
		 * subsequent calls to the method. So here's a hash over the $user and we return a substring 
		 * of the hash as a professional looking ID.
		 */
		$bwv_id = substr( self::getNumericHash($user),10,5);

		return (string) $bwv_id;
	}

	/**
	 * HokumTech Helper
	 *
	 * Returns a hash from the given integer or ilObjUser.
	 * 
	 * @param int|ilObjUser $user
	 *
	 * @return integer
	 */
	protected static function getNumericHash($user)
	{
		if($user instanceof ilObjUser)
		{
			$hash = md5($user->getId() + self::$variant);
		}
		else
		{
			$hash = md5($user);
		}
		$numeric_hash = hexdec( $hash );

		return $numeric_hash;
	}
}