<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCourseHistorizingHelper
 * 
 * @author Maximilian Becker <mbecker@databay.de>
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @version $Id$
 */


require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

class ilCourseHistorizingHelper 
{
	#region Singleton

	/** Defunct member for singleton */
	private function __clone() {}

	/** Defunct member for singleton */
	private function __construct() {}

	/** @var ilCourseHistorizingHelper $instance */
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
	 * Returns the custom id of the given course.
	 * 
	 * @param integer|ilObjCourse $course
	 *
	 * @return string
	 */
	public static function getCustomIdOf($course)
	{
		return gevCourseUtils::getInstanceByObjOrId($course)
							 ->getCustomId();
	}

	/**
	 * Returns the template title of the given course.
	 *
	 * @param integer|ilObjCourse $course
	 *
	 * @return string
	 */
	public static function getTemplateTitleOf($course)
	{
		return gevCourseUtils::getInstanceByObjOrId($course)
							 ->getTemplateTitle();
	}

	/**
	 * Returns the type of the given course.
	 *
	 * @param integer|ilObjCourse $course
	 *
	 * @return string
	 */
	public static function getTypeOf($course)
	{
		return gevCourseUtils::getInstanceByObjOrId($course)
							 ->getType();
	}

	/**
	 * Returns the topic/s of the given course.
	 *
	 * @param integer|ilObjCourse $course
	 *
	 * @return array
	 */
	public static function getTopicOf($course)
	{
		return gevCourseUtils::getInstanceByObjOrId($course)
							 ->getTopics();
	}

	/**
	 * Returns the begin of the given course.
	 *
	 * @param integer|ilObjCourse $course
	 *
	 * @return ilDate
	 */
	public static function getBeginOf($course)
	{
		return gevCourseUtils::getInstanceByObjOrId($course)
							 ->getStartDate();
	}

	/**
	 * Returns the end of the given course.
	 *
	 * @param integer|ilObjCourse $course
	 *
	 * @return ilDate
	 */
	public static function getEndOf($course)
	{
		return gevCourseUtils::getInstanceByObjOrId($course)
							 ->getEndDate();
	}

	/**
	 * Returns the hours of the given course.
	 *
	 * @param integer|ilObjCourse $course
	 *
	 * @return integer
	 */
	public static function getHoursOf($course)
	{
		// count hours in schedule 
		return gevCourseUtils::getInstanceByObjOrId($course)
							 ->getAmountHours();
	}

	/**
	 * Returns the is_expert_course flag of the given course.
	 *
	 * @param integer|ilObjCourse $course
	 *
	 * @return boolean
	 */
	public static function isExpertCourse($course)
	{
		return gevCourseUtils::getInstanceByObjOrId($course)
							 ->getIsExpertTraining();
	}

	/**
	 * Returns the venue of the given course.
	 *
	 * @param integer|ilObjCourse $course
	 *
	 * @return string
	 */
	public static function getVenueOf($course)
	{
		return gevCourseUtils::getInstanceByObjOrId($course)
							 ->geVenueTitle();
	}

	/**
	 * Returns the provider of the given course.
	 *
	 * @param integer|ilObjCourse $course
	 *
	 * @return string
	 */
	public static function getProviderOf($course)
	{
		return gevCourseUtils::getInstanceByObjOrId($course)
							 ->geProviderTitle();
	}

	/**
	 * Returns the max credit points of the given course.
	 *
	 * @param integer|ilObjCourse $course
	 *
	 * @return string
	 */
	public static function getMaxCreditPointsOf($course)
	{
		return gevCourseUtils::getInstanceByObjOrId($course)
							 ->getCreditPoints();
	}

	/**
	 * Returns the fee of the given course.
	 *
	 * @param integer|ilObjCourse $course
	 *
	 * @return string
	 */
	public static function getFeeOf($course)
	{
		return gevCourseUtils::getInstanceByObjOrId($course)
							 ->getFee();
	}

	/**
	 * Returns the tutor of the given course.
	 *
	 * @param integer|ilObjCourse $course
	 *
	 * @return string
	 */
	public static function getTutorOf($course)
	{
		$utils = gevCourseUtils::getInstanceByObjOrId($course);

		return $utils->getMainTrainerLastname().", ". $utils->getMainTrainerFirstname();
	}
}