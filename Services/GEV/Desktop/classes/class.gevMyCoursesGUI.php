<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* My Courses GUI for Generali
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
require_once("Services/CaTUIComponents/classes/class.catHSpacerGUI.php");
require_once("Services/GEV/Desktop/classes/class.gevMyCoursesQuicklinksGUI.php");
require_once("Services/GEV/Desktop/classes/class.gevMyCoursesTableGUI.php");

class gevMyCoursesGUI {
	public function __construct() {
		global $lng, $ilCtrl, $tpl, $ilUser, $ilLog;
		
		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;
		$this->user = &$ilUser;
		$this->log = &$ilLog;
	}
	
	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		if (!$cmd) {
			$cmd = "view";
		}
		
		switch ($cmd) {
			case "view":
			case "cancelBooking":
			case "finalizeCancellation":
			case "noNextCourse":
			case "noLastCourse":
				$cont = $this->$cmd();
				break;
			default:
				$this->log->write("gevMyCoursesGUI: Unknown command '".$cmd."'");
		}
		
		if ($cont) {
			$this->tpl->setContent($cont);
		}
	}
	
	public function view() {
		return $this->render();
	}
	
	public function render() {
		$qls = new gevMyCoursesQuicklinksGUI();
		$qls_out = $qls->render();
		
		$spacer = new catHSpacerGUI();
		$spacer_out = $spacer->render();
		
		$crss = new gevCoursesTableGUI($this->user->getId(), $this);
		$crss_out = $crss->getHTML();
		
		return ($qls_out
			   . $spacer_out
			   . $crss_out
			   );
	}
	
	public function loadCourseIdAndStatus() {
		require_once("Services/CourseBooking/classes/class.ilCourseBooking.php");
		
		$this->crs_id = intval($_GET["crs_id"]);
		$this->status = gevUserUtils::getInstance($this->user->getId())->getBookingStatusAtCourse($this->crs_id);
		
		if (! in_array($this->status, array(ilCourseBooking::STATUS_BOOKED, ilCourseBooking::STATUS_WAITING))) {
			$this->log->write("gevMyCoursesGUI::loadCourseIdAndStatus: status not booked or waiting.");
			$this->ctrl->redirect($this, "view");
			exit();
		}
	}
	
	public function cancelBooking() {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$this->loadCourseIdAndStatus();
		$crs_utils = gevCourseUtils::getInstance($this->crs_id);
		$usr_utils = gevUserUtils::getInstance($this->user->getId());
		
		if ( $usr_utils->paysFees() 
		   && $crs_utils->getFee() 
		   && $this->status != ilCourseBooking::STATUS_WAITING 
		   && $crs_utils->isCancelDeadlineExpired()) {
			$msg = $this->lng->txt("gev_costly_cancellation_msg");
			$action = $this->lng->txt("gev_costly_cancellation_action");
		}
		else {
			$msg = $this->lng->txt("gev_free_cancellation_msg");
			$action = $this->lng->txt("gev_costly_cancellation_action");
		}
	/*
		$msg = sprintf($msg, $crs_utils->getTitle());
		
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
		
		$vals = array(
			  array( $this->lng->txt("gev_course_id")
				   , true
				   , $this->crs_utils->getCustomId()
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
				   , $prv?true:false
				   , $prv?$prv->getTitle():""
				   )
			, array( $this->lng->txt("gev_venue")
				   , $ven?true:false
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
				   , $this->isWithPayment()
				   , str_replace(".", ",", "".$this->crs_utils->getFee()) . " &euro;"
				   )
			, array( $this->lng->txt("gev_credit_points")
				   , true
				   , $this->crs_utils->getCreditPoints()
				   )
			//, array( $this->lng->txt("precondition")
			//	   , true
			//	   , $this->crs_utils->getFormattedPreconditions()
			//	   )
			);
		
		foreach ($vals as $val) {
			if (!$val[1] or !$val[2]) {
				continue;
			}
		
			$field = new ilNonEditableValueGUI($val[0], "", true);
			$field->setValue($val[2]);
			$form->addItem($field);
		}*/
		
		require_once("Services/CaTUIComponents/classes/class.catChoiceGUI.php");
		$choice = new catChoiceGUI();
		$choice->setEnableTitle(true);
		$choice->setTitle("gev_cancellation_title");
		$choice->setSubTitle("gev_cancellation_subtitle");
		$choice->setImage("GEV_img/ico-head-trash.png");

		$choice->setQuestion($msg);
		$choice->setAbort($this->lng->txt("cancel"), $this->ctrl->getLinkTarget($this, "view"));
		$this->ctrl->setParameter($this, "crs_id", $this->crs_id);
		$choice->addChoice($action, $this->ctrl->getLinkTarget($this, "finalizeCancellation"));
		$this->ctrl->clearParameters($this, "crs_id", $this->crs_id);
		return $choice->render();
	}
	
	public function finalizeCancellation() {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		$this->loadCourseIdAndStatus();
		$crs_utils = gevCourseUtils::getInstance($this->crs_id);
		$crs_utils->cancelBookingOf($this->user->getId());
		ilUtil::sendSuccess(sprintf( $this->lng->txt("gev_cancellation_success")
								   , $crs_utils->getTitle()
								   )
						   );
		return $this->render();
	}
	
	public function noNextCourse() {
		ilUtil::sendFailure($this->lng->txt("gev_no_next_course"));
		return $this->view();
	}
	
	public function noLastCourse() {
		ilUtil::sendFailure($this->lng->txt("gev_no_last_course"));
		return $this->view();	
	}
}

?>