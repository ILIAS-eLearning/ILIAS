<?php

use \CaT\Ente\ILIAS\SeparatedUnboundProvider;
use \CaT\Ente\ILIAS\Entity;
use \ILIAS\TMS\CourseInfo;
use \ILIAS\TMS\CourseInfoImpl;

class UnboundCourseProvider extends SeparatedUnboundProvider {
	/**
	 * @inheritdocs
	 */
	public function componentTypes() {
		return [CourseInfo::class];
	}

	/**
	 * Build the component(s) of the given type for the given object.
	 *
	 * @param   string    $component_type
	 * @param   Entity    $provider
	 * @return  Component[]
	 */
	public function buildComponentsOf($component_type, Entity $entity) {
		global $DIC;
		$this->lng = $DIC["lng"];
		$this->lng->loadLanguageModule("tms");
		$this->lng->loadLanguageModule("crs");
		$this->user = $DIC->user();
		$object = $entity->object();

		if ($component_type === CourseInfo::class) {
			$ret = array();

			$ret[] = $this->getCourseInfoForTitle($entity, $object);
			$ret = $this->getCourseInfoForPeriod($ret, $entity, $object);
			$ret = $this->getCourseInfoForBookingStatus($ret, $entity, $object);
			$ret = $this->getCourseInfoForVenue($ret, $entity, (int)$object->getId());
			$ret = $this->getCourseInfoForTrainingProvider($ret, $entity, (int)$object->getId());
			$ret = $this->getCourseInfoForImportantInformation($ret, $entity, $object);
			$ret = $this->getCourseInfoForTutors($ret, $entity, $object);

			return $ret;
		}
		throw new \InvalidArgumentException("Unexpected component type '$component_type'");
	}

	/**
	 * Get a course info with course title
	 *
	 * @param Entity $entity
	 * @param Object 	$object
	 *
	 * @return CourseInfo
	 */
	protected function getCourseInfoForTitle(Entity $entity, $object) {
		return $this->createCourseInfoObject($entity
				, $this->lng->txt("title")
				, $object->getTitle()
				, 100
				, [CourseInfo::CONTEXT_SEARCH_SHORT_INFO,
					CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO,
					CourseInfo::CONTEXT_USER_BOOKING_SHORT_INFO
				  ]
			);
	}

	/**
	 * Get a course info with course period
	 *
	 * @param CourseInfo[]
	 * @param Entity $entity
	 * @param Object 	$object
	 *
	 * @return CourseInfo[]
	 */
	protected function getCourseInfoForPeriod(array $ret, Entity $entity, $object) {
		$crs_start = $object->getCourseStart();
		if($crs_start === null) {
			return $ret;
		}

		$time = $this->getSessionAppointments($entity, $object);
		$date = $this->formatPeriod($crs_start, $object->getCourseEnd());
		$ret[] = $this->createCourseInfoObject($entity
			, $this->lng->txt("date").":"
			, $date
			, 300
			, [CourseInfo::CONTEXT_SEARCH_SHORT_INFO,
				CourseInfo::CONTEXT_USER_BOOKING_SHORT_INFO
			  ]
		);

		$ret[] = $this->createCourseInfoObject($entity
			, $this->lng->txt("date").":"
			, $date."<br />".$time
			, 300
			, [CourseInfo::CONTEXT_SEARCH_FURTHER_INFO,
				CourseInfo::CONTEXT_USER_BOOKING_FURTHER_INFO
			  ]
		);

		$ret[] = $this->createCourseInfoObject($entity
			, $this->lng->txt("date")
			, $date."<br />".$time
			, 900
			, [CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO]
		);

		return $ret;
	}

