<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Desktop for the Generali
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/MainMenu/classes/class.ilMainMenuGUI.php");
require_once("Services/UIComponent/GroupedList/classes/class.ilGroupedListGUI.php");
require_once("Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php");

require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

class gevMainMenuGUI extends ilMainMenuGUI {
	const IL_STANDARD_ADMIN = "gev_ilias_admin_menu";

	public function __construct() {
		parent::__construct($a_target, $a_use_start_template);
		
		global $lng, $ilCtrl, $ilAccess, $ilUser;
		
		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->access = &$ilAccess;
		$this->user = &$ilUser;
		$this->userUtils = gevUserUtils::getInstance($this->user->getId());

		$this->lng->loadLanguageModule("gev");
	}

	public function renderMainMenuListEntries($a_tpl, $a_call_get = true) {
		// No Menu during registration.
		if (basename($_SERVER["PHP_SELF"]) == "gev_registration.php") {
			return;
		}
		
		// switch to patch template
		$a_tpl = new ilTemplate("tpl.gev_main_menu_entries.html", true, true, "Services/GEV/Desktop");
		
		// known ref_ids
		$repository = 1;
		$user_mgmt = 7;
		$org_mgmt = 56;
		$mail_mgmt = 12;
		$competence_mgmt = 41;
		$general_settings = 9;
		
		//permissions
		$manage_courses = $this->access->checkAccess("write", "", $repository);
		$manage_users = $this->access->checkAccess("visible", "", $user_mgmt);
		$manage_org_units = $this->access->checkAccess("visible", "", $org_mgmt);
		$manage_mails = $this->access->checkAccess("visible", "", $mail_mgmt);
		$manage_competences = $this->access->checkAccess("visible", "", $competence_mgmt);
		$has_managment_menu = $manage_courses || $manage_users || $manage_org_units || $manage_mails || $manage_competences;
		
		$has_super_admin_menu = $this->access->checkAccess("write", "", $general_settings);
		
		require_once("Services/TEP/classes/class.ilTEPPermissions.php");
		$tep_permissions = ilTEPPermissions::getInstance($this->user->getId());

		$employee_booking = false;
		$my_org_unit = false;
		$tep = $this->userUtils->isAdmin() || $tep_permissions->isTutor();
		$pot_participants = false;
		$apprentices = false;
		
		$has_others_menu = $employee_booking || $my_org_unit || $tep || $pot_participants || $apprentices;
		
		//require_once("Services/GEV/Reports/classes/class.gevReportingPermissions.php");
		//$report_permissions = gevReportingPermissions::getInstance($this->user->getId());

		//$report_permissions->getOrgUnitIdsWhereUserHasRole(array());
		//die();

		$report_permission_attendancebyuser =  $this->userUtils->isAdmin();//$this->userUtils->isSuperior();// || $this->userUtils->isAdmin();
		$has_reporting_menu = $this->userUtils->isAdmin(); //$report_permission_attendancebyuser; // || ....

		$is_trainer = $tep; // $tep_permissions->isTutor();

				
		
		$menu = array( 
			//							single entry?
			//						  		   render entry?
			//										  content
			  "gev_search_menu" => array(true, true, "ilias.php?baseClass=gevDesktopGUI&cmd=toCourseSearch") 
			, "gev_me_menu" => array(false, true, array(
											  //render entry?
  													// url
				  "gev_my_courses" => array(true, "ilias.php?baseClass=gevDesktopGUI&cmdClass=toMyCourses")
				, "gev_edu_bio" => array(false, "NYI!")
				, "gev_my_profile" => array(true, "ilias.php?baseClass=gevDesktopGUI&cmd=toMyProfile")
				, "gev_my_settings" => array(true, "ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToSettings")
				, "gev_my_groups" => array(false, "NYI!")
				, "gev_my_roadmap" => array(false, "NYI!")
				, "gev_my_trainer_ap" => array($is_trainer, "ilias.php?baseClass=gevDesktopGUI&cmd=toMyTrainingsAp")

				))
			, "gev_others_menu" => array(false, $has_others_menu, array(
				  "gev_employee_booking" => array($employee_booking, "NYI!")
				, "gev_my_org_unit" => array($my_org_unit, "NYI!")
				, "gev_tep" => array($tep, "ilias.php?baseClass=ilTEPGUI")
				, "gev_pot_participants" => array($pot_participants, "NYI!")
				, "gev_my_apprentices" => array($apprentices, "NYI!")
				))
			, "gev_process_menu" => array(false, false, array(
				  "gev_apprentice_grant" => array(true, "NYI!")
				, "gev_pot_applicants" => array(true, "NYI!")
				, "gev_spec_course_create" => array(true, "NYI!")
				, "gev_spec_course_approval" => array(true, "NYI!")
				, "gev_spec_course_check" => array(true, "NYI!")
				))
			, "gev_reporting_menu" => array(false, $has_reporting_menu, array(
				  "gev_report_attendance_by_employee" => array($report_permission_attendancebyuser, "ilias.php?baseClass=gevDesktopGUI&cmd=toReportAttendanceByEmployee")
				))
			, "gev_admin_menu" => array(false, $has_managment_menu, array(
				  "gev_course_mgmt" => array($manage_courses, "goto.php?target=root_1")
				, "gev_user_mgmt" => array($manage_users, "ilias.php?baseClass=ilAdministrationGUI&ref_id=7&cmd=jump")
				, "gev_org_mgmt" => array($manage_org_units, "ilias.php?baseClass=ilAdministrationGUI&ref_id=56&cmd=jump")
				, "gev_mail_mgmt" => array($manage_mails, "ilias.php?baseClass=ilAdministrationGUI&ref_id=12&cmd=jump")
				, "gev_competence_mgmt" => array($manage_competences, "ilias.php?baseClass=ilAdministrationGUI&ref_id=41&cmd=jump")
				))
			, self::IL_STANDARD_ADMIN => array(false, $has_super_admin_menu, null)
			);
		

		foreach ($menu as $title => $entry) {
			if (! $entry[1]) {
				continue;
			}
			
			if ($entry[0]) {
				$this->_renderSingleEntry($a_tpl, $title, $entry);
			}
			else{
				$this->_renderDropDownEntry($a_tpl, $title, $entry);
			}
		}
		
		// Some ILIAS idiosyncracy copied from ilMainMenuGUI.
		if ($a_call_get) {
			return $a_tpl->get();
		}
		
		return "";
	}
	
