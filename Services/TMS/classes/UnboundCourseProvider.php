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
		$lng = $DIC["lng"];
		$lng->loadLanguageModule("tms");
		$user = $DIC->user();
		$object = $entity->object();

		if ($component_type === CourseInfo::class) {
			$ret = [ new CourseInfoImpl
					( $entity
					, $lng->txt("title")
					, $object->getTitle()
					, ""
					, 100
					, [CourseInfo::CONTEXT_SEARCH_SHORT_INFO,
						CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO,
						CourseInfo::CONTEXT_USER_BOOKING_SHORT_INFO
					  ]
					)
				, new CourseInfoImpl
					( $entity
					, $lng->txt("date")
					, $this->formatPeriod($object->getCourseStart(), $object->getCourseEnd())
					, ""
					, 300
					, [CourseInfo::CONTEXT_SEARCH_SHORT_INFO,
						CourseInfo::CONTEXT_SEARCH_FURTHER_INFO,
						CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO,
						CourseInfo::CONTEXT_USER_BOOKING_SHORT_INFO,
						CourseInfo::CONTEXT_USER_BOOKING_FURTHER_INFO
					  ]
					)
				, new CourseInfoImpl
					( $entity
					, ""
					, $this->formatDate($object->getCourseStart())
					, ""
					, 300
					, [CourseInfo::CONTEXT_SEARCH_SHORT_INFO]
					)
				, new CourseInfoImpl
					( $entity
					, ""
					, $this->formatDate($object->getCourseStart())." - ".$this->formatDate($object->getCourseEnd())
					, ""
					, 300
					, [CourseInfo::CONTEXT_SEARCH_FURTHER_INFO]
					)
				];

			require_once("Modules/Course/classes/class.ilCourseParticipants.php");
			require_once("Services/Membership/classes/class.ilWaitingList.php");
			if(\ilCourseParticipants::_isParticipant($object->getRefId(), $user->getId())) {
				$ret[] = new CourseInfoImpl
						( $entity
						, $lng->txt("status")
						, $lng->txt("member")
						, ""
						, 600
						, [
							CourseInfo::CONTEXT_USER_BOOKING_FURTHER_INFO
						  ]
					);
			}

			if(\ilWaitingList::_isOnList($user->getId(), $object->getId())) {
				$ret[] = new CourseInfoImpl
						( $entity
						, $lng->txt("status")
						, $lng->txt("waitinglist")
						, ""
						, 600
						, [
							CourseInfo::CONTEXT_USER_BOOKING_FURTHER_INFO
						  ]
					);
			}

			$venue_components = $this->getVenueComponents($entity, (int)$object->getId());

			return array_merge($ret, $venue_components);
		}
		throw new \InvalidArgumentException("Unexpected component type '$component_type'");
	}

	/**
	 * Form date for gui as user timezone string
	 *
	 * @param ilDateTime 	$dat
	 * @param bool 	$use_time
	 *
	 * @return string
	 */
	protected function formatDate(\ilDateTime $date) {
		global $DIC;
		$g_user = $DIC->user();
		require_once("Services/Calendar/classes/class.ilCalendarUtil.php");
		$out_format = ilCalendarUtil::getUserDateFormat($use_time, true);
		$ret = $date->get(IL_CAL_FKT_DATE, $out_format, $g_user->getTimeZone());
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
	protected function getVenueComponents(Entity $entity, $crs_id) {
		assert('is_int($crs_id)');
		$ret = array();
		if(ilPluginAdmin::isPluginActive('venues')) {
			$vplug = ilPluginAdmin::getPluginObjectById('venues');
			$txt = $vplug->txtClosure();
			list($venue_id, $city, $address) = $vplug->getVenueInfos($crs_id);

			if($city != "") {
				$ret[] = new CourseInfoImpl
				( $entity
				, ""
				, $city
				, ""
				, 400
				, [CourseInfo::CONTEXT_SEARCH_SHORT_INFO,
					CourseInfo::CONTEXT_USER_BOOKING_SHORT_INFO
				  ]
				);
			}

			if($address != "") {
				$ret[] = new CourseInfoImpl
				( $entity
				, $txt("address")
				, $address.", ".$city
				, ""
				, 350
				, [CourseInfo::CONTEXT_SEARCH_FURTHER_INFO,
					CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO,
					CourseInfo::CONTEXT_USER_BOOKING_FURTHER_INFO
				  ]
				);
			}

			$ret[] = new CourseInfoImpl
				( $entity
				, $txt("city")
				, $address
				, ""
				, 100
				, [CourseInfo::CONTEXT_SEARCH_FURTHER_INFO]
				);
		}
		return $ret;
	}
}
