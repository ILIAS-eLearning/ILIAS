<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Desktop for the Generali
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @author   Martin Studer <ms@studer-raimann.ch>
* @version	$Id$
*/

require_once("Services/MainMenu/classes/class.ilMainMenuGUI.php");
require_once("Services/UIComponent/GroupedList/classes/class.ilGroupedListGUI.php");
require_once("Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php");
require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitAccess.php");

require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/GEV/WBD/classes/class.gevWBD.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");


class gevMainMenuGUI extends ilMainMenuGUI {
	const IL_STANDARD_ADMIN = "gev_ilias_admin_menu";
	const GEV_REPORTING_MENU = "gev_reporting_menu";

	const HAS_REPORTING_MENU_RECALCULATION_IN_SECS = 60;

	/**
	 * @var  gevUserUtils
	 */
	protected $user_utils = Null;
	/**
	 * @var ilCtrl
	 */
	protected $gCtrl = Null;
	protected $gLng;
	protected $gAccess;
	protected $gUser;

	public function __construct() {
		parent::__construct($a_target, $a_use_start_template);
		
		global $lng, $ilCtrl, $ilAccess, $ilUser;
		
		$this->gLng = &$lng;
		$this->gCtrl = &$ilCtrl;
		$this->gAccess = &$ilAccess;
		$this->gUser = &$ilUser;
		
		if($this->gUser->getId() !== 0) {
			$this->user_utils = gevUserUtils::getInstance($this->gUser->getId());
			$this->wbd = gevWBD::getInstance($this->gUser->getId());
		}

		$this->gLng->loadLanguageModule("gev");
	}

	public function executeCommand() {
		$cmd = $this->gCtrl->getCmd();
		switch ($cmd) {
			case "getReportingMenuDropDown":
				assert($this->gCtrl->isAsynch());
				echo $this->getReportingMenuDropDown();
				die();
			default:
				throw new Exception("gevMainMenuGUI: Unknown Command '$cmd'.");
		}
	}

