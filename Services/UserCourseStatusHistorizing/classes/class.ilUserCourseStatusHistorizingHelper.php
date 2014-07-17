<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUserCourseStatusHistorizingHelper
 * 
 * @author Maximilian Becker <mbecker@databay.de>
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @version $Id$
 */

require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/GEV/Utils/classes/class.gevBillingUtils.php");

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
	 * @return integer
	 */
	public static function getCreditPointsOf($user, $course)
	{
		return gevCourseUtils::getInstanceByObjOrId($course)
							 ->getCreditPointsOf(self::getId($user));
	}

	/**
	 * Returns the booking status of the given user-course-relation.
	 *
	 * @param integer|ilObjUser   $user
	 * @param integer|ilObjCourse $course
	 *
	 * @return string
	 */
	public static function getBookingStatusOf($user, $course)
	{
		return gevCourseUtils::getInstanceByObjOrId($course)
							 ->getBookingStatusLabelOf(self::getId($user));
	}

	/**
	 * Returns the participation status of the given user-course-relation.
	 *
	 * @param integer|ilObjUser   $user
	 * @param integer|ilObjCourse $course
	 *
	 * @return string
	 */
	public static function getParticipationStatusOf($user, $course)
	{
		return gevCourseUtils::getInstanceByObjOrId($course)
							 ->getParticipationStatusLabelOf(self::getId($user));
	}

	/**
	 * Returns the overnights of the given user-course-relation.
	 *
	 * @param integer|ilObjUser   $user
	 * @param integer|ilObjCourse $course
	 *
	 * @return integer
	 */
	public static function getOvernightsOf($user, $course)
	{

		$user_utils = gevUserUtils::getInstanceByObjOrId($user);
		$crs_utils = gevCourseUtils::getInstanceByObjOrId($course);
		return $user_utils->getOvernightAmountForCourse($crs_utils->getCourse());
	}

	/**
	 * Returns the function of the given user-course-relation.
	 *
	 * @param integer|ilObjUser   $user
	 * @param integer|ilObjCourse $course
	 *
	 * @return string
	 */
	public static function getFunctionOf($user, $course)
	{
		return gevUserUtils::getInstanceByObjOrId($user)
						   ->getFunctionAtCourse(self::getId($course));

	}

	/**
	 * Returns the bill id of the given user-course-relation.
	 * 
	 * Use of method "hasBillId" and definition of a meaningful "no-value" should be done here.
	 * 
	 * @param integer|ilObjUser   $user
	 * @param integer|ilObjCourse $course
	 *
	 * @return string
	 */
	public static function getBillIdOf($user, $course)
	{
		
		$bill = gevBillingUtils::getInstance()
							   ->getBillForCourseAndUser( self::getId($user)
							   							, self::getId($course)
							   							);
		if ($bill) {
			return $bill->getId();
		}
		else {
			return null;
		}
	}
	
	protected static function getId($obj) {
		if (is_int($obj) || is_numeric($obj)) {
			return (int)$obj;
		}
		else {
			return $obj->getId();
		}
	}
}