<?php

/**
 * cat-tms-patch start
 */
require_once("Services/Component/classes/class.ilPluginAdmin.php");
require_once("Services/TMS/TrainingSearch/classes/class.ilTrainingSearchGUI.php");
require_once("Services/TMS/TrainingSearch/classes/TrainingSearchDB.php");
require_once("Services/TMS/TrainingSearch/classes/Helper.php");

class ilTrainingSearchDB implements TrainingSearchDB {
	public function __construct(ilBookableFilter $filter, Helper $helper) {
		global $DIC;

		$this->g_objDefinition = $DIC["objDefinition"];
		$this->g_db = $DIC->database();
		$this->g_tree = $DIC->repositoryTree();
		$this->g_access = $DIC->access();

		$this->filter = $filter;
		$this->helper = $helper;
	}

	/**
	 * @inheritdoc
	 */
	public function getBookableTrainingsFor($user_id, array $filter) {
		$crs_infos = array();

		if(ilPluginAdmin::isPluginActive('xccl')) {
			$crss = $this->getAllCourses($user_id);
			$crss = $this->filterCoursesUserIsBookedTo($user_id, $crss);
			$crss = $this->filterCoursesUserHasNoPermissionsTo($user_id, $crss);
			$crss = $this->addCourseClassification($crss);
			$crss = $this->createBookableCourseByFilter($crss, $filter);
		}

		return $crss;
	}

	/**
	 * @inheritdoc
	 */
	public function getBookableCourse($ref_id,
				$crs_title,
				$type,
				ilDateTime $start_date = null,
				$bookings_available,
				array $target_group,
				$goals,
				array $topics,
				ilDateTime $end_date = null,
				$city,
				$address,
				$costs = "KOSTEN"
	) {
		require_once("Services/TMS/TrainingSearch/classes/BookableCourse.php");
		return new BookableCourse(
				$ref_id,
				$crs_title,
				$type,
				$start_date,
				(string)$bookings_available,
				$target_group,
				$goals,
				$topics,
				$end_date,
				$city,
				$address,
				$costs
			);
	}

	/**
	 * Get all courses
	 *
	 * @param int 	$user_id
	 *
	 * @return array<ilObjCourse>
	 */
	protected function getAllCourses($user_id) {
		$query = "SELECT DISTINCT object_reference.ref_id FROM object_data".PHP_EOL
				." LEFT JOIN object_reference ON object_data.obj_id = object_reference.obj_id".PHP_EOL
				." WHERE object_data.type = 'crs'".PHP_EOL
				."     AND object_reference.deleted IS NULL";

		
		$res = $this->g_db->query($query);
		$ret = array();
		while($row = $this->g_db->fetchAssoc($res)) {
			$crs = ilObjectFactory::getInstanceByRefId($row["ref_id"]);
			$ret[] = array("crs" => $crs);
		}

		return $ret;
	}

	/**
	 * Filter courses from pool user is booked to
	 *
	 * @param int 	$user_id
	 * @param array<ilObjCourse> 	$crss
	 *
	 * @return array<ilObjCourse>
	 */
	protected function filterCoursesUserIsBookedTo($user_id, array $crss) {
		return array_filter($crss, function ($value) use ($user_id) {
			$crs = $value["crs"];

			if($this->userIsOnCourse($user_id, $crs->getRefId(), $crs->getId())) {
				return false;
			}

			return true;
		});
	}

	/**
	 * Check user is booked on course or waiting list
	 *
	 * @param int 	$user_id
	 * @param int 	$crs_ref_id
	 * @param int 	$crs_id
	 *
	 * @return bool
	 */
	protected function userIsOnCourse($user_id, $crs_ref_id, $crs_id) {
		require_once("Modules/Course/classes/class.ilCourseParticipants.php");
		require_once("Services/Membership/classes/class.ilWaitingList.php");
		if(\ilCourseParticipants::_isParticipant($crs_ref_id, $user_id)
			|| \ilWaitingList::_isOnList($user_id, $crs_id)
		) {
			return true;
		}

		return false;
	}

	/**
	 * Filter courses user has no permissions to
	 *
	 * @param int 	$user_id
	 * @param array<ilObjCourse> 	$crss
	 *
	 * @return array<ilObjCourse>
	 */
	protected function filterCoursesUserHasNoPermissionsTo($user_id, array $crss) {
		return array_filter($crss, function($value) use ($user_id) {
			$crs = $value["crs"];

			if(!$this->userHasPermissions($user_id, $crs->getRefId())) {
				return false;
			}

			return true;
		});
	}

	/**
	 * Check user has needed permissions to view the course
	 *
	 * @param int 	$user_id
	 * @param int 	$crs_ref_id
	 *
	 * @return bool
	 */
	protected function userHasPermissions($user_id, $crs_ref_id) {
		if($this->g_access->checkAccess("visible", "", $crs_ref_id)
			&& $this->g_access->checkAccess("read", "", $crs_ref_id)
		) {
			return true;
		}

		return false;
	}