	protected function _renderSingleEntry($a_tpl, $a_title, $a_entry) {
		$a_tpl->setCurrentBlock("single_entry");
		
		$a_tpl->setVariable("ENTRY_ID", 'id="'.$a_title.'"');
		$this->_setActiveClass($a_tpl, $a_title);
		$a_tpl->setVariable("ENTRY_TARGET", $a_entry[2]);
		$a_tpl->setVariable("ENTRY_TITLE", $this->lng->txt($a_title));
		
		$a_tpl->parseCurrentBlock();
	}
	
	protected function _renderDropDownEntry($a_tpl, $a_title, $a_entry) {
		if ($a_title == self::IL_STANDARD_ADMIN) {
			$this->_renderAdminMenu($a_tpl);
		} 
		else {
			$a_tpl->setCurrentBlock("dropdown_entry");
			
			$trigger_id = $a_title;
			$target_id = $a_title."_ov";
			
			$a_tpl->setVariable("ENTRY_ID", 'id="'.$trigger_id.'"');
			$a_tpl->setVariable("ENTRY_ID_OV", 'id="'.$target_id.'"');
			$this->_setActiveClass($a_tpl, $a_title);
			$a_tpl->setVariable("ENTRY_TITLE", $this->lng->txt($a_title));
			
			$a_tpl->setVariable("ENTRY_CONT", $this->_renderDropDown($a_entry[2]));
			
			$ov = new ilOverlayGUI($target_id);
			$ov->setTrigger($trigger_id);
			$ov->setAnchor($trigger_id);
			$ov->setAutoHide(false);
			$ov->add();
			
			$a_tpl->parseCurrentBlock();
		}
	}
	
	protected function _renderDropDown($a_entries) {
		$gl = new ilGroupedListGUI();
		
		foreach($a_entries as $title => $entry) {
			if ($entry === null) {
				$gl->addSeperator();
			}
			else if ($entry[0]) {
				$gl->addEntry($this->lng->txt($title), $entry[1], "_top");
			}
		}
		
		return $gl->getHTML();
	}
	
	protected function _renderAdminMenu($a_tpl) {
		$a_tpl->setCurrentBlock("admin_entry");
		require_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
		
		$selection = new ilAdvancedSelectionListGUI();
		
		$selection->setSelectionHeaderSpanClass("MMSpan");
		$selection->setItemLinkClass("small");
		$selection->setUseImages(false);

		$selection->setListTitle($this->lng->txt(self::IL_STANDARD_ADMIN));
		$selection->setId(self::IL_STANDARD_ADMIN);
		$selection->setAsynch(true);
		$selection->setAsynchUrl("ilias.php?baseClass=ilAdministrationGUI&cmd=getDropDown&cmdMode=asynch");
		
		$a_tpl->setVariable("ADMIN_DROP_DOWN", $selection->getHTML());
		$a_tpl->parseCurrentBlock();
	}
	
	protected function _setActiveClass($a_tpl, $a_title) {
		if($this->active == $a_title) {
			$a_tpl->setVariable("MM_CLASS", "MMActive");
		}
		else {
			$a_tpl->setVariable("MM_CLASS", "MMInactive");
		}
	}
}

?>