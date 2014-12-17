<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * List all users from course
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesCourseBooking
 */
class ilCourseBookingMembersTableGUI extends ilTable2GUI
{
	protected $has_waiting; // [bool]
	protected $cancel_deadline_expired; // [bool]
	protected $perm_cancel_others; // [bool]
	protected $perm_book_others; // [bool]	
	// gev-patch start
	private $course;
	// gev-patch end
	
	/**
	 * Constructor
	 *
	 * @param ilObject $a_parent_obj
	 * @param string $a_parent_cmd
	 * @param ilObjCourse $a_course
	 * @param ilCourseBookingPermissions $a_perm
	 */
	public function  __construct($a_parent_obj, $a_parent_cmd, ilObjCourse $a_course, ilCourseBookingPermissions $a_perm = null)
	{
		global $ilCtrl, $ilUser;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);			
		
		// gev-patch start
		$this->course = $a_course;

		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$this->user = &$ilUser;
		$this->userUtils = gevUserUtils::getInstance($this->user->getId());
		// gev-patch end

		$bookings = ilCourseBookings::getInstance($a_course);
		$this->has_waiting = $bookings->isWaitingListActivated();
		
		$helper = ilCourseBookingHelper::getInstance($a_course);
		$this->cancel_deadline_expired = $helper->isCancellationDeadlineReached();
		
		if($helper->isUltimateBookingDeadlineReached() || !$a_perm)
		{
			$this->perm_cancel_others = $this->perm_book_others = false;
		}
		else
		{
			$this->perm_cancel_others = $a_perm->cancelCourseForOthers();
			$this->perm_book_others = $a_perm->bookCourseForOthers();
		}
		
		$this->addColumn($this->lng->txt("name"), "name");
		$this->addColumn($this->lng->txt("login"), "login");
		$this->addColumn($this->lng->txt("objs_orgu"), "org");
		$this->addColumn($this->lng->txt("crsbook_admin_status"), "status");
		$this->addColumn($this->lng->txt("crsbook_admin_status_change"), "status_change");
		
		if($this->perm_cancel_others || $this->perm_book_others)
		{			
			$this->addColumn("", "");
			
			require_once "Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php";
		}
		
		$this->setDefaultOrderField("name");
						
		$this->setRowTemplate("tpl.members_row.html", "Services/CourseBooking");
		$this->setFormAction($ilCtrl->getFormAction($this->getParentObject(), $this->getParentCmd()));	
		
