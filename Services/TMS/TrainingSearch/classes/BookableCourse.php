<?php

use CaT\Ente\ILIAS\ilHandlerObjectHelper;
use ILIAS\TMS\CourseInfo;
use ILIAS\TMS\CourseInfoHelper;
use ILIAS\TMS\CourseAction;
use ILIAS\TMS\CourseActionHelper;

/**
 * cat-tms-patch start
 */

class BookableCourse {


	/**
	 * @var	int
	 */
	protected $ref_id;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var ilDateTime | null
	 */
	protected $begin_date;

	/**
	 * @var string
	 */
	protected $bookings_available;

	/**
	 * @var string[]
	 */
	protected $target_group;

	/**
	 * @var string
	 */
	protected $goals;

	/**
	 * @var string[]
	 */
	protected $topics;

	/**
	 * @var ilDateTime | null
	 */
	protected $end_date;

	/**
	 * @var string
	 */
	protected $location;

	/**
	 * @var string
	 */
	protected $address;

	/**
	 * @var string
	 */
	protected $fee;

	public function __construct
		($ref_id,
		$title,
		$type,
		ilDateTime $begin_date = null,
		$bookings_available,
		array $target_group,
		$goals,
		array $topics,
		ilDateTime $end_date = null,
		$location,
		$address,
		$fee
	) {
		assert('is_int($ref_id)');
		assert('is_string($title)');
		assert('is_string($type)');
		assert('is_string($bookings_available)');
		assert('is_array($target_group)');
		assert('is_string($goals)');
		assert('is_array($topics)');
		assert('is_string($location)');
		assert('is_string($address)');
		assert('is_string($fee)');

		$this->ref_id = $ref_id;
		$this->title = $title;
		$this->type = $type;
		$this->begin_date = $begin_date;
		$this->bookings_available = $bookings_available;
		$this->target_group = $target_group;
		$this->goals = $goals;
		$this->topics = $topics;
		$this->end_date = $end_date;
		$this->location = $location;
		$this->address = $address;
		$this->fee = $fee;
	}