	/**
	 * Get a course info with booking status
	 *
	 * @param CourseInfo[]
	 * @param Entity $entity
	 * @param Object 	$object
	 *
	 * @return CourseInfo[]
	 */
	protected function getCourseInfoForBookingStatus(array $ret, Entity $entity, $object) {
		require_once("Modules/Course/classes/class.ilCourseParticipants.php");
		require_once("Services/Membership/classes/class.ilWaitingList.php");
		if(\ilCourseParticipants::_isParticipant($object->getRefId(), $this->user->getId())) {
			$ret[] = $this->createCourseInfoObject($entity
					, $this->lng->txt("status")
					, $this->lng->txt("booked_as_member")
					, 600
					, [
						CourseInfo::CONTEXT_USER_BOOKING_FURTHER_INFO
					  ]
				);
		}

		if(\ilWaitingList::_isOnList($this->user->getId(), $object->getId())) {
			$ret[] = $this->createCourseInfoObject($entity
					, $this->lng->txt("status")
					, $this->lng->txt("booked_on_waitinglist")
					, 600
					, [
						CourseInfo::CONTEXT_USER_BOOKING_FURTHER_INFO
					  ]
				);
		}

			if($object->getCourseStart() !== null) {
				$ret[] = new CourseInfoImpl
					( $entity
					, $lng->txt("date").":"
					, $this->formatPeriod($object->getCourseStart(), $object->getCourseEnd())
					, ""
					, 300
					, [CourseInfo::CONTEXT_SEARCH_SHORT_INFO,
						CourseInfo::CONTEXT_SEARCH_FURTHER_INFO,
						CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO,
						CourseInfo::CONTEXT_USER_BOOKING_DETAIL_INFO
					  ]
				);

			$ret[] = $this->createCourseInfoObject($entity
					, $this->lng->txt("crs_important_info")
					, $crs_important_info
					, 700
					, [
						CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO
					  ]
				);
		}

		return $ret;
	}

	/**
	 * Get a course info with listed tutors
	 *
	 * @param CourseInfo[]
	 * @param Entity $entity
	 * @param Object 	$object
	 *
	 * @return CourseInfo[]
	 */
	protected function getCourseInfoForTutors(array $ret, Entity $entity, $object) {
		$tutor_ids = $object->getMembersObject()->getTutors();
		if(count($tutor_ids) > 0) {
			foreach ($tutor_ids as $tutor_id) {
				$tutor_names[] = \ilObjUser::_lookupFullname($tutor_id);
			}

			$tutor_names = join(", ", $tutor_names);
			$ret[] = $this->createCourseInfoObject($entity
					, $this->lng->txt("trainer")
					, $tutor_names
					, 1500
					, [
						CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO
					  ]
				);
		}

		return $ret;
	}

	/**
	 * Get a course info with session appointments
	 *
	 * @param Entity $entity
	 * @param Object 	$object
	 *
	 * @return string
	 */
	protected function getSessionAppointments(Entity $entity, $object) {
		$vals = array();

		$sessions = $this->getSessionsOfCourse($object->getRefId());
		if(count($sessions) > 0) {
			foreach ($sessions as $session) {
				$appointment 	= $session->getFirstAppointment();
				$start_time 	= $appointment->getStart()->get(IL_CAL_FKT_DATE, "H:i", "UTC");
				$end_time 		= $appointment->getEnd()->get(IL_CAL_FKT_DATE, "H:i", "UTC");
				$offset 		= $appointment->getDaysOffset();

				$vals[$offset] = $this->lng->txt("day")." ".$offset." ".$start_time." - ".$end_time;
			}

		}

		asort($vals);
		return join("<br />", $vals);
	}

	/**
	 * Find sessions underneath course 
	 *
	 * @param 	int 			$crs_ref_id
	 * @return 	ilObjSession[]
	 */
	protected function getSessionsOfCourse($crs_ref_id)
	{
		global $DIC;

		$g_tree 	= $DIC->repositoryTree();
		$ret 		= array();
		$sessions 	= $g_tree->getChildsByType($crs_ref_id, "sess");

		foreach($sessions as $session)
		{
			$ret[] = ilObjectFactory::getInstanceByRefId($session['ref_id']);
		}

		return $ret;
	}

