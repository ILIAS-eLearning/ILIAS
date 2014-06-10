<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Advertised Courses for Generali.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
require_once("Services/GEV/Desktop/classes/class.gevCourseHighlightsSliderGUI.php");
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

class gevCourseHighlightsGUI {
	public function __construct($a_target_user_id = null) {
		global $lng, $ilCtrl, $ilUser;
		
		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->user_id = $ilUser->getId();

		if ($a_target_user_id === null) {
			$this->target_user_id = $this->user_id;
		}
		else {
			$this->target_user_id = $a_target_user_id;
		}
		
		$this->hl_slider = new gevCourseHighlightsSliderGUI($this->target_user_id);
	}
	
	public function countHighlights() {
		return $this->hl_slider->countHighlights();
	}
	
	public function render() {
		if ($this->target_user_id == $this->user_id) {
			$hl_title = new catTitleGUI("gev_highlights", "gev_my_highlights_desc", "gev_highlights_header.png");
		}
		else {
			$hl_title = new catTitleGUI("gev_highlights", "gev_theirs_highlights_desc", "gev_highlights_header.png");
		}

		return 	  $hl_title->render()
				. $this->hl_slider->render();
	}
}

?>