	public function getRefId() {
		return $this->ref_id;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getType() {
		return $this->type;
	}

	public function getBeginDate() {
		return $this->begin_date;
	}

	public function getBookingsAvailable() {
		return $this->bookings_available;
	}

	public function getTargetGroup() {
		return $this->target_group;
	}

	public function getGoals() {
		return $this->goals;
	}

	public function getTopics() {
		return $this->topics;
	}

	public function getEndDate() {
		return $this->end_date;
	}

	public function getLocation() {
		return $this->location;
	}

	public function getAddress() {
		return $this->address;
	}

	public function getFee() {
		return $this->fee;
	}

	// TODO: this propably doesn't belong here. This might be removed or consolidated
	// once the search logic is turned into a proper db-query. This also deserves tests.

	use ilHandlerObjectHelper;
	use CourseInfoHelper;
	use CourseActionHelper;
	use \ILIAS\TMS\MyUsersHelper;

	/**
	 * @var	CourseInfo[]|null
	 */
	protected $short_info = null;

	/**
	 * @var	CourseInfo[]|null
	 */
	protected $detail_info = null;

	protected function getDIC() {
		return $GLOBALS["DIC"];
	}

	protected function getEntityRefId() {
		return $this->ref_id;
	}

	protected function getShortInfo() {
		if ($this->short_info === null) {
			$this->short_info = $this->getCourseInfo(CourseInfo::CONTEXT_SEARCH_SHORT_INFO);
		}
		return $this->short_info;
	}

	protected function getDetailInfo() {
		if ($this->detail_info === null) {
			$this->detail_info = $this->getCourseInfo(CourseInfo::CONTEXT_SEARCH_DETAIL_INFO);
		}
		return $this->detail_info;
	}

	protected function getFurtherInfo() {
		if ($this->small_detail_info === null) {
			$this->small_detail_info = $this->getCourseInfo(CourseInfo::CONTEXT_SEARCH_FURTHER_INFO);
		}
		return $this->small_detail_info;
	}

	protected function getSearchActions() {
		if ($this->search_actions === null) {
			$this->search_actions = $this->getCourseAction(CourseAction::CONTEXT_SEARCH);
		}
		return $this->search_actions;
	}

	protected function getSuperiorSearchActions() {
		if ($this->superior_search_actions === null) {
			$this->superior_search_actions = $this->getCourseAction(CourseAction::CONTEXT_SUPERIOR_SEARCH);
		}
		return $this->superior_search_actions;
	}

	protected function getIsBookable() {
		if ($this->is_bookable === null) {
			$this->is_bookable = $this->getCourseInfo(CourseInfo::CONTEXT_IS_BOOKABLE);
		}
		return $this->is_bookable;
	}

	protected function getIDDRelevant() {
		if ($this->idd_relevant === null) {
			$this->idd_relevant = $this->getCourseInfo(CourseInfo::CONTEXT_IDD_RELEVANT);
		}
		return $this->idd_relevant;
	}

	public function getTitleValue() {
		// Take most important info as title
		$short_info = $this->getShortInfo();
		if (count($short_info) > 0) {
			return $short_info[0]->getValue();
		}
		return $this->getUnknownString();
	}

	public function getSubTitleValue() {
		// Take second most important info as subtitle
		$short_info = $this->getShortInfo();
		if (count($short_info) > 1) {
			return $short_info[1]->getValue();
		}
		return $this->getUnknownString();
	}

	public function getImportantFields() {
		// Take info 2-7 as fields in header line
		$short_info = $this->getShortInfo();
		return $this->unpackValue(array_slice($short_info, 2, 5));
	}

	public function getFurtherFields() {
		// Take info 2 to end as fields in header line
		return $this->unpackLabelAndNestedValue($this->getUIFactory(), $this->getFurtherInfo());
	}

	public function getDetailFields() {
		$detail_info = $this->getDetailInfo();
		if(count($detail_info) > 0) {
			return $this->unpackLabelAndNestedValue($this->getUIFactory(), $this->getDetailInfo());
		}

		return ["" => $this->getNoDetailInfoMessage()];
	}

	/**
	 * Returns the course is bookable or not
	 *
	 * @return bool
	 */
	public function isBookable() {
		$is_bookable = $this->getIsBookable();
		return count($is_bookable) > 0;
	}

	/**
	 * Returns the course is idd relevant or not
	 *
	 * @return bool
	 */
	public function isIDDRelevant() {
		$idd_relevant = $this->getIDDRelevant();
		return count($idd_relevant) > 0;
	}

	public function getSearchActionLinks(\ilCtrl $ctrl, $usr_id, $superior) {
		if($superior) {
			$search_actions = $this->getSuperiorSearchActions();
		} else {
			$search_actions = $this->getSearchActions();
		}

		$ret = array();
		foreach($search_actions as $search_action) {
			if($search_action->isAllowedFor($usr_id)) {
				$ret[$search_action->getLabel()] = $search_action->getLink($ctrl, $usr_id);
			}
		}

		return $ret;
	}

	/**
	 * Checks the bookingmodalities allows user to book
	 *
	 * @return bool
	 */
	protected function isAllowedToBook($booking_modus, $usr_id) {
		global $DIC;
		$g_user = $DIC->user();

		if ($g_user->getId() == $usr_id
			&& $booking_modus == "self_booking"
		) {
			return true;
		}

		if ($g_user->getId() == $usr_id
			&& $booking_modus == "booking_superior"
		) {
			return false;
		}

		$employees = $this->getUserWhereCurrentCanBookFor((int)$g_user->getId());
		if(array_key_exists($usr_id, $employees)
			&& $booking_modus == "booking_superior"
		) {
			return true;
		}

		return false;
	}

	/**
	 * Get the UI-factory.
	 *
	 * @return ILIAS\UI\Factory
	 */
	public function getUIFactory() {
		global $DIC;
		return $DIC->ui()->factory();
	}

	/**
	 * Form date for gui as user timezone string
	 *
	 * @param ilDateTime 	$dat
	 * @param bool 	$use_time
	 *
	 * @return string
	 */
	protected function formatDate($dat, $use_time = false) {
		global $DIC;
		$user = $DIC["ilUser"];
		require_once("Services/Calendar/classes/class.ilCalendarUtil.php");
		$out_format = ilCalendarUtil::getUserDateFormat($use_time, true);
		$ret = $dat->get(IL_CAL_FKT_DATE, $out_format, $user->getTimeZone());
		if(substr($ret, -5) === ':0000') {
			$ret = substr($ret, 0, -5);
		}

		return $ret;
	}

	/**
	 * Get a string that is "unknown" in the users language.
	 *
	 * @return string
	 */
	protected function getUnknownString() {
		global $DIC;
		$lng = $DIC["lng"];
		return $lng->txt("unknown");
	}

	/**
	 * Get a string that is "unknown" in the users language.
	 *
	 * @return string
	 */
	protected function getNoDetailInfoMessage() {
		global $DIC;
		$lng = $DIC["lng"];
		return $lng->txt("no_detail_infos");
	}
}

/**
 * cat-tms-patch end
 */
