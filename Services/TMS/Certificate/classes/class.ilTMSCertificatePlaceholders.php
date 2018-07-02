<?php
//require_once('./Services/User/classes/class.ilObjUser.php');

/**
 * Append placeholders to certificates.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilTMSCertificatePlaceholders {

	//2do: description should be put through translation.
	private static $PLACEHOLDER = array(
		'IDD_TIME' => array('iddTime', '(TMS) IDD-Einstellung des Kurses (EduTracking)'),
		'IDD_USER_TIME' => array('iddUserTime', '(TMS) IDD-Zeit des Users (CourseMember)'),
		'COURSE_START_DATE' => array('crsStartDate', '(TMS) Startdatum des Kurses'),
		'COURSE_END_DATE' => array('crsEndDate', '(TMS) Enddatum des Kurses'),
		'COURSE_TYPE' => array('crsType', '(TMS) Kurstyp')
	);

	/**
	 * @var \ilObjCourse
	 */
	protected $crs_obj;


	/**
	 * @param \ilObjCourse 	$crs_obj
	 */
	public function __construct($crs_obj) {
		global $DIC;
		$this->g_tree = $DIC->repositoryTree();
		$this->g_objDefinition = $DIC["objDefinition"];

		$this->crs_obj = $crs_obj;
	}

	/**
	 * return the value for a placeholder
	 * @param string 	$placeholder
	 * @param int | null 	$usr_id
	 * @return string | null
	 */
	public function getValueFor($placeholder, $usr_id) {
		if(! array_key_exists($placeholder, self::$PLACEHOLDER)) {
			return null;
		}
		$func = $this::$PLACEHOLDER[$placeholder][0];
		return $this->$func($usr_id);

	}

	/**
	 * return the preview-value for a placeholder
	 * @param string 	$placeholder
	 * @return string | null
	 */
	public function getPreviewFor($placeholder) {
		if(! array_key_exists($placeholder, self::$PLACEHOLDER)) {
			return null;
		}
		switch ($placeholder) {

			case 'IDD_USER_TIME':
				return '01:23';

			case 'IDD_TIME':
			case 'COURSE_START_DATE':
			case 'COURSE_END_DATE':
			case 'COURSE_TYPE':
				return $this->getValueFor($placeholder, null);

			default:
				return null;
		}
		return $placeholder;
	}

	/**
	 * return all placeholders with id=>description
	 * @return array<string, string>
	 */
	public function placeholderDescriptions() {
		$buf = array();
		foreach (self::$PLACEHOLDER as $id => list($func, $desc)) {
			$buf[$id] = $desc;
		}
		return $buf;
	}

	/**
	 * return all placeholders and values for preview
	 * @return array<string, string>
	 */
	public function placeholderPreview() {
		$buf = array();
		foreach (array_keys(self::$PLACEHOLDER) as $id) {
			$buf[$id] = $this->getPreviewFor($id);
		}
		return $buf;
	}

	/**
	 * return all placeholders and values for outpu
	 * @return array<string, string>
	 */
	public function placeholderPresentation($usr_id) {
		$buf = array();
		foreach (array_keys(self::$PLACEHOLDER) as $id) {
			$buf[$id] = $this->getValueFor($id, $usr_id);
		}
		return $buf;
	}



	/**
	 * Get all children by type recursive
	 *
	 * @param int 	$ref_id
	 * @param string 	$search_type
	 *
	 * @return Object[] 	of search type
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
	 * Transforms the idd minutes into printable string
	 *
	 * @param int 	$minutes
	 *
	 * @return string
	 */
	protected function transformMinutes($minutes)
	{
		if ($minutes === null) {
			return "";
		}
		$hours = floor($minutes / 60);
		$minutes = $minutes - $hours * 60;
		return str_pad($hours, "2", "0", STR_PAD_LEFT).":".str_pad($minutes, "2", "0", STR_PAD_LEFT);
	}


	protected function iddTime($usr_id) {
		$edutracking = $this->getFirstChildOfByType($this->crs_obj->getRefId(), 'xetr');
		if(! $edutracking) {
			return $edutracking;
		}
		$actions = $edutracking->getActionsFor("IDD");
		$settings = $actions->select();
		return $this->transformMinutes($settings->getMinutes());
	}

	protected function iddUserTime($usr_id) {
		$coursemember = $this->getFirstChildOfByType($this->crs_obj->getRefId(), 'xcmb');
		if(! $coursemember) {
			return $coursemember;
		}
		$actions = $coursemember->getActions();
		$members = $actions->getMemberWithSavedLPSatus();
		foreach ($members as $key => $member) {
			if((int)$usr_id === $member->getUserId()) {
				$user_idd_time = $member->getIDDLearningTime();
				return $this->transformMinutes($user_idd_time);
			}
		}
		return null;
	}

	protected function crsStartDate($usr_id) {
		$start = $this->crs_obj->getCourseStart();
		if($start) {
			return $start->get(IL_CAL_FKT_DATE, "d.m.Y");
		} else {
			return null;
		}
	}

	protected function crsEndDate($usr_id) {
		$end = $this->crs_obj->getCourseEnd();
		if($end) {
			return $end->get(IL_CAL_FKT_DATE, "d.m.Y");
		} else {
			return null;
		}
	}

	protected function crsType($usr_id) {
		$cc = $this->getFirstChildOfByType($this->crs_obj->getRefId(), 'xccl');
		if(! $cc) {
			return $cc;
		}
		$actions = $cc->getActions();
		$settings = $cc->getCourseClassification();
		$type_id = $settings->getType();
		return $actions->getTypeName($type_id)[0];
	}
}
