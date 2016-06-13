<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/CourseBooking/classes/class.ilCourseBookings.php";
require_once "./Services/CourseBooking/classes/class.ilCourseBookingPermissions.php";

/**
 * Course booking administration GUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesCourseBooking
 * @ilCtrl_Calls ilCourseBookingAdminGUI: ilRepositorySearchGUI
 */
class ilCourseBookingAdminGUI
{
	protected $course; // [ilObjCourse]
	protected $permissions; // [ilCourseBookingPermissions]
	
	/**
	 * Constructor
	 * 
	 * @param ilObjCourse $a_course
	 * @return self
	 */
	public function __construct(ilObjCourse $a_course)
	{
		//gev-patch start
		global $lng, $ilUser, $ilLog;
		$this->gLog = $ilLog;
		//gev-patch end

		
		$this->setCourse($a_course);
		$this->ilUser = $ilUser;
		
		$perm = ilCourseBookingPermissions::getInstance($this->getCourse());
		$this->setPermissions($perm);
		
		if(!$this->getPermissions()->viewOtherBookings())
		{
			ilUtil::sendFailure($lng->txt("msg_no_perm_read"), true);
			$this->returnToParent();
		}
		
		$lng->loadLanguageModule("crsbook");
	}
	
	/**
	 * Factory
	 * 
	 * @throws ilException
	 * @param int $a_ref_id
	 * @return self
	 */
	public static function getInstanceByRefId($a_ref_id)
	{
		global $tree;
		
		if(ilObject::_lookupType($a_ref_id, true) != "crs" ||
			$tree->isDeleted($a_ref_id))
		{
			throw new ilException("CourseBookingAdminGUI - needs course ref id");
		}
		
		require_once "Modules/Course/classes/class.ilObjCourse.php";
		$course = new ilObjCourse($a_ref_id);
		return new self($course);
	}
	
	
	//
	// properties
	//
	
	/**
	 * Set course
	 * 
	 * @param ilObjCourse $a_course
	 */
	protected function setCourse(ilObjCourse $a_course)
	{
		$this->course = $a_course;
	}
	
	/**
	 * Get course
	 * 
	 * @return ilObjCourse 
	 */	
	protected function getCourse()
	{
		return $this->course;
	}
	
	/**
	 * Set permissions
	 * 
	 * @param ilCourseBookingPermissions $a_perms
	 */
	protected function setPermissions(ilCourseBookingPermissions $a_perms)
	{
		$this->permissions = $a_perms;
	}
	
	/**
	 * Get permissions
	 * 
	 * @return ilCourseBookingPermissions 
	 */	
	protected function getPermissions()
	{
		return $this->permissions;
	}
	
			
	//
	// GUI basics
	//
	
	/**
	 * Execute request command
	 * 
	 * @return boolean
	 */
	public function executeCommand()
	{
		global $ilCtrl, $lng;

		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("listBookings");
		
		switch($next_class)
		{			
			case "ilrepositorysearchgui":		
				if(!$ilCtrl->isAsynch())
				{
					$this->setTabs("listBookings");			
				}
				
				$bookings = ilCourseBookings::getInstance($this->getCourse());
				if($bookings->isWaitingListActivated())
				{
					$status = array(
						ilCourseBooking::STATUS_BOOKED => $lng->txt("crsbook_admin_status_booked")
						,ilCourseBooking::STATUS_WAITING => $lng->txt("crsbook_admin_status_waiting")
					);					
				}
				
				include_once "./Services/Search/classes/class.ilRepositorySearchGUI.php";
				$rep_search = new ilRepositorySearchGUI();				
				$rep_search->setCallback($this,
					"assignMembersConfirm",
					$status
				);				
				$rep_search->disableRoleSearch(true);
				$rep_search->setResultsCallback($this, "assignMembersFromSearch");
			
				$ilCtrl->setReturn($this, "listBookings");
				$ilCtrl->forwardCommand($rep_search);											
				break;
			
			default:				
				$this->$cmd();
				break;
		}
		
		return true;
	}
	
	// gev-patch start
	
	static $back_target_id = "course_booking_admin_gui_back_target";
	
	// Functionality to control target of back link
	static public function setBackTarget($a_target) {
		ilSession::set(self::$back_target_id, $a_target);
	}
	
	static public function removeBackTarget() {
		ilSession::clear(self::$back_target_id);
	}
	
	// gev-patch end
	
