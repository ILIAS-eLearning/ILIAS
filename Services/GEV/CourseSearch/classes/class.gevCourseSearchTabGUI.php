<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Table showing courses of a user for Generali.
*
* @author	Stefan Hecken <stefan.hecken@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/Desktop/classes/class.gevCourseSearch.php");
class gevCourseSearchTabGUI {
	

	public function __construct($a_search_options, $a_parent_obj, $a_selected_tab) {
		global $ilCtrl, $lng, $ilUser;

		$this->selected_tab = $a_selected_tab;
		$this->parent_obj = $a_parent_obj;

		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->crs_srch = gevCourseSearch::getInstance($ilUser->getId());

		$this->tabs = $this->crs_srch->getPossibleTabs();

		$this->tpl = new ilTemplate("tpl.gev_crs_search_tab.html", true, true, "Services/GEV/Desktop");
		
		$this->course_counting = $this->crs_srch->getCourseCounting($a_search_options);
	}

	public function render() {
		
		foreach ($this->tabs as $key => $value) {
			$this->ctrl->setParameter($this->parent_obj,"active_tab",$key);

			$this->tpl->setCurrentBlock("tab");
			if($this->selected_tab == $key) {
				$this->tpl->setVariable("SELECTED",gevCourseSearch::CSS_SELECTED_TAB);
			}
			
			$this->tpl->setVariable("LINK",$this->ctrl->getLinkTarget($this->parent_obj));
			$this->tpl->setVariable("TAB_NAME",$value." (".$this->course_counting[$key].")");
			$this->tpl->parseCurrentBlock();

			$this->ctrl->clearParameters($this->parent_obj);
		}

		return $this->tpl->get();
	}
}

?>