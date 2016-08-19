<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Table showing courses of a user for Generali.
*
* @author	Stefan Hecken <stefan.hecken@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/CourseSearch/classes/class.gevCourseSearch.php");
class gevCourseSearchTabGUI {
	

	public function __construct($a_search_options, $a_parent_obj, $a_selected_tab, $crs_srch) {
		global $ilCtrl, $ilUser, $lng;

		$this->selected_tab = $a_selected_tab;
		$this->parent_obj = $a_parent_obj;

		$this->gCtrl = $ilCtrl;
		$this->gLng = $lng;
		$this->crs_srch = $crs_srch;

		$this->tabs = $this->crs_srch->getPossibleTabs();

		$this->tpl = new ilTemplate("tpl.gev_crs_search_tab.html", true, true, "Services/GEV/CourseSearch");
		
		$this->course_counting = $this->crs_srch->getCourseCounting($a_search_options);
	}

	public function render() {
		
		foreach ($this->tabs as $key => $data) {
			list($value) = $data;
			$this->gCtrl->setParameter($this->parent_obj,"active_tab",$key);
			$this->tpl->setCurrentBlock("tab");
			$class = gevCourseSearch::CSS_NOT_SELECTED_TAB;
			if($this->selected_tab == $key) {
				$class = gevCourseSearch::CSS_SELECTED_TAB;
			}

			$this->tpl->setVariable("SELECTED",$class);
			$this->tpl->setVariable("A_SELECTED",$class);
			$this->tpl->setVariable("LINK",$this->gCtrl->getLinkTarget($this->parent_obj));
			$this->tpl->setVariable("TAB_NAME",$this->gLng->txt($value)." (".$this->course_counting[$key].")");
			$this->tpl->parseCurrentBlock();
			$this->gCtrl->setParameter($this->parent_obj,"active_tab",null);
		}

		return $this->tpl->get();
	}
}