	/**
	 * Set tabs
	 * 
	 * @param string $a_active
	 */
	protected function setTabs($a_active)
	{
		global $ilTabs, $ilCtrl, $lng;
		
		$ilTabs->clearTargets();
		
		/*
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTargetByClass("ilobjcoursegui", "members"));
		*/
		$back_target = ilSession::get(self::$back_target_id);
		$ilTabs->setBackTarget(
			$lng->txt("back")
			, $back_target === null ? $ilCtrl->getLinkTarget($this, "returnToParent")
									: $back_target
			);
		
		$ilTabs->addTab("listBookings",
			$lng->txt("crsbook_admin_tab_list_bookings"),
			$ilCtrl->getLinkTarget($this, "listBookings"));
		
		$ilTabs->addTab("listCancellations",
			$lng->txt("crsbook_admin_tab_list_cancellations"),
			$ilCtrl->getLinkTarget($this, "listCancellations"));
		
		$ilTabs->activateTab($a_active);
	}
	
	/**
	 * Return to parent GUI
	 */
	protected function returnToParent()
	{
		global $ilCtrl;
		
		$ilCtrl->redirectByClass(array("ilRepositoryGUI", "ilObjCourseGUI"), "members");		
		// $ilCtrl->returnToParent($this);
	}
	
	
	//
	// BOOKINGS
	//
	
	/**
	 * List course bookings
	 */
	protected function listBookings()
	{
		global $ilToolbar, $ilCtrl, $lng, $tpl;
		
		$this->setTabs("listBookings");
				
		if($this->isBookingAllowed())
		{
			$bookings = ilCourseBookings::getInstance($this->getCourse());
			if($bookings->isWaitingListActivated())
			{
				$types = array(
					ilCourseBooking::STATUS_BOOKED => $lng->txt("crsbook_admin_status_booked")
					,ilCourseBooking::STATUS_WAITING => $lng->txt("crsbook_admin_status_waiting")
				);
			}

			include_once "./Services/Search/classes/class.ilRepositorySearchGUI.php";
			ilRepositorySearchGUI::fillAutoCompleteToolbar(
				$this,
				$ilToolbar,
				array(
					"auto_complete_name"	=> $lng->txt("user"),
					"user_type"				=> $types,
					"submit_name"			=> $lng->txt("add"),
					"add_search"			=> true
				)
			);

			$ilToolbar->addSeparator();

			$ilToolbar->addButton($lng->txt("crsbook_admin_add_group"),
				$ilCtrl->getLinkTarget($this, "addGroup"));

			$ilToolbar->addSeparator();

			$ilToolbar->addButton($lng->txt("crsbook_admin_add_org_unit"),
				$ilCtrl->getLinkTarget($this, "addOrgUnit"));
		}		
		
		require_once "Services/CourseBooking/classes/class.ilCourseBookingMembersTableGUI.php";
		$tbl = new ilCourseBookingMembersTableGUI($this, "listBookings", $this->getCourse(), $this->getPermissions());
		return $tpl->setContent($tbl->getHTML());
	}

	/**
	 * Is allowed to book
	 *
	 *@return bool
	 */
	public function isBookingAllowed(){
		$crs_booking_helper = ilCourseBookingHelper::getInstance($this->getCourse());
		if($crs_booking_helper->isUltimateBookingDeadlineReached())
		{	
			return false;
		}

		if($this->getPermissions()->bookCourseForOthers()){
			require_once "Services/GEV/Utils/classes/class.gevCourseUtils.php";
			$crs_utils = gevCourseUtils::getInstance($this->getCourse()->getId());			

			if($crs_utils->isDecentralTraining() &&
				$crs_utils->hasTrainer($this->ilUser->getId()) && 
				$crs_booking_helper->isBookingDeadlineReached() )
			{				
					return false;
						
			}		

			return true;
		}

		return false;
	}
	
	/**
	 * Display search result as table GUI (to set status) 
	 *
	 * @param string $a_parent_gui
	 * @param string $a_parent_cmd
	 * @param array $a_user_ids
	 */
	public function assignMembersFromSearch($a_parent_gui, $a_parent_cmd, array $a_user_ids = null)
	{
		global $tpl, $lng, $ilCtrl;
		
		if(!$a_user_ids)
		{
			$a_user_ids = $_REQUEST["user_ids"];
		}
		if(!count($a_user_ids))
		{
			ilUtil::sendFailure($lng->txt("crs_no_users_selected"), true);
			$ilCtrl->redirect($this, "listBookings");
		}
		
		require_once "Services/CourseBooking/classes/class.ilCourseBookingSearchResultsTableGUI.php";
		$tbl = new ilCourseBookingSearchResultsTableGUI($a_parent_gui, $a_parent_cmd, $a_user_ids);
		
		$tbl->setFormAction($ilCtrl->getFormAction($this, "assignMembersConfirm"));
		$tbl->addCommandButton("assignMembersConfirm", $lng->txt("add"));
		$tbl->addCommandButton("listBookings", $lng->txt("cancel"));
		
		// see ilRepositorySearchGUI::showSearchUserTable()
		$tpl->setVariable("RES_TABLE", $tbl->getHTML());
	}
	
