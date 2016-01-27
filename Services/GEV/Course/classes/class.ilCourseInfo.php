<?php
/**
* this class stores course informations to get the option to compare
* courses after udates
*
*
*/

require_once("./Services/EventHandling/classes/class.ilEventHookPlugin.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

class ilCourseInfo {
	protected $title; //string
	protected $description; //string
	protected $start_date; //ilDate
	protected $end_date; //ilDate
	protected $schedule; //array
	protected $venue; //integer
	protected $venue_free; //string
	protected $vc_link; //string
	protected $vc_password_tutor; //string
	protected $vc_password_member; //string
	protected $accomodation_id; //integer

	protected $obj_id; //integer
	protected $ref_id; //integer

	public function __construct($crs_id) 
	{
		assert(is_int($crs_id));

		$crs_utils = gevCourseUtils::getInstance($crs_id);
		$crs_utils->refreshCourse();

		//never compare these
		$this->obj_id = $crs_utils->getId();
		$this->ref_id = $crs_utils->getRefId();

		//to compare
		$this->title = $crs_utils->getTitle();
		$this->description = $crs_utils->getSubtitle();
		$this->start_date = $crs_utils->getStartDate();
		$this->end_date = $crs_utils->getStartDate();
		$this->schedule = $crs_utils->getSchedule();
		$this->venue = $crs_utils->getVenueId();
		$this->venue_free = $crs_utils->getVenueFreeText();
		$this->vc_link = $crs_utils->getVirtualClassLink();
		$this->vc_password_tutor = $crs_utils->getVirtualClassPasswordTutor();
		$this->vc_password_member = $crs_utils->getVirtualClassPassword();
		$this->accomodation_id = $crs_utils->getAccomodationId();
	}

	/**
	* gets the obj_id
	*
	* @return integer
	*/
	public function obId() {
		return $this->obj_id;
	}

	/**
	* gets the ref_id
	*
	* @return integer
	*/
	public function refId() {
		return $this->ref_id;
	}

	/**
	* get the title
	*
	* @return string
	*/
	public function title() {
		return $this->title;
	}

	/**
	* get the description
	*
	* @return string
	*/
	public function description() {
		return $this->description;
	}

	/**
	* get the start_date
	*
	* @return ilDate
	*/
	public function start_date() {
		return $this->start_date;
	}

	/**
	* get the end_date
	*
	* @return ilDate
	*/
	public function end_date() {
		return $this->end_date;
	}

	/**
	* get the schedule
	*
	* @return array
	*/
	public function schedule() {
		return $this->schedule;
	}

	/**
	* get the venue
	*
	* @return string
	*/
	public function venue() {
		return $this->venue;
	}

	/**
	* get the venue_free
	*
	* @return string
	*/
	public function venue_free() {
		return $this->venue_free;
	}

	/**
	* get the vc_link
	*
	* @return string
	*/
	public function vc_link() {
		return $this->vc_link;
	}

	/**
	* get the vc_password_tutor
	*
	* @return string
	*/
	public function vc_password_tutor() {
		return $this->vc_password_tutor;
	}

	/**
	* get the vc_password_member
	*
	* @return string
	*/
	public function vc_password_member() {
		return $this->vc_password_member;
	}

	/**
	* get the accomodation_id
	*
	* @return string
	*/
	public function accomodation_id() {
		return $this->accomodation_id;
	}

	/**
	* gets all properties in an array
	*
	* @return array
	*/
	public function getAllProperties() {
		return array("title"=>$this->title()
					, "description"=>$this->description()
					, "start_date"=>$this->start_date()
					, "end_date"=>$this->end_date()
					, "schedule"=>$this->schedule()
					, "venue"=>$this->venue()
					, "venue_free"=>$this->venue_free()
					, "vc_link"=>$this->vc_link()
					, "vc_password_tutor"=>$this->vc_password_tutor()
					, "vc_password_member"=>$this->vc_password_member()
					, "accomodation_id"=>$this->accomodation_id()
				);
	}

	/**
	* search for differences between his own and given
	* return TRUE if there are differences
	*
	* @param ilCourseInfo $to_compare
	*
	* @return boolean
	*/
	public function compareWith(ilCourseInfo $to_compare) {
		$to_compare_array = $to_compare->getAllProperties();

		foreach ($this->getAllProperties() as $key => $value) {
			if(gettype($value) == "array") {
				if(array_diff_assoc($to_compare_array[$key], $value) || array_diff_assoc($value, $to_compare_array[$key])) {
					return true;
				}

				continue;
			}

			if(gettype($value) == "ilDate") {
				if($this->compareDates($to_compare_array[$key], $value)) {
					return true;
				}

				continue;
			}

			if($to_compare_array[$key] != $value) {
				return true;
			}
		}

		return false;
	}

	/**
	* compares dates
	* return TRUE if there are differences
	*
	* @param ilDate $date1
	* @param ilDate $date2
	*
	* @return boolean
	*/
	protected function compareDates(ilDate $date1, ilDate $date2) {
		$date1_str = $date1->get(IL_CAL_DATE);
		$date2_str = $date2->get(IL_CAL_DATE);

		return $date1_str != $date2_str;
	}
}