	public function renderMainMenuListEntries($a_tpl, $a_call_get = true) {
		// No Menu during registration or on makler page
		$basename = basename($_SERVER["PHP_SELF"]);
		if (   $basename == "gev_registration.php"
			|| $basename == "gev_logindata.php"
			|| $basename == "makler.php" ) {
			return "";
		}

		if ($this->gUser->getId() == 0) {
			return "";
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
		$manage_courses = $this->gAccess->checkAccess("write", "", $repository);
		$search_courses = $manage_courses || ($this->user_utils && $this->user_utils->hasRoleIn(array("Admin-Ansicht")));
		$manage_users = $this->gAccess->checkAccess("visible", "", $user_mgmt);
		$manage_org_units = $this->gAccess->checkAccess("visible", "", $org_mgmt);
		$manage_mails = $this->gAccess->checkAccess("visible", "", $mail_mgmt);
		$manage_competences = $this->gAccess->checkAccess("visible", "", $competence_mgmt);
		$has_managment_menu = ($manage_courses || $search_courses || $manage_users || $manage_org_units || $manage_mails || $manage_competences)
							&& ($this->user_utils && !$this->user_utils->hasRoleIn(array("OD/BD", "UA", "ID FK", "DBV UVG", "HA 84", "OD", "FD", "BD")))
							;
		
		$has_super_admin_menu = $this->gAccess->checkAccess("write", "", $general_settings);
		
		require_once("Services/TEP/classes/class.ilTEPPermissions.php");

		$employee_booking = ($this->user_utils && $this->user_utils->canViewEmployeeBookings());
		require_once("Services/GEV/Utils/classes/class.gevHAUtils.php");
		$can_create_ha_unit = ($this->user_utils && ($this->user_utils->hasRoleIn(array("HA 84")) && !gevHAUtils::getInstance()->hasHAUnit($this->user_utils->getId())));
		$local_user_admin = ($this->user_utils && $this->user_utils->isSuperior()); //Local User Administration Permission

		$has_others_menu = $employee_booking || $can_create_ha_unit;
		$could_do_wbd_registration = $this->wbd && $this->wbd->hasWBDRelevantRole() && !$this->wbd->getWBDBWVId() && ($this->wbd->getNextWBDAction() == gevWBD::USR_WBD_NEXT_ACTION_NOTHING);

		$manage_course_block_units = ($this->user_utils && !$this->user_utils->notEditBuildingBlocks());
		
		//Für den Anfang sollen das nur Administratoren sehen
		$is_training_manager = ($this->user_utils && $this->user_utils->isTrainingManagerOnAnyCourse());

		$view_all_assessments = ($this->user_utils && $this->user_utils->isAdmin());

		//get all OrgUnits of superior
		$arr_org_units_of_superior = $this->user_utils ? $this->user_utils->getOrgUnitsWhereUserIsDirectSuperior() : array();
		$arr_local_user_admin_links = array();
		if($arr_org_units_of_superior) {
			foreach($arr_org_units_of_superior as $arr_org_unit_of_superior) {
				if (ilObjOrgUnitAccess::_checkAccessAdministrateUsers($arr_org_unit_of_superior['ref_id'])) {
					$this->gCtrl->setParameterByClass("ilLocalUserGUI", "ref_id", $arr_org_unit_of_superior['ref_id']);
					$arr_local_user_admin_links[$arr_org_unit_of_superior['ref_id']]['title'] = ilObject::_lookupTitle($arr_org_unit_of_superior['obj_id']);
					$arr_local_user_admin_links[$arr_org_unit_of_superior['ref_id']]['url'] = $this->gCtrl->getLinkTargetByClass(array("ilAdministrationGUI","ilObjOrgUnitGUI","ilLocalUserGUI"), "index");
				}
			}
		}

		$main_menue_permissions = array("manage_courses"=>$manage_courses
									,"search_courses"=>$search_courses
									,"manage_users"=>$manage_users
									,"manage_org_units"=>$manage_org_units
									,"manage_mails"=>$manage_mails
									,"manage_course_block_units"=>$manage_course_block_units);
		
		$menu = array( 
			//single entry?
			//render entry?
			//content
			//link title
			  "gev_search_menu" => array(true, true, "ilias.php?baseClass=gevDesktopGUI&cmd=toCourseSearch",$this->gLng->txt("gev_search_menu"), $this->gLng->txt("gev_search_menu"))
			, "gev_me_menu" => array(true, true, "ilias.php?baseClass=gevDesktopGUI&cmd=toMyCourses", $this->gLng->txt("gev_me_menu"))
			, "gev_others_menu" => array(false, $has_others_menu, array(
				  "gev_employee_booking" => array($employee_booking, "ilias.php?baseClass=gevDesktopGUI&cmd=toEmployeeBookings",$this->gLng->txt("gev_employee_booking"))
				, "gev_create_org_unit" => array($can_create_ha_unit, "ilias.php?baseClass=gevDesktopGUI&cmd=createHAUnit", $this->gLng->txt("gev_create_ha_org_unit"))
				, "gev_my_assessments" => array(true, "ilias.php?baseClass=gevDesktopGUI&cmd=toMyAssessments", $this->gLng->txt("gev_my_assessments"))
				, "gev_all_assessments" => array($view_all_assessments, "ilias.php?baseClass=gevDesktopGUI&cmd=toAllAssessments", $this->gLng->txt("gev_all_assessments"))
				), $this->gLng->txt("gev_others_menu"))
			, self::GEV_REPORTING_MENU => array(false, $this->hasReportingMenu(), null)

			, "gev_admin_menu" => array(false, $has_managment_menu, $this->_getAdminMainMenuEntries($main_menue_permissions), $this->gLng->txt("gev_admin_menu"))
			, self::IL_STANDARD_ADMIN => array(false, $has_super_admin_menu, null)
			);

		//Enhance Menu with Local Useradmin Roles
		if(count($arr_local_user_admin_links) > 0)  {
			foreach($arr_local_user_admin_links as $key => $arr_local_user_admin_link) {
				$menu["gev_others_menu"][2]["gev_my_local_user_admin_".$key] = array(
					$local_user_admin,
					$arr_local_user_admin_link['url'],
					sprintf($this->gLng->txt("gev_my_local_user_admin"), $arr_local_user_admin_link['title'])
					);
			}
		}

		$count = 1;
		foreach ($menu as $id => $entry) {
			if (! $entry[1]) {
				continue;
			}
			
			if ($entry[0]) {
				$this->_renderSingleEntry($a_tpl, $id, $entry, $count);
			}
			else{
				$this->_renderDropDownEntry($a_tpl, $id, $entry, $count);
			}
			$count++;
		}
		
		// Some ILIAS idiosyncracy copied from ilMainMenuGUI.
		if ($a_call_get) {
			return $a_tpl->get();
		}
		
		return "";
	}
	
	protected function _renderSingleEntry($a_tpl, $a_id, $a_entry, $count) {
		$a_tpl->setCurrentBlock("single_entry");
		
		$a_tpl->setVariable("ENTRY_ID", 'id="'.$a_id.'"');
		$a_tpl->setVariable("NUM", $count);
		$this->_setActiveClass($a_tpl, $a_id);
		$a_tpl->setVariable("ENTRY_TARGET", $a_entry[2]);
		$a_tpl->setVariable("ENTRY_TITLE", $a_entry[3]);
		
		$a_tpl->parseCurrentBlock();
	}
	
	protected function _renderDropDownEntry($a_tpl, $a_id, $a_entry, $count) {
		if ($a_id == self::IL_STANDARD_ADMIN) {
			$this->_renderAdminMenu($a_tpl, $count);
		}
		else if ($a_id == self::GEV_REPORTING_MENU) {
			$this->_renderReportingMenu($a_tpl, $count);
		} 
		else {
			
			$trigger_id = $a_id;
			$target_id = $a_id."_ov";
			
			$tpl = new ilTemplate("tpl.gev_main_menu_entry.html", true, true, "Services/GEV/Desktop");
			$tpl->setVariable("ENTRY_ID", 'id="'.$trigger_id.'"');
			$a_tpl->setVariable("NUM", $count);
			$tpl->setVariable("ENTRY_ID_OV", 'id="'.$target_id.'"');
			$this->_setActiveClass($tpl, $a_id);
			$tpl->setVariable("ENTRY_TITLE", $a_entry[3]);
			
			$tpl->setVariable("ENTRY_CONT", $this->getDropDown($a_entry[2])->getHTML());
			
			$ov = new ilOverlayGUI($target_id);
			$ov->setTrigger($trigger_id);
			$ov->setAnchor($trigger_id);
			$ov->setAutoHide(false);
			$ov->add();
			
			$a_tpl->setCurrentBlock("multi_entry");
			$a_tpl->setVariable("NUM", $count);
			$a_tpl->setVariable("CONTENT", $tpl->get());
			$a_tpl->parseCurrentBlock();
		}
	}
	
	protected function _renderAdminMenu($a_tpl, $count) {
		require_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
		
		$selection = new ilAdvancedSelectionListGUI();
		
		$selection->setSelectionHeaderSpanClass("MMSpan");
		$selection->setItemLinkClass("small");
		$selection->setUseImages(false);

		$selection->setListTitle($this->gLng->txt(self::IL_STANDARD_ADMIN));
		$selection->setId(self::IL_STANDARD_ADMIN);
		$selection->setAsynch(true);
		$selection->setAsynchUrl("ilias.php?baseClass=ilAdministrationGUI&cmd=getDropDown&cmdMode=asynch");
		
		$a_tpl->setCurrentBlock("multi_entry");
		$a_tpl->setVariable("NUM", $count);
		$a_tpl->setVariable("CONTENT", $selection->getHTML());
		$a_tpl->parseCurrentBlock();
	}

	protected function _renderReportingMenu($a_tpl, $count) {
		require_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");

		$selection = new ilAdvancedSelectionListGUI();

		$selection->setSelectionHeaderSpanClass("MMSpan");
		$selection->setItemLinkClass("small");
		$selection->setUseImages(false);

		$selection->setListTitle($this->gLng->txt(self::GEV_REPORTING_MENU));
		$selection->setId(self::GEV_REPORTING_MENU);
		$selection->setAsynch(true);
		$selection->setAsynchUrl("ilias.php?baseClass=gevMainMenuGUI&cmd=getReportingMenuDropDown&cmdMode=asynch");

		$a_tpl->setCurrentBlock("multi_entry");
		$a_tpl->setVariable("CONTENT", $selection->getHTML());
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

	protected function getDropDown($a_entries) {
		$gl = new ilGroupedListGUI();
		
		foreach($a_entries as $id => $entry) {
			if ($entry === null) {
				$gl->addSeperator();
			}
			else if ($entry[0]) {
				$gl->addEntry($entry[2], $entry[1], "_top");
			}
		}
		
		return $gl;
	}

	protected function _getAdminMainMenuEntries($main_menue_permissions) {
		$ret = array(
				  "gev_course_mgmt" => array($main_menue_permissions["manage_courses"], "goto.php?target=root_1",$this->gLng->txt("gev_course_mgmt"))
				, "gev_course_mgmt_search" => array($main_menue_permissions["search_courses"], "ilias.php?baseClass=gevDesktopGUI&cmd=toAdmCourseSearch",$this->gLng->txt("gev_course_search_adm"))
				, "gev_user_mgmt" => array($main_menue_permissions["manage_users"], "ilias.php?baseClass=ilAdministrationGUI&ref_id=7&cmd=jump",$this->gLng->txt("gev_user_mgmt"))
				, "gev_org_mgmt" => array($main_menue_permissions["manage_org_units"], "ilias.php?baseClass=ilAdministrationGUI&ref_id=56&cmd=jump",$this->gLng->txt("gev_org_mgmt"))
				, "gev_mail_mgmt" => array($main_menue_permissions["manage_mails"], "ilias.php?baseClass=ilAdministrationGUI&ref_id=12&cmd=jump",$this->gLng->txt("gev_mail_mgmt")));

		$bb_pool = gevUserUtils::getBuildingBlockPoolsTitleUserHasPermissionsTo($this->gUser->getId(), array(gevSettings::USE_BUILDING_BLOCK, "visible"));
		foreach ($bb_pool as $key => $value) {
			$this->gCtrl->setParameterByClass("ilobjbuildingblockpoolgui", "ref_id", gevObjectUtils::getRefId($key));
			$link = $this->gCtrl->getLinkTargetByClass(array("ilObjPluginDispatchGUI","ilobjbuildingblockpoolgui"),"showContent");
			$ret[$value] = array($main_menue_permissions["manage_course_block_units"], $link, $value);
			$this->gCtrl->clearParametersByClass("ilobjbuildingblockpoolgui");
		}

		return $ret;
	}

	protected function getReportingMenuDropDown() {
		require_once("Services/Link/classes/class.ilLink.php");
		require_once("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php");
		$entries = array();

		$visible_repo_reports = ilObjReportBase::getVisibleReportsObjectData($this->gUser);
		foreach ($visible_repo_reports as $info) {
			$entries[] = array(true, ilLink::_getStaticLink($info["ref_id"], $info["type"]),$info["title"]);
		}
		// sort entries by title
		uasort($entries, function ($el1,$el2) {
			return strcasecmp($el1[2],$el2[2]);
		});

		$grouped_list = $this->getDropDown($entries);
		return $grouped_list->getHTML();
	}

	// Stores the info whether a user has a reporting menu in the session of the user to
	// only calculate it once. Will reuse that value on later calls. 
	protected function hasReportingMenu() {
		$has_reporting_menu = ilSession::get("gev_has_reporting_menu");
		$last_permission_calculation = ilSession::get("gev_has_reporting_menu_calculation_ts");
		if ( $has_reporting_menu === null
		||   $last_permission_calculation + self::HAS_REPORTING_MENU_RECALCULATION_IN_SECS < time()) {
			require_once("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php");

			$visible_repo_reports = ilObjReportBase::getVisibleReportsObjectData($this->gUser);

			$has_reporting_menu = (count($visible_repo_reports) > 0);
			ilSession::set("gev_has_reporting_menu", $has_reporting_menu);
			ilSession::set("gev_has_reporting_menu_calculation_ts", time());
		}

		return $has_reporting_menu;
	}
}
