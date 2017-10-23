<?php
use ILIAS\TMS\Mailing;

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

/**
 * Course-related placeholder-values
 */
class ilTMSMailContextCourse implements Mailing\MailContext {
	private static $PLACEHOLDER = array(
		'COURSE_TITLE' => 'crsTitle',
		'COURSE_LINK' => 'crsLink',
		'SCHEDULE' => 'crsSchedule'
	);

	/**
	 * @var int
	 */
	protected $crs_ref_id;

	public function __construct($crs_ref_id) {
		assert('is_int($crs_ref_id)');
		$this->crs_ref_id = $crs_ref_id;
	}

	/**
	 * @inheritdoc
	 */
	public function valueFor($placeholder_id, $contexts = array()) {
		if(array_key_exists($placeholder_id, $this::$PLACEHOLDER)){
			$func = $this::$PLACEHOLDER[$placeholder_id];
			return $this->$func();
		}
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function placeholderIds() {
		return array_keys($this::$PLACEHOLDER);
	}

	/**
	 * @return int
	 */
	public function getCourseRefId() {
		return $this->crs_ref_id;
	}

	/**
	 * @return string
	 */
	public function crsTitle() {
		global $ilObjDataCache;
		$obj_id = $ilObjDataCache->lookupObjId($this->getCourseRefId());
		return $ilObjDataCache->lookupTitle($obj_id);
	}

	/**
	 * @return string
	 */
	public function crsLink() {
		require_once './Services/Link/classes/class.ilLink.php';
		return ilLink::_getLink($this->getCourseRefId(), 'crs');
	}


	/**
	 * @return string
	 */
	public function crsSchedule() {
		$schedule = array();
		$sessions = $this->getSessionAppointments();
		foreach ($sessions as $sortdat => $times) {
			list($date, $start, $end) = $times;
			$schedule[] = sprintf("%s, %s - %s %s",
				$date,
				$start, $end, $this->g_lang->txt('oclock')
			);
		}
		return implode('<br>', $schedule);
	}

	/**
	 * @return string | null
	 */
	public function crsStartdate() {
		$crs = $this->getCourseObject();
		$start = $crs->getCourseStart();
		if($start) {
			return $start->get(IL_CAL_FKT_DATE, "d.m.Y");
		} else {
			return null;
		}

	}

	/**
	 * @return string | null
	 */
	public function crsEnddate() {
		$crs = $this->getCourseObject();
		$end = $crs->getCourseEnd();
		if($end) {
			return $end->get(IL_CAL_FKT_DATE, "d.m.Y");
		} else {
			return null;
		}
	}

	/**
	 * @return string | null
	 */
	public function trainerFirstname() {
		$trainer = $this->getTrainer();
		if($trainer !== null) {
			return $trainer->getFirstname();
		}
		return $trainer;
	}

	/**
	 * @return string | null
	 */
	public function trainerLastname() {
		$trainers = $this->getTrainers();
		if(count($trainers) === 0) {
			return null;
		}
		$trainer = array_shift($trainers);
		return $trainer->getLastname();
	}


	/**
	 * @param  ilObjUser 	$a
	 * @param  ilObjUser 	$b
	 * @return int
	 */
	protected function sortByLastname($a, $b) {
		return strcasecmp($a->getLastname(), $b->getLastname());
	}


	/**
	 * @return string | null
	 */
	public function trainerAll() {
		$trainers = $this->getTrainers();
		if(count($trainers) === 0) {
			return null;
		}

		usort($trainers, array($this, 'sortByLastname'));

		$buf = [];
		foreach ($trainers as $trainer) {
			$buf[] =  $trainer->getFirstname() .' ' .$trainer->getLastname();
		}
		return implode(', ', $buf);
	}

	/**
	 * @return string | null
	 */
	public function adminFirstname() {
		$admin = $this->getAdmin();
		if($admin !== null) {
			return $admin->getFirstname();
		}
		return $admin;
	}

	/**
	 * @return string | null
	 */
	public function adminLastname() {
		$admin = $this->getAdmin();
		if($admin !== null) {
			return $admin->getLastname();
		}
		return $admin;
	}

	/**
	 * @return string | null
	 */
	public function adminEmail() {
		$admin = $this->getAdmin();
		if($admin !== null) {
			return $admin->getEmail();
		}
		return $admin;
	}


	/**
	 * @return [CaT\Plugins\Venues\ilActions | null, CaT\Plugins\Venues\VenueAssignment | null]
	 */
	protected function getCrsVenueAssignmentAndActions() {
		if(!ilPluginAdmin::isPluginActive('venues')) {
			return null;
		}
		$vplug = ilPluginAdmin::getPluginObjectById('venues');
		$vactions =  $vplug->getActions();
		$vassignment = $vactions->getAssignment($this->getCourseObjectId());
		if(! $vassignment) { //no venue configured at course.
			$vassignment = null;
		}
		return [$vactions, $vassignment];
	}

	/**
	 * @return string | null
	 */
	public function crsVenue() {
		list($vactions, $vassignment) = $this->getCrsVenueAssignmentAndActions();
		if(is_null($vassignment)) {
			return null;
		}
		if($vassignment->isCustomAssignment()) {
			$venue_text = $vassignment->getVenueText();
		} else {
			$vid = $vassignment->getVenueId();
			$v = $vactions->getVenue($vid);
			$gen = $v->getGeneral();
			$add = $v->getAddress();
			$con = $v->getContact();

			$venue_text = array(
				$gen->getName(),
				$add->getAddress1(),
				$add->getAddress2(),
				$add->getPostcode() .' ' .$add->getCity(),
				$con->getPhone(),
				$gen->getHomepage()
			);
			$venue_text = array_filter($venue_text, function($val) {return trim($val) !== '';});
			$venue_text = implode('<br />', $venue_text);
		}
		return $venue_text;
	}

	/**
	 * @return string | null
	 */
	public function crsVenueName() {
		list($vactions, $vassignment) = $this->getCrsVenueAssignmentAndActions();
		if(is_null($vassignment)) {
			return null;
		}
		if($vassignment->isCustomAssignment()) {
			return null; //or: the first line?
		}
		$vid = $vassignment->getVenueId();
		$v = $vactions->getVenue($vid);
		$gen = $v->getGeneral();
		return $gen->getName();
	}

	/**
	 * @return string | null
	 */
	public function crsProvider() {
		if(!ilPluginAdmin::isPluginActive('trainingprovider')) {
			return null;
		}
		$pplug = ilPluginAdmin::getPluginObjectById('trainingprovider');
		$pactions = $pplug->getActions();
		$passignment = $pactions->getAssignment($this->getCourseObjectId());

		if(! $passignment) { //no provider configured at course.
			return null;
		}

	/**
	 * Get session appointments from within the course
	 *
	 * @param Entity $entity
	 * @param Object 	$object
	 *
	 * @return string
	 */
	protected function getSessionAppointments() {
		$vals = array();
		$sessions = $this->getAllChildrenOfByType($this->getCourseRefId(), "sess");

		if(count($sessions) > 0) {
			foreach ($sessions as $session) {
				$appointment = $session->getFirstAppointment();
				$sort_date = $appointment->getStart()->get(IL_CAL_FKT_DATE, "YmdHi");
				$start_date = $appointment->getStart()->get(IL_CAL_FKT_DATE, "d.m.Y");
				$start_time = $appointment->getStart()->get(IL_CAL_FKT_DATE, "H:i");
				$end_time = $appointment->getEnd()->get(IL_CAL_FKT_DATE, "H:i");
				$vals[$sort_date] = array($start_date, $start_time, $end_time);
			}
		}

		ksort($vals, SORT_NUMERIC);
		return $vals;
	}



	/**
	 * Get all children by type recursive
	 *
	 * @param int 	$ref_id
	 * @param string 	$search_type
	 *
	 * @return Object 	of search type
	 */
	protected function getAllChildrenOfByType($ref_id, $search_type) {
		global $DIC;
		$g_tree = $DIC->repositoryTree();
		$g_objDefinition = $DIC["objDefinition"];

		$childs = $g_tree->getChilds($ref_id);
		$ret = array();

		foreach ($childs as $child) {
			$type = $child["type"];
			if($type == $search_type) {
				$ret[] = \ilObjectFactory::getInstanceByRefId($child["child"]);
			}

			if($g_objDefinition->isContainer($type)) {
				$rec_ret = $this->getAllChildrenOfByType($child["child"], $search_type);
				if(! is_null($rec_ret)) {
					$ret = array_merge($ret, $rec_ret);
				}
			}
		}

		return $ret;
	}


}
