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
		$spacer = new catHSpacerGUI();
		$spacer_out = $spacer->render();
		
		$crss = new gevCoursesTableGUI($this->user->getId(), $this);
		$crss_out = $crss->getHTML();
		
		return ( $spacer_out
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
		
		$this->loadCourseIdAndStatus();
		$crs_utils = gevCourseUtils::getInstance($this->crs_id);
		
		if ($crs_utils->isAbsoluteCancelDeadlineExpired()) {
			throw new Exception("gevMyCoursesGUI::cancelBooking: Absolute cancel deadline expired!");
		}
		
		return $crs_utils->renderCancellationForm($this, $this->user->getId());
	}
	
	public function finalizeCancellation() {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");

		$this->loadCourseIdAndStatus();
		$automails = new gevCrsAutoMails($this->crs_id);
		$crs_utils = gevCourseUtils::getInstance($this->crs_id);
		$user_id = $this->user->getId();
		$old_status = $crs_utils->getBookingStatusOf($user_id);
		$crs_utils->cancelBookingOf($user_id);
		$new_status = $crs_utils->getBookingStatusOf($user_id);
		
		if ($old_status == ilCourseBooking::STATUS_WAITING) {
			if ($new_status == ilCourseBooking::STATUS_CANCELLED_WITHOUT_COSTS) {
				$automails->send("self_cancel_waiting_to_cancelled_without_costs", array($user_id));
			}
		}
		else if ($old_status == ilCourseBooking::STATUS_BOOKED) {
			if ($new_status == ilCourseBooking::STATUS_CANCELLED_WITHOUT_COSTS) {
				$automails->send("self_cancel_booked_to_cancelled_without_costs", array($user_id));
			}
			else if ($new_status == ilCourseBooking::STATUS_CANCELLED_WITH_COSTS) {
				$automails->send("self_cancel_booked_to_cancelled_with_costs", array($user_id));
			}
		}
		
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
