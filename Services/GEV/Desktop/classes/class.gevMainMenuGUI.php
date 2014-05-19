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

class gevMainMenuGUI extends ilMainMenuGUI {
	const IL_STANDARD_ADMIN = "gev_ilias_admin_menu";

	public function __construct() {
		parent::__construct($a_target, $a_use_start_template);
		
		global $lng, $ilCtrl;
		
		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;

		$this->lng->loadLanguageModule("gev");
	}

	public function renderMainMenuListEntries($a_tpl, $a_call_get = true) {
		// switch to patch template
		$a_tpl = new ilTemplate("tpl.gev_main_menu_entries.html", true, true, "Services/GEV/Desktop");
		
		
		$menu = array( 
			//							single entry?
			//						  		   render entry?
			//										  content
			  "gev_search_menu" => array(true, true, $this->ctrl->getLinkTargetByClass("gevCourseSearchGUI"))
			, "gev_me_menu" => array(false, true, array(
											  //render entry?
  													// url
				  "gev_my_courses" => array(true, $this->ctrl->getLinkTargetByClass("gevMyCoursesGUI"))
				, "gev_edu_bio" => array(true, "NYI!")
				, "gev_my_profile" => array(true, "NYI!")
				, "gev_my_groups" => array(true, "NYI!")
				, "gev_my_roadmap" => array(true, "NYI!")
				, "gev_my_trainer_ap" => array(true, "NYI!")
				))
			, "gev_others_menu" => array(false, true, array(
				  "gev_employee_booking" => array(true, "NYI!")
				, "gev_my_org_unit" => array(true, "NYI!")
				, "gev_tep" => array(true, "NYI!")
				, "gev_pot_participants" => array(true, "NYI!")
				, "gev_my_apprentices" => array(true, "NYI!")
				))
			, "gev_process_menu" => array(false, true, array(
				  "gev_apprentice_grant" => array(true, "NYI!")
				, "gev_pot_applicants" => array(true, "NYI!")
				, "gev_spec_course_create" => array(true, "NYI!")
				, "gev_spec_course_approval" => array(true, "NYI!")
				, "gev_spec_course_check" => array(true, "NYI!")
				))
			, "gev_reporting_menu" => array(false, true, array())
			, "gev_admin_menu" => array(false, true, array(
				  "gev_course_mgmt" => array(true, "NYI!")
				, "gev_user_mgmt" => array(true, "NYI!")
				, "gev_org_mgmt" => array(true, "NYI!")
				, "gev_mail_mgmt" => array(true, "NYI!")
				, "gev_competence_mgmt" => array(true, "NYI!")
				))
			, self::IL_STANDARD_ADMIN => array(false, true, null)
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
			else {
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