		$this->getItems($a_course, !($a_perm instanceof ilCourseBookingPermissions));
	}

	/**
	 * Get user data
	 * 
	 * @param ilObjCourse $a_course
	 * @param bool $a_show_cancellations
	 */
	protected function getItems(ilObjCourse $a_course, $a_show_cancellations)
	{					
		$data = array();
		
		$status_map = array(
			ilCourseBooking::STATUS_BOOKED => $this->lng->txt("crsbook_admin_status_booked")
			,ilCourseBooking::STATUS_WAITING => $this->lng->txt("crsbook_admin_status_waiting")
			,ilCourseBooking::STATUS_CANCELLED_WITHOUT_COSTS => $this->lng->txt("crsbook_admin_status_cancelled_without_costs")
			,ilCourseBooking::STATUS_CANCELLED_WITH_COSTS => $this->lng->txt("crsbook_admin_status_cancelled_with_costs")
		);
		
		ilDatePresentation::setUseRelativeDates(false);
		
		foreach(ilCourseBooking::getCourseTableData($a_course->getId(), $a_show_cancellations) as $item)
		{			
			$data[] = array(
				"id" => $item["user_id"]
				,"name" => $item["lastname"].", ".$item["firstname"]
				,"login" => $item["login"]
				,"org" => $item["org_unit"]
				,"org_txt" => $item["org_unit_txt"]
				,"status" => $item["status"]
				,"status_txt" => $status_map[$item["status"]]
				,"status_change" => $item["status_changed_on"]
				,"status_change_txt" => 
					ilDatePresentation::formatDate(new ilDateTime($item["status_changed_on"], IL_CAL_UNIX)).
					", ". // $this->lng->txt("by").
					" ".$item["status_changed_by_txt"]
			);
		}
	
		$this->setData($data);
	}
	
	/**
	 * Get user action link
	 * 
	 * @param int $a_user_id
	 * @param int $a_status
	 * @return string
	 */
	protected function getLink($a_user_id, $a_status)
	{
		global $ilCtrl;
		
		$ilCtrl->setParameter($this->getParentObject(), "user_id", $a_user_id);
		$ilCtrl->setParameter($this->getParentObject(), "user_status", $a_status);
		$url = $ilCtrl->getLinkTarget($this->getParentObject(), "confirmUserAction");
		$ilCtrl->setParameter($this->getParentObject(), "user_status", "");
		$ilCtrl->setParameter($this->getParentObject(), "user_id", "");
		return $url;
	}

	/**
	 * Fill template row
	 * 
	 * @param array $a_set
	 */
	protected function fillRow($a_set)
	{
		$this->tpl->setVariable("TXT_NAME", $a_set["name"]);		
		$this->tpl->setVariable("TXT_LOGIN", $a_set["login"]);		
		$this->tpl->setVariable("TXT_ORG", $a_set["org_txt"]);	
		$this->tpl->setVariable("TXT_STATUS", $a_set["status_txt"]);		
		$this->tpl->setVariable("TXT_CHANGE", $a_set["status_change_txt"]);		
	
		// actions
		if($this->perm_cancel_others || $this->perm_book_others)
		{			
			$list = new ilAdvancedSelectionListGUI();
			$list->setId("crsbook".$a_set["id"]); // #25
			$list->setListTitle($this->lng->txt("actions"));
			
			if($this->perm_book_others)
			{
				if($this->has_waiting && $a_set["status"] == ilCourseBooking::STATUS_BOOKED)
				{
					$list->addItem($this->lng->txt("crsbook_admin_action_to_waiting_list"),
						"",
						$this->getLink($a_set["id"], ilCourseBooking::STATUS_WAITING));
				}
				else if($a_set["status"] == ilCourseBooking::STATUS_WAITING)
				{
					$list->addItem($this->lng->txt("crsbook_admin_action_book"),
						"",
						$this->getLink($a_set["id"], ilCourseBooking::STATUS_BOOKED));
				}
			}




			if($this->perm_cancel_others)
			{
				
				// gev-patch start (2014-10-01)
				/*
				$list->addItem($this->lng->txt("crsbook_admin_action_cancel_without_costs"),
						"",
						$this->getLink($a_set["id"], ilCourseBooking::STATUS_CANCELLED_WITHOUT_COSTS));
				
				// gev-patch start
				if($a_set["status"] == ilCourseBooking::STATUS_BOOKED &&
					$this->cancel_deadline_expired)
				{
					$list->addItem($this->lng->txt("crsbook_admin_action_cancel_with_costs"),
						"",
						$this->getLink($a_set["id"], ilCourseBooking::STATUS_CANCELLED_WITH_COSTS));
				}
				*/
				
				// #0000641
				require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
				require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
				require_once("Services/GEV/Utils/classes/class.gevBillingUtils.php");
				
				$crs_utils = gevCourseUtils::getInstanceByObj($this->course);
				$usr_utils = gevUserUtils::getInstance($a_set["id"]);

				$crs_reached_deadline = $this->cancel_deadline_expired;
				$crs_hasfee = $crs_utils->getFee();
				$usr_paysfee = $usr_utils->paysFees();
				$usr_isbooked = ($a_set["status"] == ilCourseBooking::STATUS_BOOKED);
				$has_bill = (gevBillingUtils::getInstance()->getNonFinalizedBillForCourseAndUser() !== null);

				if($crs_reached_deadline && $crs_hasfee && $usr_paysfee && $usr_isbooked && $has_bill){
					//when deadline expired 
					//and user pays fees 
					//and seminar has fees
					//and, of course, user is booked
					$list->addItem($this->lng->txt("crsbook_admin_action_cancel_with_costs"),
						"",
						$this->getLink($a_set["id"], ilCourseBooking::STATUS_CANCELLED_WITH_COSTS));

					//current user is admin, may cancel w/o costs anyway
					if($this->userUtils->isAdmin()){
						$list->addItem($this->lng->txt("crsbook_admin_action_cancel_without_costs"),
							"",
							$this->getLink($a_set["id"], ilCourseBooking::STATUS_CANCELLED_WITHOUT_COSTS));	
					}


				} else {
					//when deadline is not reached 
					//or user pays no fees
					//or seminar is w/o fees
					//or user is not booked (i.e. waiting)
					$list->addItem($this->lng->txt("crsbook_admin_action_cancel_without_costs"),
						"",
						$this->getLink($a_set["id"], ilCourseBooking::STATUS_CANCELLED_WITHOUT_COSTS));
				}
				// gev-patch end (2014-10-01)



			}
			
			$this->tpl->setVariable("ACTIONS", $list->getHTML());
		}
	}
}

?>