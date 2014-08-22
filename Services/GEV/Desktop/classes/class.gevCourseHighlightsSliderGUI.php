<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Slider for advertised Courses for Generali.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/CaTUIComponents/classes/class.catSliderGUI.php");
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/Calendar/classes/class.ilDatePresentation.php");

class gevCourseHighlightsSliderGUI extends catSliderGUI {
	public function __construct($a_user_id = null) {
		parent::__construct();
		
		global $lng, $ilCtrl, $ilUser;
		
		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		
		if ($a_user_id === null) {
			$this->user_id = $ilUser->getId();
		}
		else {
			$this->user_id = $a_user_id;
		}
		
		$this->user_utils = gevUserUtils::getInstance($this->user_id);
		
		$this->setTemplate("tpl.gev_course_highlights_slider.html", "Services/GEV/Desktop");
		$this->setSliderId("CourseHighlightsSlider");
		$this->highlight_ids = $this->user_utils->getCourseHighlights();
	}
	
	public function countHighlights() {
		return count($this->highlight_ids);
	}
	
	public function renderSlides() {
		$crs_amd =
			array( gevSettings::CRS_AMD_START_DATE => "start_date"
				 , gevSettings::CRS_AMD_END_DATE => "end_date"
				 , gevSettings::CRS_AMD_TYPE => "type"
				 , gevSettings::CRS_AMD_VENUE => "venue"
				 , gevSettings::CRS_AMD_FEE => "fee"
				 , gevSettings::CRS_AMD_CREDIT_POINTS => "credit_points"
				 );
		
		$crs_data = gevAMDUtils::getInstance()->getTable($this->highlight_ids, $crs_amd);
		
		$ret = "";
		
		foreach ($crs_data as $crs) {
			$tpl = new ilTemplate("tpl.gev_course_highlight_slide.html", false, false, "Services/GEV/Desktop");
			$tpl->setVariable("CREDIT_POINT", $crs["credit_points"]);
			$tpl->setVariable("TYPE", $crs["type"]);

			$org_title = $crs["title"];
			if(strlen($org_title) > 64) {
				$title = '<a title="' .$org_title .'">';
				$title .= substr($org_title, 0, 64);
				$title .= ' [...]</a>';
			}else{
				$title = $org_title;
			}
			$tpl->setVariable("TITLE", $title);

			if ($crs["start_date"] && $crs["end_date"]) {
				$tpl->setCurrentBlock("date");
				//$tpl->setVariable("DATE", ilDatePresentation::formatPeriod($crs["start_date"], $crs["end_date"]));
				$dat = ilDatePresentation::formatPeriod($crs["start_date"], $crs["end_date"]);
				$dat = str_replace('-', '-<br>', $dat);
				$tpl->setVariable("DATE", $dat);

				$tpl->parseCurrentBlock();
			}
			if ($crs["venue"]) {
				$tpl->setCurrentBlock("venue");
				$tpl->setVariable("VENUE", gevOrgUnitUtils::getInstance($crs["venue"])->getLongTitle());
				$tpl->parseCurrentBlock();
			}
			$tpl->setVariable("FEE", gevCourseUtils::formatFee($crs["fee"])." €");
			$tpl->setVariable("BOOK_ACTION", gevCourseUtils::getBookingLinkTo($crs["obj_id"],$this->user_id)); // TODO: Implement that properly
			$ret .= $tpl->get();
		}
		
		return $ret;
	}
}

?>