	// #425
	public function showSearchResults()
	{
		global $ilCtrl;
		
		// see ilTableGUI2::determineOffsetAndOrder()
		$old = $_POST["_table_nav"];
		if($_POST["_table_nav1"] != $old)
		{
			$nav = $_POST["_table_nav1"];
		}
		else if($_POST["_table_nav2"] != $old)
		{
			$nav = $_POST["_table_nav2"];
		}					
		$ilCtrl->setParameterByClass("ilrepositorysearchgui", "_table_nav", $nav);
		$ilCtrl->redirectByClass("ilrepositorysearchgui", "showSearchResults");
	}
	
	
	//
	// GROUP
	// 
	
	/**
	 * Add group members
	 * 
	 * @param ilPropertyFormGUI $a_form
	 */	
	protected function addGroup(ilPropertyFormGUI $a_form = null)
	{
		global $tpl;
		
		$this->setTabs("listBookings");
		
		if(!$a_form)
		{
			$a_form = $this->initAddGroupForm();
		}
		$tpl->setContent($a_form->getHTML());
	}
	
	/**
	 * Init form to add members from group
	 * 
	 * @return ilPropertyFormGUI
	 */
	protected function initAddGroupForm()
	{
		global $ilCtrl, $lng, $tree, $ilAccess;
		
		require_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this, "assignMembersFromGroup"));
		$form->setTitle($lng->txt("crsbook_admin_add_group"));

		$options = array("" => $lng->txt("please_select"));
		$this->insertAddableGroups($options);
		if(sizeof($options) == 1)
		{
			ilUtil::sendFailure($lng->txt("admin_no_group_available"), true);
			$ilCtrl->redirect($this, "listBookings");
		}
		
		$grp = new ilSelectInputGUI($lng->txt("obj_grp"), "grp");
		$grp->setRequired(true);
		$grp->setOptions($options);
		$form->addItem($grp);
		
		$form->addCommandButton("assignMembersFromGroup", $lng->txt("continue"));
		$form->addCommandButton("listBookings", $lng->txt("cancel"));
		
		return $form;
	}
	
	protected function insertAddableGroups(&$a_options) {
		global $ilDB, $ilAccess;
		$res = $ilDB->query( "SELECT od.obj_id, od.title, oref.ref_id "
						    ."  FROM object_data od"
						    ."  JOIN object_reference oref ON oref.obj_id = od.obj_id "
						    ." WHERE od.type = 'grp'");
		while ($rec = $ilDB->fetchAssoc($res)) {
			if ($ilAccess->checkAccess("visible", "", $rec["ref_id"], "grp", $rec["obj_id"])) {
				$a_options[$rec["obj_id"]] = $rec["title"];
			}
		}
	}
	
	/**
	 * Assign members from group (status form)
	 */
	public function assignMembersFromGroup()
	{
		global $tpl, $lng, $ilCtrl;
		
		$grp_obj_id = $_REQUEST["grp"];
		if($grp_obj_id)
		{
			$ilCtrl->setParameter($this, "grp", $grp_obj_id);
			$ilCtrl->setParameter($this, "fsrch", true);
			
			require_once "./Modules/Group/classes/class.ilGroupParticipants.php";
			$members = ilGroupParticipants::_getInstanceByObjId($grp_obj_id);
			$user_ids = $members->getMembers();
			if(sizeof($user_ids))
			{
				$this->setTabs("listBookings");
				
				require_once "Services/CourseBooking/classes/class.ilCourseBookingSearchResultsTableGUI.php";
				$tbl = new ilCourseBookingSearchResultsTableGUI($this, "assignMembersFromGroup", $user_ids, ilCourseBooking::STATUS_BOOKED);

				$tbl->setFormAction($ilCtrl->getFormAction($this, "assignMembersConfirm"));
				$tbl->addCommandButton("assignMembersConfirm", $lng->txt("add"));
				$tbl->addCommandButton("listBookings", $lng->txt("cancel"));
						
				return $tpl->setContent($tbl->getHTML());
			}
			else
			{
				ilUtil::sendFailure($lng->txt("crsbook_admin_group_has_no_members"));
			}
		}
		
		$form = $this->initAddGroupForm();
		if(!$grp_obj_id)
		{
			$form->checkInput();
		}
		$form->setValuesByPost();
		$this->addGroup($form);
	}
	
	
	//
	// ORG UNIT
	// 
	
	/**
	 * Add org unit members
	 * 
	 * @param ilPropertyFormGUI $a_form
	 */	
	protected function addOrgUnit(ilPropertyFormGUI $a_form = null)
	{
		global $tpl;
		
		$this->setTabs("listBookings");
		
		if(!$a_form)
		{
			$a_form = $this->initAddOrgUnitForm();
		}
		$tpl->setContent($a_form->getHTML());
	}
	
	/**
	 * Init form to add members from org unit
	 * 
	 * @return ilPropertyFormGUI
	 */
	protected function initAddOrgUnitForm()
	{
		global $ilCtrl, $lng;
		
		require_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this, "assignMembersFromOrgUnit"));
		$form->setTitle($lng->txt("crsbook_admin_add_org_unit"));
		
		
		require_once "Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php";
		$ou_tree = ilObjOrgUnitTree::_getInstance();		
				
		$book_rcrsv = $ou_tree->getOrgusWhereUserHasPermissionForOperation("book_employees_rcrsv");
		$book = $ou_tree->getOrgusWhereUserHasPermissionForOperation("book_employees");		
		
		$ou_ids = array();
		foreach($ou_tree->getAllChildren(ilObjOrgUnit::getRootOrgRefId()) as $ou_ref_id)
		{				
			if(in_array($ou_ref_id, $book) || in_array($ou_ref_id, $book_rcrsv))
			{
				$ou_ids[] = $ou_ref_id;
			}
			else
			{
				$parent = $ou_tree->getParent($ou_ref_id);
				while($parent)
				{					
					if(in_array($parent, $book_rcrsv))
					{
						$ou_ids[] = $ou_ref_id;
						break;
					}					
					$parent = $ou_tree->getParent($parent);
				}				
			}			
		}
		
		if(!sizeof($ou_ids))
		{
			//gev-patch start
			ilUtil::sendFailure($lng->txt("gev_crs_book_no_perm_at_any_org_units"), true);
			//gev-patch end
			$ilCtrl->redirect($this, "listBookings");
		}
		
		$titles = ilCourseBookingHelper::getOrgUnitTitles($ou_ids);
		
		$options = array("" => $lng->txt("please_select"));
		foreach($ou_ids as $ou_id)
		{
			$options[$ou_id] = $titles[$ou_id];
		}
		asort($options);
		
		$ou = new ilSelectInputGUI($lng->txt("obj_orgu"), "org");
		$ou->setRequired(true);
		$ou->setOptions($options);
		$form->addItem($ou);
		
		$recur = new ilCheckboxInputGUI($lng->txt("crsbook_admin_org_add_recursive"), "subs");
		$form->addItem($recur);

		$form->addCommandButton("assignMembersFromOrgUnit", $lng->txt("continue"));
		$form->addCommandButton("listBookings", $lng->txt("cancel"));
		
		return $form;
	}
	
	/**
	 * Assign members from group (status form)
	 */
	public function assignMembersFromOrgUnit()
	{
		global $tpl, $lng, $ilCtrl;
		
		$org_ref_id = $_REQUEST["org"];		
		if($org_ref_id)
		{
			$org_subs = $_REQUEST["subs"];
			
			$ilCtrl->setParameter($this, "org", $org_ref_id);
			$ilCtrl->setParameter($this, "subs", $org_subs);
			$ilCtrl->setParameter($this, "fsrch", true);
			
			require_once "Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php";
			$ou_tree = ilObjOrgUnitTree::_getInstance();	
			$user_ids = $ou_tree->getEmployees($org_ref_id, $org_subs);	
			$user_ids = array_unique(array_merge($user_ids, $ou_tree->getSuperiors($org_ref_id, $org_subs)));	

			if(sizeof($user_ids))
			{								
				$this->setTabs("listBookings");
				
				require_once "Services/CourseBooking/classes/class.ilCourseBookingSearchResultsTableGUI.php";
				$tbl = new ilCourseBookingSearchResultsTableGUI($this, "assignMembersFromOrgUnit", $user_ids, ilCourseBooking::STATUS_BOOKED);
														
				$tbl->setFormAction($ilCtrl->getFormAction($this, "assignMembersConfirm"));
				$tbl->addCommandButton("assignMembersConfirm", $lng->txt("add"));
				$tbl->addCommandButton("listBookings", $lng->txt("cancel"));
						
				return $tpl->setContent($tbl->getHTML());
			}
			else
			{
				ilUtil::sendFailure($lng->txt("crsbook_admin_org_unit_has_no_members"));
			}
		}
		
		$form = $this->initAddOrgUnitForm();		
		if(!$org_ref_id)
		{
			$form->checkInput();
		}			
		$form->setValuesByPost();
		$this->addOrgUnit($form);
	}
	
	
	//
	// BOOKING
	//
	
	/**
	 * Redirect to search result
	 * 
	 */
	protected function cancelToSearch()
	{
		global $ilCtrl;
		$ilCtrl->redirectByClass("ilRepositorySearchGUI", "showSearchResults");
	}
	
	/**
	 * Confirm members assignment	 	
	 * 
	 * @param array $a_user_ids 
	 * @param int $a_status 
	 * @return bool
	 */
	public function assignMembersConfirm(array $a_user_ids = null, $a_status = null)
	{
		global $ilCtrl, $lng, $tpl;
		
		// coming from user search book which has a status row for each user
		$from_search = false;
		if($_POST["usr_srch"])
		{
			if(!$_REQUEST["fsrch"])
			{
				$from_search = true;
			}
			
			$a_user_ids = $a_status = array();		
			foreach($_POST["usr_srch"] as $user_id => $status)
			{
				if($status) 
				{
					$a_user_ids[] = $user_id;
					$a_status[$user_id] = $status;
				}
			}
			if(!sizeof($a_user_ids))
			{
				ilUtil::sendFailure($lng->txt("crs_no_users_selected"), true);
				$ilCtrl->redirect($this, "listBookings");
			}
		}
		if(!count($a_user_ids))
		{
			ilUtil::sendFailure($lng->txt("crs_no_users_selected"), true);
			return false;
		}
		
		// see ilObjCourseGUI::assignMembers()
				
		include_once "./Services/CourseBooking/classes/class.ilCourseBookingHelper.php";	
		include_once "./Services/CourseBooking/classes/class.ilUserCourseBookings.php";	
		$bookings = ilCourseBookings::getInstance($this->getCourse());
		$helper = ilCourseBookingHelper::getInstance($this->getCourse());
		
		ilDatePresentation::setUseRelativeDates(false);
	
		$status = array();
		$valid = 0;
		foreach($a_user_ids as $user_id)
		{
			// should never happen as we only get valid user ids from ilRepositorySearchGUI
			if(!ilObjectFactory::getInstanceByObjId($user_id, false))
			{				
				// message is given by ilUserUtil
				$status[$user_id] = "";
				continue;				
			}
			
			if($bookings->isMemberOrWaiting($user_id))
			{
				if(sizeof($a_user_ids) > 1)
				{
					$status[$user_id] = $lng->txt("crsbook_admin_assign_already_assigned");
				}
				else
				{
					ilUtil::sendFailure($lng->txt("crsbook_admin_assign_already_assigned"), true);
					$ilCtrl->redirect($this, "listBookings");
				}				
				continue;
			}
			
			if($bookings->isCancelled($user_id))
			{
				$status[$user_id] = $lng->txt("crsbook_admin_assign_cancelled_user");
				continue;
			}			
			
			if(!$helper->isBookable($user_id))
			{
				$status[$user_id] = $lng->txt("crsbook_admin_assign_not_bookable_user");
				continue;
			}
			
			$user_bookings = ilUserCourseBookings::getInstance($user_id);	
			$course_start = $helper->getCourseStart();
			$course_end = $helper->getCourseEnd();
			
			if ($course_start && $course_end) {
				$overlapping_courses = $user_bookings->getCoursesDuring(
					$helper->getCourseStart(), $helper->getCourseEnd());
			}
			else {
				$overlapping_courses = null;
			}
			if($overlapping_courses)
			{
				$parts = array();
				foreach($overlapping_courses as $course)
				{
					$parts[] = $course["title"]. 
						" (".ilDatePresentation::formatDate($course["start"]).
						" - ".ilDatePresentation::formatDate($course["end"]).")";
				}
				$status[$user_id] = $lng->txt("crsbook_admin_assign_overlapping_user").
					": ".implode(", ", $parts);
				continue;
			}
					
			$status[$user_id] = $lng->txt("ok");
			$valid++;
		}
		
		// all users are valid - no need for confirmation
		if($valid == sizeof($status))
		{
			return $this->assignMembers($a_user_ids, $a_status);
		}
		
		
		// confirmation 
		
		include_once "./Services/User/classes/class.ilUserUtil.php";
		include_once "./Services/Utilities/classes/class.ilConfirmationGUI.php";
		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($ilCtrl->getFormAction($this, "assignMembers"));
		$confirm->setHeaderText($lng->txt("crsbook_admin_assign_confirm"));
		$confirm->setConfirm($lng->txt("confirm"), "assignMembers");
		$confirm->setCancel($lng->txt("cancel"), $from_search ? "cancelToSearch" : "listBookings");		
				
		if(is_array($a_status))
		{
			foreach($a_status as $user_id => $user_status)
			{
				$confirm->addHiddenItem("bkst[".$user_id."]", $user_status);
			}
		}
		else
		{
			$confirm->addHiddenItem("bkst", $a_status);
		}
		
		foreach($status as $user_id => $message)
		{			
			$confirm->addItem("user_ids[]",
				$user_id,
				ilUserUtil::getNamePresentation($user_id, false, false, "", true).
				": ".$message
			);
		}
		
		$tpl->setContent($confirm->getHTML());	
		return true;
	}
	
	/**
	 * Assign members
	 */
	protected function assignMembers($a_user_ids = null, $a_status = null)
	{			
		global $ilCtrl, $lng;
		
		//gev-patch start
		$ilLog->write("enter ilCourseBookingAdminGUI::assignMembers");
		$ilLog->write("param user_ids:");
		$ilLog->dump($user_ids);
		$ilLog->write("param status");
		$ilLog->dump($status);

		if(!$this->getPermissions()->bookCourseForOthers())
		{
			$ilCtrl->redirect($this, "listBookings");
		}
		
		if(!$a_user_ids)
		{
			$a_user_ids = $_REQUEST["user_ids"];
		}
		if(!$a_status)
		{
			$a_status = $_REQUEST["bkst"];
		}
		if(!$a_status)
		{
			$a_status = ilCourseBooking::STATUS_BOOKED;
		}
		if(!count($a_user_ids))
		{
			ilUtil::sendFailure($lng->txt("crs_no_users_selected"), true);
			$ilCtrl->redirect($this, "listBookings");
		}
		
		
		// see ilObjCourseGUI::assignMembers()
		include_once "./Services/CourseBooking/classes/class.ilCourseBookingHelper.php";	
		$helper = ilCourseBookingHelper::getInstance($this->getCourse());
		
		include_once "./Modules/Forum/classes/class.ilForumNotification.php";				
		$bookings = ilCourseBookings::getInstance($this->getCourse());		
		$members_obj = $this->getCourse()->getMembersObject();
	
		$added_users = 0;
		foreach($a_user_ids as $user_id)
		{
			if(!ilObjectFactory::getInstanceByObjId($user_id, false))
			{								
				continue;
			}
			
			if(!$helper->isBookable($user_id))
			{
				continue;
			}
			
			/* #29
			if($bookings->isMemberOrWaiting($user_id))
			{					
				continue;
			}
			*/
			
			if(is_array($a_status))
			{
				$user_status = (isset($a_status[$user_id]))
					? $a_status[$user_id]
					: ilCourseBooking::STATUS_BOOKED;
			}
			else
			{
				$user_status = $a_status;
			}
			
			if($user_status == ilCourseBooking::STATUS_BOOKED)
			{
				// nothing to do
				if($bookings->isMember($user_id))
				{
					continue;
				}

				//gev-patch start
				$this->gLog->write("####################");
				$this->gLog->write("Start booking ".$user_id);
				$this->gLog->write("####################");
				//gev-patch end

				if($bookings->bookCourse($user_id))
				{
					// gev-patch start
					$this->gLog->write("####################");
					$this->gLog->write("Booking Success ".$user_id);
					$this->gLog->write("####################");
					require_once("Services/GEV/Mailing/classes/class.gevCrsAdditionalMailSettings.php");
					$addMailSettings = new gevCrsAdditionalMailSettings($this->getCourse()->getId());
					
					if(!$addMailSettings->getSuppressMails()) {
						require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
						$automails = new gevCrsAutoMails($this->getCourse()->getId());
						
						require_once "Services/GEV/Utils/classes/class.gevCourseUtils.php";
						$crs_utils = gevCourseUtils::getInstance($this->getCourse()->getId());

						require_once "Services/GEV/Mailing/classes/class.gevDeadlineMailingJob.php";
						$deadline_job_ran = gevDeadlineMailingJob::isMailSend($this->getCourse()->getId(), "invitation");

						if(!$crs_utils->isDecentralTraining() && !$crs_utils->isSelflearning()) {
							$automails->sendDeferred("admin_booking_to_booked", array($user_id));
						}
						
						$days_before_course_start = $addMailSettings->getInvitationMailingDate();
						$date = $crs_utils->getStartDate();
						$now = new ilDate(date("Y-m-d"), IL_CAL_DATE);
						if ($date && !$crs_utils->isSelflearning()) {
							$date_d = $date->get(IL_CAL_DATE);
							$now_d = $now->get(IL_CAL_DATE);
							
							// Implementation of #1623: send invitations directly
							// when user is booked at the day where the course starts.
							if ($now_d == $date_d) {
								$automails->send("invitation", array($user_id));
							}
							else {
								$date->increment(IL_CAL_DAY, -1 * $days_before_course_start);
								$date_unix = $date->get(IL_CAL_UNIX);
								$now_unix = $now->get(IL_CAL_UNIX);

								if($now_unix > $date_unix && $deadline_job_ran) {
									$automails->sendDeferred("invitation", array($user_id));
								}
							}
						}
					}
					
					$this->setDefaultAccomodations($user_id);
					// gev-patch end
					
					// :TODO: needed?
					$members_obj->sendNotification($members_obj->NOTIFY_ACCEPT_USER, $user_id);
					ilForumNotification::checkForumsExistsInsert($this->getCourse()->getRefId(), $user_id);
					$this->getCourse()->checkLPStatusSync($user_id);
				}
			}
			else
			{
				// nothing to do
				if($bookings->isWaiting($user_id))
				{
					continue;
				}
				$bookings->putOnWaitingList($user_id);
				// gev-patch start
				require_once("Services/GEV/Mailing/classes/class.gevCrsAdditionalMailSettings.php");
				$addMailSettings = new gevCrsAdditionalMailSettings($this->getCourse()->getId());
					
				if(!$addMailSettings->getSuppressMails()) {
					require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
					$automails = new gevCrsAutoMails($this->getCourse()->getId());
					$automails->sendDeferred("admin_booking_to_waiting", array($user_id));
				}
				
				$this->setDefaultAccomodations($user_id);
				// gev-patch end
			}
			
			$added_users++;
		}
		
		if($added_users)
		{
			ilUtil::sendSuccess($lng->txt("crs_users_added"), true);
			unset($_SESSION["crs_search_str"]);
			unset($_SESSION["crs_search_for"]);
			unset($_SESSION['crs_usr_search_result']);

			// $this->checkLicenses(true);
		}
		$ilLog->write("leave ilCourseBookingAdminGUI::assignMembers");
		$ilCtrl->redirect($this, "listBookings");
	}
	
	
	// gev-patch start
	protected function setDefaultAccomodations($a_user_id) {
		require_once("Services/Accomodations/classes/class.ilAccomodations.php");
		
		$accomodations = ilAccomodations::getInstance($this->getCourse());
		$start = $accomodations->getCourseStart();
		$end = $accomodations->getCourseEnd();

		if ($start && $end) {
			$user_nights = array();
			while (ilDate::_before($start, $end)) {
				$user_nights[] = new ilDate($start->get(IL_CAL_DATE), IL_CAL_DATE);
				$start->increment(IL_CAL_DAY, 1);
			}

			$accomodations->setAccomodationsOfUser($a_user_id, $user_nights);
		}
	}
	// gev-patch end
	
	
	//
	// USER ACTIONS
	// 
	
	/**
	 * Check if any user action is currently possible
	 * 
	 * @param int $a_status
	 * @return int
	 */
	protected function isUserActionPossible($a_status)
	{
		$user_id = (int)$_REQUEST["user_id"];
		if ($user_id &&
			!ilCourseBookingHelper::getInstance($this->getCourse())->isUltimateBookingDeadlineReached())
		{
			if(
				(in_array($a_status, array(ilCourseBooking::STATUS_BOOKED, ilCourseBooking::STATUS_WAITING)) &&
					$this->getPermissions()->bookCourseForOthers()) ||
				(in_array($a_status, array(ilCourseBooking::STATUS_CANCELLED_WITHOUT_COSTS, ilCourseBooking::STATUS_CANCELLED_WITH_COSTS)) &&
					$this->getPermissions()->cancelCourseForOthers()))
			{
				return $user_id;
			}
		}
	}
	
	/**
	 * Confirm (any) user action
	 */
	protected function confirmUserAction()
	{
		global $ilCtrl, $lng, $tpl;
		
		$user_status = (int)$_GET["user_status"];
		$user_id = $this->isUserActionPossible($user_status);
		
		if(!$user_id || !$user_status)
		{
			$ilCtrl->redirect($this, "listBookings");
		}
								
		$map = array(
			ilCourseBooking::STATUS_BOOKED => "Book"
			,ilCourseBooking::STATUS_WAITING => "ToWaitingList"
			,ilCourseBooking::STATUS_CANCELLED_WITHOUT_COSTS => "CancelWithoutCosts"
			,ilCourseBooking::STATUS_CANCELLED_WITH_COSTS => "CancelWithCosts"
		);		
		$cmd = "userAction".$map[$user_status];
		
		include_once "./Services/User/classes/class.ilUserUtil.php";
		include_once "./Services/Utilities/classes/class.ilConfirmationGUI.php";
		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($ilCtrl->getFormAction($this, $cmd));
		$confirm->setHeaderText($lng->txt("crsbook_admin_user_action_confirm_".$map[$user_status]));
		$confirm->setConfirm($lng->txt("confirm"), $cmd);
		$confirm->setCancel($lng->txt("cancel"), "listBookings");
		
		$confirm->addItem("user_id",
				$user_id,
				ilUserUtil::getNamePresentation($user_id, false, false, "", true)
			);
		
		$tpl->setContent($confirm->getHTML());
	}
	
	/**
	 * Add user as participant
	 */
	protected function userActionBook()
	{
		global $ilCtrl, $lng;
		
		$user_id = $this->isUserActionPossible(ilCourseBooking::STATUS_BOOKED);
		if($user_id)
		{		
			$bookings = ilCourseBookings::getInstance($this->getCourse());
			if($bookings->isWaiting($user_id) &&
				$bookings->bookCourse($user_id))
			{
				ilUtil::sendSuccess($lng->txt("crsbook_admin_user_action_done"), true);
				// gev-patch start
				require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
				$automails = new gevCrsAutoMails($this->getCourse()->getId());
				require_once "Services/GEV/Utils/classes/class.gevCourseUtils.php";
				$crs_utils = gevCourseUtils::getInstance($this->getCourse()->getId());
				if (!$crs_utils->isDecentralTraining() && !$crs_utils->isSelflearning()) {
					$automails->send("admin_booking_to_booked", array($user_id));
					$automails->send("invitation", array($user_id));
				}
				// gev-patch end
			}
		}
		
		$ilCtrl->redirect($this, "listBookings");
	}
	
	/**
	 * Put user on waiting list
	 */
	protected function userActionToWaitingList()
	{
		global $ilCtrl, $lng;
		
		$user_id = $this->isUserActionPossible(ilCourseBooking::STATUS_WAITING);
		if($user_id)
		{		
			$bookings = ilCourseBookings::getInstance($this->getCourse());
			if($bookings->isMember($user_id) && 			
				$bookings->putOnWaitingList($user_id))
			{
				ilUtil::sendSuccess($lng->txt("crsbook_admin_user_action_done"), true);
				// gev-patch start
				require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
				$automails = new gevCrsAutoMails($this->getCourse()->getId());
				$automails->sendDeferred("admin_booking_to_waiting", array($user_id));
				// gev-patch end
			}
		}
		
		$ilCtrl->redirect($this, "listBookings");
	}
	
	/**
	 * Cancel user without costs
	 */
	protected function userActionCancelWithoutCosts()
	{		
		global $ilCtrl, $lng, $ilLog;
		
		$user_id = $this->isUserActionPossible(ilCourseBooking::STATUS_CANCELLED_WITHOUT_COSTS);
		if($user_id)
		{								
			$bookings = ilCourseBookings::getInstance($this->getCourse());
			// gev-patch start
			$old_status = $bookings->getUserStatus($user_id);
			// gev-patch end
			if($bookings->cancelWithoutCosts($user_id))
			{
				// gev-patch start
				require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
				$automails = new gevCrsAutoMails($this->getCourse()->getId());
				if ($old_status == ilCourseBooking::STATUS_BOOKED) {
					
					$ilLog->write("ilCourseBookingAdminGUI::userActionCancelWithoutCosts:"
						." send cancellation (admin_cancel_booked_to_cancelled_without_costs)"
						." course=" . $this->getCourse()->getId()
						." user=" . $user_id
					);

					$automails->sendDeferred("admin_cancel_booked_to_cancelled_without_costs", array($user_id));
				}
				else if ($old_status == ilCourseBooking::STATUS_WAITING) {
					$ilLog->write("ilCourseBookingAdminGUI::userActionCancelWithoutCosts:"
						." send cancellation (admin_cancel_waiting_to_cancelled_without_costs)"
						." course=" . $this->getCourse()->getId()
						." user=" . $user_id
					);

					$automails->sendDeferred("admin_cancel_waiting_to_cancelled_without_costs", array($user_id));
				}
				// gev-patch end
				ilUtil::sendSuccess($lng->txt("crsbook_admin_user_action_done"), true);
			}
		}
		
		$ilCtrl->redirect($this, "listBookings");
	}
	
	/**
	 * Cancel user with costs
	 */
	protected function userActionCancelWithCosts()
	{
		global $ilCtrl, $lng, $ilLog;
		
		$user_id = $this->isUserActionPossible(ilCourseBooking::STATUS_CANCELLED_WITH_COSTS);
		if($user_id)
		{								
			$bookings = ilCourseBookings::getInstance($this->getCourse());
			if($bookings->cancelWithCosts($user_id))
			{
				// gev-patch start
				$ilLog->write("ilCourseBookingAdminGUI::userActionCancelWithCosts:"
					." send cancellation (admin_cancel_booked_to_cancelled_with_costs)"
					." course=" . $this->getCourse()->getId()
					." user=" . $user_id
				);

				require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
				$automails = new gevCrsAutoMails($this->getCourse()->getId());
				$automails->sendDeferred("admin_cancel_booked_to_cancelled_with_costs", array($user_id));
				// gev-patch end
				ilUtil::sendSuccess($lng->txt("crsbook_admin_user_action_done"), true);
			}
		}
		
		$ilCtrl->redirect($this, "listBookings");
	}
	
	
	//
	// CANCELLATION
	//
	
	/**
	 * List course cancellation
	 */
	protected function listCancellations()
	{		
		global $tpl;
		
		$this->setTabs("listCancellations");
		
		require_once "Services/CourseBooking/classes/class.ilCourseBookingMembersTableGUI.php";
		$tbl = new ilCourseBookingMembersTableGUI($this, "listCancellations", $this->getCourse());
		return $tpl->setContent($tbl->getHTML());		
	}
}
