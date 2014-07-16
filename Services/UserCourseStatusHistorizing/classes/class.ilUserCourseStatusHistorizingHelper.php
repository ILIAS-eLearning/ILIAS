<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUserCourseStatusHistorizingHelper
 * 
 * This is a MOCK, full of HokumTech predictable nonsense rocket-science.
 * 
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 */
class ilUserCourseStatusHistorizingHelper 
{
	#region Singleton

	/** Defunct member for singleton */
	private function __clone() {}

	/** Defunct member for singleton */
	private function __construct() {}

	/** @var ilUserCourseStatusHistorizingHelper $instance */
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
	 * Returns the creditpoints for the given user-course-relation.
	 *
	 * @param integer|ilObjUser   $user
	 * @param integer|ilObjCourse $course
	 *
	 * @TODO: Implement "the real thing".
	 *
	 * @return integer
	 */
	public static function getCreditPointsOf($user, $course)
	{
		$credit_points = substr(self::getNumericHash($user),4,3);
		return (int)$credit_points;
	}

	/**
	 * Returns the booking status of the given user-course-relation.
	 *
	 * @param integer|ilObjUser   $user
	 * @param integer|ilObjCourse $course
	 *
	 * @TODO: Implement "the real thing".
	 *
	 * @return string
	 */
	public static function getBookingStatusOf($user, $course)
	{
		$booking_status = substr(self::getNumericHash($user),2,4);

		return 'Booking_status_' . $booking_status;
	}

	/**
	 * Returns the participation status of the given user-course-relation.
	 *
	 * @param integer|ilObjUser   $user
	 * @param integer|ilObjCourse $course
	 *
	 * @TODO: Implement "the real thing".
	 *
	 * @return string
	 */
	public static function getParticipationStatusOf($user, $course)
	{
		$participation_status = substr(self::getNumericHash($user),2,4);

		return 'Participation_status_' . $participation_status;
	}

	/**
	 * Returns the overnights of the given user-course-relation.
	 *
	 * @param integer|ilObjUser   $user
	 * @param integer|ilObjCourse $course
	 *
	 * @TODO: Implement "the real thing".
	 *
	 * @return integer
	 */
	public static function getOvernightsOf($user, $course)
	{
		$overnights = substr(self::getNumericHash($user),4,3);
		return (int)$overnights;
	}

	/**
	 * Returns the function of the given user-course-relation.
	 *
	 * @param integer|ilObjUser   $user
	 * @param integer|ilObjCourse $course
	 *
	 * @TODO: Implement "the real thing".
	 *
	 * @return string
	 */
	public static function getFunctionOf($user, $course)
	{
		$function = substr(self::getNumericHash($user),10,5);
		return (string) $function;
	}

	/**
	 * Returns the bill id of the given user-course-relation.
	 * 
	 * Use of method "hasBillId" and definition of a meaningful "no-value" should be done here.
	 * 
	 * @param integer|ilObjUser   $user
	 * @param integer|ilObjCourse $course
	 *
	 * @TODO: Implement "the real thing".
	 *
	 * @return string
	 */
	public static function getBillIdOf($user, $course)
	{
		$function = substr(self::getNumericHash($user),10,5);
		return (string) $function;
	}

	/**
	 * HokumTech Helper
	 *
	 * Returns a numeric hash from the given integer or ilObjUser.
	 *
	 * @param int|ilObjUser $user
	 *
	 * @TODO: Remove this method once production code is implemented.
	 *
	 * @return integer
	 */
	protected static function getNumericHash($user)
	{
		if($user instanceof ilObjUser)
		{
			$hash = md5(serialize($user));
		}
		else
		{
			$hash = md5($user);
		}
		$numeric_hash = hexdec( $hash );

		return $numeric_hash;
	}
}