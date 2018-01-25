<?php
use ILIAS\TMS\Mailing;

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

/**
 * Course-related placeholder-values
 */
class ilTMSMailContextCourse implements Mailing\MailContext {
	private static $PLACEHOLDER = array(
		'COURSE_TITLE' => 'placeholder_desc_crs_title',
		'COURSE_LINK' => 'placeholder_desc_crs_link',
		'SCHEDULE' => 'placeholder_desc_crs_schedule',
		'COURSE_START_DATE' => 'placeholder_desc_crs_startdate',
		'COURSE_END_DATE' => 'placeholder_desc_crs_enddate',
		'TRAINER_FIRST_NAME' => 'placeholder_desc_crs_trainer_firstname',
		'TRAINER_LAST_NAME' => 'placeholder_desc_crs_trainer_lastname',
		'OFFICE_FIRST_NAME' => 'placeholder_desc_crs_admin_firstname',
		'OFFICE_LAST_NAME' => 'placeholder_desc_crs_admin_lastname',
		'VENUE' => 'placeholder_desc_crs_venue',
		'TRAINING_PROVIDER' => 'placeholder_desc_crs_provider'
	);

	/**
	 * @var int
	 */
	protected $crs_ref_id;

	/**
	 * @var ilObjCourse | null
	 */
	protected $crs_obj;

	/**
	 * @var ilLanguage
	 */
	protected $g_lang;

	public function __construct($crs_ref_id) {
		assert('is_int($crs_ref_id)');
		$this->crs_ref_id = $crs_ref_id;

		global $DIC;
		$this->g_lang = $DIC->language();
		$this->g_lang->loadLanguageModule("tms");
	}

	/**
	 * @inheritdoc
	 */
	public function valueFor($placeholder_id, $contexts = array()) {
		switch ($placeholder_id) {
			case 'COURSE_TITLE':
				return $this->crsTitle();
			case 'COURSE_LINK':
				return $this->crsLink();
			case 'SCHEDULE':
				return $this->crsSchedule();
			case 'COURSE_START_DATE':
				return $this->crsStartdate();
			case 'COURSE_END_DATE':
				return $this->crsEnddate();
			case 'TRAINER_FIRST_NAME':
				return $this->trainerFirstname();
			case 'TRAINER_LAST_NAME':
				return $this->trainerLastname();
			case 'OFFICE_FIRST_NAME':
				return $this->adminFirstname();
			case 'OFFICE_LAST_NAME':
				return $this->adminLastname();
			case 'VENUE':
				return $this->crsVenue();
			case 'TRAINING_PROVIDER':
				return $this->crsProvider();
			default:
				return null;
		}

	}

	/**
	 * @inheritdoc
	 */
	public function placeholderIds() {
		return array_keys($this::$PLACEHOLDER);
	}

	/**
	 * @inheritdoc
	 */
	public function placeholderDescriptionForId($placeholder_id) {
		return $this->g_lang->txt(self::$PLACEHOLDER[$placeholder_id]);
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

		if($passignment->isCustomAssignment()) {
			$provider_text = $passignment->getProviderText();
		} else {
			$pid = $passignment->getProviderId();
			$pvals = $pactions->getProviderValues($pid);
			$provider_text = implode('<br />', array(
				$pvals[$pactions::F_NAME],
				$pvals[$pactions::F_ADDRESS1],
				$pvals[$pactions::F_ADDRESS2],
				$pvals[$pactions::F_POSTCODE] .' '.$pvals[$pactions::F_CITY]
			));
		}
		return $provider_text;
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

	/**
	 * Get the course-object
	 *
	 * @return ilObjCourse
	 */
	protected function getCourseObject() {
		if(! $this->crs_obj) {
			$this->crs_obj = \ilObjectFactory::getInstanceByRefId($this->getCourseRefId());
		}
		return $this->crs_obj;
	}

	/**
	 * Get the course-object's obj_id
	 *
	 * @return int
	 */
	protected function getCourseObjectId() {
		global $ilObjDataCache;
		return $ilObjDataCache->lookupObjId($this->getCourseRefId());
	}

	/**
	 * Get first member with trainer-role
	 *
	 * @return ilObjUser | null
	 */
	protected function getTrainer() {
		$participants = $this->getCourseObject()->getMembersObject();
		$trainers = $participants->getTutors();
		if(count($trainers) > 0) {
			$trainer_id = (int)$trainers[0];
			return new \ilObjUser($trainer_id);
		}
		return null;
	}

	/**
	 * Get first member with admin-role
	 *
	 * @return ilObjUser | null
	 */
	protected function getAdmin() {
		$participants = $this->getCourseObject()->getMembersObject();
		$admins = $participants->getAdmins();
		if(count($admins) > 0) {
			$admin_id = (int)$admins[0];
			return new \ilObjUser($admin_id);
		}
		return null;
	}

}