	/**
	 * Add first course classification of course
	 *
	 * @param array<int, ilObjCourse | ilObjBookingModalities[]>
	 *
	 * @return array<int, ilObjCourse | ilObjBookingModalities[] | ilObjCourseClassification>
	 */
	protected function addCourseClassification($crs_infos) {
		foreach ($crs_infos as $key => &$crs_info) {
			$crs_info["xccl"] = $this->getFirstChildOfByType($crs_info["crs"]->getRefId(), "xccl");
		}

		return $crs_infos;
	}

	/**
	 * Get first child by type recursive
	 *
	 * @param int 	$ref_id
	 * @param string 	$search_type
	 *
	 * @return Object 	of search type
	 */
	protected function getAllChildrenOfByType($ref_id, $search_type) {
		$childs = $this->g_tree->getChilds($ref_id);
		$ret = array();

		foreach ($childs as $child) {
			$type = $child["type"];
			if($type == $search_type) {
				$ret[] = \ilObjectFactory::getInstanceByRefId($child["child"]);
			}

			if($this->g_objDefinition->isContainer($type)) {
				$rec_ret = $this->getAllChildrenOfByType($child["child"], $search_type);
				if(! is_null($rec_ret)) {
					$ret = array_merge($ret, $rec_ret);
				}
			}
		}

		return $ret;
	}

	/**
	 * Get first child by type recursive
	 *
	 * @param int 	$ref_id
	 * @param string 	$search_type
	 *
	 * @return Object 	of search type
	 */
	protected function getFirstChildOfByType($ref_id, $search_type) {
		$childs = $this->g_tree->getChilds($ref_id);

		foreach ($childs as $child) {
			$type = $child["type"];
			if($type == $search_type) {
				return \ilObjectFactory::getInstanceByRefId($child["child"]);
			}

			if($this->g_objDefinition->isContainer($type)) {
				$ret = $this->getFirstChildOfByType($child["child"], $search_type);
				if(! is_null($ret)) {
					return $ret;
				}
			}
		}

		return null;
	}

	/**
	 * Perform filter on all course informations
	 *
	 * @param array<int, ilObjCourse | ilObjBookingModalities[] | ilObjCourseClassification>
	 * @param array<int, string | int>
	 *
	 * @return BookableCourse[]
	 */
	protected function createBookableCourseByFilter(array $crs_infos, array $filter) {
		$ret = array();

		foreach ($crs_infos as $key => $value) {
			$crs = $value["crs"];

			$start_date = $crs->getCourseStart();
			$end_date = $crs->getCourseEnd();
			$title = $crs->getTitle();

			list($venue_id, $city, $address) = $this->helper->getVenueInfos($crs->getId());
			list($type_id,$type,$target_group_ids,$target_group,$goals,$topic_ids,$topics) = $this->helper->getCourseClassificationValues($value["xccl"]);
			list($provider_id) = $this->helper->getProviderInfos($crs->getId());

			if($start_date) {
				if(array_key_exists(Helper::F_DURATION, $filter)
					&& !$this->filter->courseInFilterPeriod($start_date, $filter[Helper::F_DURATION]["start"], $filter[Helper::F_DURATION]["end"])
				) {
					unset($crs_infos[$key]);
					continue;
				}

				if($this->filter->courseIsExpired($start_date)) {
					unset($crs_infos[$key]);
					continue;
				}
			}

			if(array_key_exists(Helper::F_TARGET_GROUP, $filter)
				&& (count($target_group_ids) == 0
					|| !$this->filter->courseHasTargetGroups($target_group_ids, $filter[Helper::F_TARGET_GROUP])
				)
			) {
				unset($crs_infos[$key]);
				continue;
			}

			if(array_key_exists(Helper::F_TOPIC, $filter)
				&& (count($topic_ids) == 0
					|| !$this->filter->courseHasTopics($topic_ids, $filter[Helper::F_TOPIC])
				)
			) {
				unset($crs_infos[$key]);
				continue;
			}

			if(array_key_exists(Helper::F_TYPE, $filter)
				&& !$this->filter->courseHasType($type_id, $filter[Helper::F_TYPE])
			) {
				unset($crs_infos[$key]);
				continue;
			}

			if(array_key_exists(Helper::F_TITLE, $filter)
				&& !$this->filter->crsTitleStartsWith($title, $filter[Helper::F_TITLE])
			) {
				unset($crs_infos[$key]);
				continue;
			}

			$ret[] = $this->getBookableCourse((int)$crs->getRefId(),
				$title,
				$type,
				$start_date,
				"",
				$target_group,
				$goals,
				$topics,
				$end_date,
				$city,
				$address
			);
		}

		return $ret;
	}
}

/**
 * cat-tms-patch end
 */
