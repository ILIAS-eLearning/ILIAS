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
			  "gev_search_menu" => array(true, true, "http://www.google.de")
			, "gev_me_menu" => array(false, true, array(
											  //render entry?
  													// url
					"gev_my_courses" => array(true, "http://www.google.de")
				))
			, "gev_others_menu" => array(false, true, array())
			, "gev_process_menu" => array(false, true, array())
			, "gev_reporting_menu" => array(false, true, array())
			, "gev_admin_menu" => array(false, true, array())
			, "gev_ilias_admin_menu" => array(false, true, array())
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
	
	protected function _renderDropDown($a_entries) {
		$gl = new ilGroupedListGUI();
		
		foreach($a_entries as $title => $entry) {
			if ($entry === null) {
				$gl->addSeperator();
			}
			else {
				if ($a_entry[1] === null) {
					$gl->addEntry($this->lng->txt($title), $entry[1], "_top");
				}
				else {
					$gl->addEntry($this->lng->txt($title), $entry[1], "_top");
				}
			}
		}
		
		return $gl->getHTML();
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