	/**
	 * Form date.
	 *
	 * @param ilDateTime 	$dat
	 * @param bool 	$use_time
	 *
	 * @return string
	 */
	protected function formatDate(\ilDateTime $date) {
		require_once("Services/Calendar/classes/class.ilCalendarUtil.php");
		$out_format = ilCalendarUtil::getUserDateFormat($use_time, true);
		$ret = $date->get(IL_CAL_FKT_DATE, $out_format, $this->user->getTimeZone());
		if(substr($ret, -5) === ':0000') {
			$ret = substr($ret, 0, -5);
		}

		return $ret;
	}

	/**
	 * Form date period.
	 *
	 * @param ilDateTime 	$dat
	 * @param bool 	$use_time
	 *
	 * @return string
	 */
	protected function formatPeriod(\ilDateTime $date1, \ilDateTime $date2) {
		return $this->formatDate($date1)." - ".$this->formatDate($date2);
	}

	/**
	 * Checks venue plugin is aktive and returns component objects
	 *
	 * @param int 	$crs_id
	 *
	 * @return CourseInfoImpl[]
	 */
	protected function getCourseInfoForVenue($ret, Entity $entity, $crs_id) {
		assert('is_int($crs_id)');
		if(ilPluginAdmin::isPluginActive('venues')) {
			$vplug = ilPluginAdmin::getPluginObjectById('venues');
			$txt = $vplug->txtClosure();
			list($venue_id, $city, $address, $name, $postcode) = $vplug->getVenueInfos($crs_id);

			if($city != "") {
				$ret[] = $this->createCourseInfoObject($entity
				, ""
				, $city
				, 400
				, [CourseInfo::CONTEXT_SEARCH_SHORT_INFO,
					CourseInfo::CONTEXT_USER_BOOKING_SHORT_INFO
				  ]
				);
			}

			if($name != "") {
				$val = array();
				$val[] = $name;

				if($address != "") {
					$val[] = $address;
				}

				if($postcode != "" || $city != "") {
					$val[] = $postcode." ".$city;
				}

				$ret[] = $this->createCourseInfoObject($entity
					, $txt("title").":"
					, join("<br />", $val)
					, 350
					, [CourseInfo::CONTEXT_SEARCH_FURTHER_INFO,
						CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO,
						CourseInfo::CONTEXT_USER_BOOKING_FURTHER_INFO
					  ]
				);

				$ret[] = $this->createCourseInfoObject($entity
					, $txt("title")
					, join("<br />", $val)
					, 1200
					, [CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO]
				);
			}
		}
		return $ret;
	}

	/**
	 * Checks training provider plugin is activ and returns component objects
	 *
	 * @param int 	$crs_id
	 *
	 * @return CourseInfoImpl[]
	 */
	protected function getCourseInfoForTrainingProvider($ret, Entity $entity, $crs_id) {
		assert('is_int($crs_id)');
		if(ilPluginAdmin::isPluginActive('trainingprovider')) {
			$vplug = ilPluginAdmin::getPluginObjectById('trainingprovider');
			$txt = $vplug->txtClosure();
			list($provider_id, $provider) = $vplug->getProviderInfos($crs_id);

			if($provider != "") {
				$ret[] = $this->createCourseInfoObject($entity
					, $txt("title")
					, $provider
					, 1100
					, [CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO]
					);
			}
		}
		return $ret;
	}

	/**
	 * Create a course inforamtion object
	 *
	 * @param Entity 	$entity
	 * @param string 	$label
	 * @param int | array | string 	$value
	 * @param int 	$step
	 * @param string[] 	$contexte
	 *
	 * @return CourseInfoImpl
	 */
	protected function createCourseInfoObject(Entity $entity, $label, $value, $step, $contexts) {
		return new CourseInfoImpl
					( $entity
					, $label
					, $value
					, ""
					, $step
					, $contexts
				);
	}
}
