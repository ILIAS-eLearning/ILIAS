<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Course seraching GUI for Generali
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

class gevBookingGUI {
	public function __construct() {
		global $lng, $ilCtrl, $tpl, $ilUser, $ilLog;

		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;
		$this->log = &$ilLog;
		$this->current_user = &$ilUser;
		$this->user_id = null;
		$this->user_utils = null;
		$this->crs_id = null;
		$this->crs_utils = null;

		$this->tpl->getStandardTemplate();
	}

	public function executeCommand() {
		$this->initUser();
		$this->initCourse();
		$cmd = $this->ctrl->getCmd();
		
		switch($cmd) {
			case "book":
				$this->$cmd();
				break;
			default:
				$this->log->write("gevBookingGUI: Unknown command '".$cmd."'");
		}
	}
	
	protected function initUser() {
		if ($_GET["user_id"] === null) {
			$this->log->write("gevBookingGUI::initUser: No user id in GET.");
			ilUtil::redirect("index.php");
		}
		
		$this->user_id = $_GET["user_id"];
		$this->user_utils = gevUserUtils::getInstance($this->user_id);
	}
	
	protected function initCourse() {
		if ($_GET["crs_id"] === null) {
			$this->log->write("gevBookingGUI::initCourse: No course id in GET.");
			ilUtil::redirect("index.php");
		}
		
		$this->crs_id = $_GET["crs_id"];
		$this->crs_utils = gevCourseUtils::getInstance($this->crs_id);
	}
	
	protected function book() {
		require_once("Services/CaTUIComponents/classes/class.catPropertyFormGUI.php");
		require_once("Services/Form/classes/class.ilNonEditableValueGUI.php");
		
		$form = new catPropertyFormGUI();
		$form->setTemplate("tpl.gev_booking_form.html", "Services/GEV/Desktop");
		
		$prv = $this->crs_utils->getProvider();
		$ven = $this->crs_utils->getVenue();
		
		$vals = array(
			  array( $this->lng->txt("gev_course_id")
			  	   , true
			  	   , $this->crs_utils->getCustomId()
			  	   )
			, array( $this->lng->txt("gev_target_group")
				   , true
				   , $this->crs_utils->getTargetGroupDesc()
				   )
			, array( $this->lng->txt("gev_targets_and_benefit")
				   , true
				   , $this->crs_utils->getGoals()
				   )
			, array( $this->lng->txt("gev_contents")
				   , true
				   , $this->crs_utils->getContents()
				   )
			, array( $this->lng->txt("gev_course_type")
				   , true
				   , implode(", ", $this->crs_utils->getMethods())
				   )
			, array( $this->lng->txt("appointment")
				   , true
				   , $this->crs_utils->getFormattedAppointment()
				   )
			, array( $this->lng->txt("gev_provider")
				   , $prv
				   , $prv?$prv->getTitle():""
				   )
			, array( $this->lng->txt("gev_venue")
				   , $ven
				   , $ven?$ven->getTitle():""
				   )
			, array( $this->lng->txt("gev_instructor")
				   , true
				   , $this->crs_utils->getMainTrainerName()
				   )
			, array( $this->lng->txt("gev_subscription_end")
				   , true
				   , $this->lng->txt("until") . " ". $this->crs_utils->getFormattedBookingDeadlineDate()
				   )
			, array( $this->lng->txt("gev_free_places")
				   , true
				   , $this->crs_utils->getFreePlaces()
				   )
			, array( $this->lng->txt("gev_training_contact")
				   , true
				   , $this->crs_utils->getMainAdminName()
				   )
			, array( $this->lng->txt("gev_training_fee")
				   , ($this->crs_utils->getFee()?true:false) && $this->user_utils->paysFees()
				   , str_replace(".", ",", "".$this->crs_utils->getFee()) . " &euro;"
				   )
			, array( $this->lng->txt("gev_credit_points")
				   , true
				   , $this->crs_utils->getCreditPoints()
				   )
			, array( $this->lng->txt("precondition")
				   , true
				   , $this->crs_utils->getFormattedPreconditions()
				   )
			);
		
		foreach ($vals as $val) {
			if (!$val[1] or !$val[2]) {
				continue;
			}
		
			$field = new ilNonEditableValueGUI($val[0], "", true);
			$field->setValue($val[2]);
			$form->addItem($field);
		}
		
		// TODO: use to be implemented ilAccomodationsFormGUIHelper here.
		//$overnights = new ilAccomodationsPeriodInputGUI($this->lng->txt("gev_accomodations"), "overnights");
		//$overnights->setPeriod($this->crs_utils->getStartDate(), $this->crs_utils->getEndDate());
		//$form->addItem($overnights);
		
		if ($this->user_id == $this->current_user->getId()) {
			$note = new ilNonEditableValueGUI($this->lng->txt("notice"), "", true);
			$note->setValue($this->lng->txt("gev_booking_note"));
			$form->addItem($note);
		}
		
		$this->tpl->setContent($form->getContent());
	}
}

?>