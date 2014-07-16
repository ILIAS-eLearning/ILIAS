<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCourseHistorizingHelper
 * 
 * This is a MOCK, full of HokumTech predictable nonsense rocket-science.
 *
 */
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
		/* Hokum-Tech predictable nonsense rocket science:
		 *
		 * In order to have a meaningful behaviour, we need to get the same random result during
		 * subsequent calls to the method. So here's a hash over the $course and we return a substring 
		 * of the hash as a suffix to the custom id.
		 */
		$ou_suffix = substr(self::getNumericHash($course),0,3);

		return 'CustomID_' . $ou_suffix;
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
		/* Hokum-Tech predictable nonsense rocket science:
		 *
		 * In order to have a meaningful behaviour, we need to get the same random result during
		 * subsequent calls to the method. So here's a hash over the $course and we return a substring 
		 * of the hash as a suffix to the template title.
		 */
		$ou_suffix = substr(self::getNumericHash($course),1,3);

		return 'TemplateTitle_' . $ou_suffix;
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
		/* Hokum-Tech predictable nonsense rocket science:
		 *
		 * In order to have a meaningful behaviour, we need to get the same random result during
		 * subsequent calls to the method. So here's a hash over the $course and we return a substring 
		 * of the hash as a suffix to the type.
		 */
		$ou_suffix = substr(self::getNumericHash($course),2,3);

		return 'Type_' . $ou_suffix;
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
		/* Hokum-Tech predictable nonsense rocket science:
		 *
		 * In order to have a meaningful behaviour, we need to get the same random result during
		 * subsequent calls to the method. So here's a hash over the $course and we return a substring 
		 * of the hash as a suffix to the topic.
		 */
		$ou_suffix = substr(self::getNumericHash($course),3,1);

		$topics = array();
		for($i = 0; $i <= $ou_suffix; $i++)
		{
			$topics[] = 'Topic_'.substr(self::getNumericHash($course),$i, 2);
		}
		return $topics;
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
		/* Hokum-Tech predictable nonsense rocket science:
		 *
		 * In order to have a meaningful behaviour, we need to get the same random result during
		 * subsequent calls to the method. So here's a hash over the $course and we return a substring 
		 * of the hash as a input for the date.
		 */
		return new ilDate(substr(self::getNumericHash($course),1,10), IL_CAL_UNIX);
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
		/* Hokum-Tech predictable nonsense rocket science:
		 *
		 * In order to have a meaningful behaviour, we need to get the same random result during
		 * subsequent calls to the method. So here's a hash over the $course and we return a substring 
		 * of the hash as input for the date.
		 */
		return new ilDate(substr(self::getNumericHash($course),2,10), IL_CAL_UNIX);
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
		/* Hokum-Tech predictable nonsense rocket science:
		 *
		 * In order to have a meaningful behaviour, we need to get the same random result during
		 * subsequent calls to the method. So here's a hash over the $course and we return a substring 
		 * of the hash as hours.
		 */
		return (int) substr(self::getNumericHash($course),4,1);
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
		/* Hokum-Tech predictable nonsense rocket science:
		 *
		 * In order to have a meaningful behaviour, we need to get the same random result during
		 * subsequent calls to the method. So here's a hash over the $course and we return true or
		 * false based on the parity of a hashes digit.
		 */
		$numeric_hash = self::getNumericHash($course);

		if ( $numeric_hash % 2 == 0)
		{
			$date = true;
		}
		else
		{
			$date = false;
		}

		return $date;
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
		/* Hokum-Tech predictable nonsense rocket science:
		 *
		 * In order to have a meaningful behaviour, we need to get the same random result during
		 * subsequent calls to the method. So here's a hash over the $user and we return a substring 
		 * of the hash as a suffix to the units name.
		 */
		$ou_suffix = substr(self::getNumericHash($course),4,3);

		return 'Venue_' . $ou_suffix;
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
		/* Hokum-Tech predictable nonsense rocket science:
		 *
		 * In order to have a meaningful behaviour, we need to get the same random result during
		 * subsequent calls to the method. So here's a hash over the $user and we return a substring 
		 * of the hash as a suffix to the units name.
		 */
		$ou_suffix = substr(self::getNumericHash($course),5,3);

		return 'Provider_' . $ou_suffix;
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
		/* Hokum-Tech predictable nonsense rocket science:
		 *
		 * In order to have a meaningful behaviour, we need to get the same random result during
		 * subsequent calls to the method. So here's a hash over the $user and we return a substring 
		 * of the hash as a suffix to the units name.
		 */
		$ou_suffix = substr(self::getNumericHash($course),6,2);

		return (string) $ou_suffix;
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
		/* Hokum-Tech predictable nonsense rocket science:
		 *
		 * In order to have a meaningful behaviour, we need to get the same random result during
		 * subsequent calls to the method. So here's a hash over the $user and we return a substring 
		 * of the hash as a suffix to the units name.
		 */
		$ou_suffix = (int) substr(self::getNumericHash($course),4,5);

		return (float) $ou_suffix/100;
	}

	/**
	 * HokumTech Helper
	 *
	 * Returns a hash from the given integer or ilObjCourse.
	 * 
	 * @param int|ilObjCourse $course
	 *
	 * @return integer
	 */
	protected static function getNumericHash($course)
	{
		if($course instanceof ilObjCourse)
		{
			$hash = md5(serialize($course->getId()));
		}
		else
		{
			$hash = md5($course);
		}
		$numeric_hash = hexdec( $hash );

		return $numeric_hash;
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
		/* Hokum-Tech predictable nonsense rocket science:
		 *
		 * In order to have a meaningful behaviour, we need to get the same random result during
		 * subsequent calls to the method. So here's a hash over the $course and we return a substring 
		 * of the hash as a suffix to the tutor name.
		 */
		$suffix = substr(self::getNumericHash($course),4,3);

		return 'Mann, Heinz_L_' . $suffix;
	}
}