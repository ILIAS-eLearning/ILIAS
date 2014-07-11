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
		
		$this->checkIfUserAlreadyPassedASimilarCourse();
		$this->checkIfCourseIsFull();
		$this->checkIfUserIsAllowedToBookCourseForOtherUser();
		$this->checkIfUserIsAllowedToBookCourse();
		
		$cmd = $this->ctrl->getCmd();
		
		switch($cmd) {
			case "backToSearch":
				$this->toCourseSearch();
			case "book":
			case "paymentInfo":
			case "finalizeBookingWithoutPayment":
			case "finalizeBookingWithPayment":
				$this->setRequestParameters();
				$cont = $this->$cmd();
				break;
			default:
				$this->log->write("gevBookingGUI: Unknown command '".$cmd."'");
		}
		
		
		if ($cont) {
			$this->insertInTemplate($cont);
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
	
	protected function toCourseSearch() {
		ilUtil::redirect("ilias.php?baseClass=gevDesktopGUI&cmd=toCourseSearch");
		exit();
	}
	
	protected function checkIfUserAlreadyPassedASimilarCourse() {
		if ($this->user_utils->hasPassedCourseDerivedFromTemplate($this->crs_utils->getTemplateRefId())) {
			ilUtil::sendFailure( $this->isSelfBooking() ? $this->lng->txt("gev_passed_similar_course_self")
														: $this->lng->txt("gev_passed_similar_course_employee")
							   , true);
			$this->toCourseSearch();
		}
	}
	
	protected function checkIfCourseIsFull() {
		$free_places = $this->crs_utils->getFreePlaces();
		if ( $free_places && $free_places <= 0 
		  && !$this->crs_utils->isWaitingListActivated()) {
			ilUtil::sendFailure($this->lng->txt("gev_course_is_full"), true);
			$this->toCourseSearch();
		}
	}
	
	protected function checkIfUserIsAllowedToBookCourseForOtherUser() {
		if ( !$this->crs_utils->canBookCourseForOther($this->current_user->getId(), $this->user_id)) {
			ilUtil::sendFailure($this->lng->txt("gev_not_allowed_to_book_crs_for_other"), true);
			$this->toCourseSearch();
		}
	}
	
	public function checkIfUserIsAllowedToBookCourse() {
		if ( !$this->crs_utils->isBookableFor($this->user_id)) {
			ilUtil::sendFailure($this->lng->txt("gev_user_not_allowed_to_book_crs"), true);
			$this->toCourseSearch();
		}
	}
	
	protected function setRequestParameters() {
		$this->ctrl->setParameter($this, "crs_id", $this->crs_id);
		$this->ctrl->setParameter($this, "user_id", $this->user_id);
	}
	
	protected function isSelfBooking() {
		return $this->user_id == $this->current_user->getId();
	}
	
	protected function isWithPayment() {
		return $this->user_utils->paysFees() && ($this->crs_utils->getFee()?true:false);
	}
	
	protected function insertInTemplate($a_cont) {
		require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
		require_once("Services/CaTUIComponents/classes/class.catHSpacerGUI.php");
		
		if ($this->isSelfBooking()) {
			$title = new catTitleGUI("gev_booking", "gev_booking_header_note", "GEV_img/ico-head-booking.png");
			$employee_info = "";
		}
		else {
			require_once("Services/CaTUIComponents/classes/class.catPropertyFormGUI.php");
			require_once("Services/Form/classes/class.ilNonEditableValueGUI.php");
			
			$title = new catTitleGUI("gev_book_employee", "gev_booking_header_note", "GEV_img/ico-head-booking.png");
			$spacer = new catHSpacerGUI();
			
			$form = new catPropertyFormGUI();
			$form->setTemplate("tpl.gev_booking_form.html", "Services/GEV/Desktop");
			$field = new ilNonEditableValueGUI($this->lng->txt("gev_booking_for"), "", true);
			$field->setValue($this->user_utils->getFullName());
			$form->addItem($field);
			
			$employee = $spacer->render()
					  . $form->getContent()
					  . $spacer->render()
					  ;
		}
		
		$this->tpl->setContent( $title->render()
							  . $employee
							  . $a_cont
							  );
	}
	
	protected function book() {
		require_once("Services/CaTUIComponents/classes/class.catPropertyFormGUI.php");
		require_once("Services/Form/classes/class.ilNonEditableValueGUI.php");
		require_once("Services/Accomodations/classes/class.ilSetAccomodationsGUI.php");
		
		$form = new catPropertyFormGUI();
		$form->setTemplate("tpl.gev_booking_form.html", "Services/GEV/Desktop");
		$form->setTitle($this->crs_utils->getTitle());
		$form->addCommandButton("backToSearch", $this->lng->txt("gev_to_course_search"));
		
		if ($this->isWithPayment()) {
			$form->addCommandButton("paymentInfo", $this->lng->txt("gev_set_billing_data"));
		}
		else {
			$form->addCommandButton("finalizeBookingWithoutPayment", $this->lng->txt("gev_obligatory_booking"));
		}
		$form->setFormAction($this->ctrl->getFormAction($this));

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
		}
		
		if ($this->crs_utils->getAccomodation()) {
			$this->lng->loadLanguageModule("acco");
			ilSetAccomodationsGUI::addAccomodationsToForm($form, $this->crs_id, $this->user_id);
		}
		
		if ($this->isSelfBooking()) {
			$note = new ilNonEditableValueGUI($this->lng->txt("notice"), "", true);
			$note->setValue($this->lng->txt("gev_booking_note"));
			$form->addItem($note);
		}
		
		return $form->getHTML();
	}
	
	protected function buildPaymentForm($a_accomodations = null) {
		require_once("Services/CaTUIComponents/classes/class.catPropertyFormGUI.php");
		require_once("Services/Form/classes/class.ilTextInputGUI.php");
		require_once("Services/Form/classes/class.ilEMailInputGUI.php");
		require_once("Services/Form/classes/class.ilCheckboxInputGUI.php");
		require_once("Services/Form/classes/class.ilHiddenInputGUI.php");
		
		$form = new catPropertyFormGUI();
		$form->setTemplate("tpl.gev_booking_form.html", "Services/GEV/Desktop");
		$form->setTitle($this->crs_utils->getTitle());
		$form->addCommandButton("backToSearch", $this->lng->txt("gev_to_course_search"));
		$form->addCommandButton("finalizeBookingWithPayment", $this->lng->txt("gev_obligatory_booking"));
		$form->setFormAction($this->ctrl->getFormAction($this));
		
		$recipient = new ilTextInputGUI($this->lng->txt("gev_bill_recipient"), "recipient");
		$recipient->setRequired(true);
		$form->addItem($recipient);
		
		$agency = new ilTextInputGUI($this->lng->txt("gev_bill_agency_name"), "agency");
		$agency->setRequired(true);
		$form->addItem($agency);
		
		$street = new ilTextInputGUI($this->lng->txt("street"), "street");
		$street->setRequired(true);
		$form->addItem($street);
		
		$housenumber = new ilTextInputGUI($this->lng->txt("housenumber"), "housenumber");
		$housenumber->setRequired(true);
		$form->addItem($housenumber);
		
		$zipcode = new ilTextInputGUI($this->lng->txt("zipcode"), "zipcode");
		$zipcode->setRequired(true);
		$form->addItem($zipcode);
		
		$city = new ilTextInputGUI($this->lng->txt("city"), "city");
		$city->setRequired(true);
		$form->addItem($city);
		
		$costcenter = new ilTextInputGUI($this->lng->txt("gev_bill_costcenter"), "costcenter");
		$costcenter->setRequired(true);
		$form->addItem($costcenter);
		
		$coupons = new ilTextInputGUI($this->lng->txt("gev_coupon_codes"), "coupons");
		$coupons->setMulti(true);
		$form->addItem($coupons);
		
		$email = new ilEMailInputGUI($this->lng->txt("gev_bill_email"), "email");
		$email->setRequired(true);
		$form->addItem($email);
		
		$agb = new ilCheckboxInputGUI($this->lng->txt("gev_accept_book_cond"), "agb");
		$form->addItem($agb);
		
		$accomodations = new ilHiddenInputGUI("accomodations");
		if ($a_accomodations) {
			$accomodations->setValue(serialize($a_accomodations));
		}
		$form->addItem($accomodations);
		
		return $form;
	}

	protected function paymentInfo() {
		require_once("Services/CaTUIComponents/classes/class.catPropertyFormGUI.php");
		require_once("Services/Accomodations/classes/class.ilSetAccomodationsGUI.php");
		
		$_form = new catPropertyFormGUI();
		ilSetAccomodationsGUI::addAccomodationsToForm($_form, $this->crs_id, $this->user_id);
		if (!$_form->checkInput()) {
			$this->log->write("gevBookingGUI::paymentInfo: This should not happen, the form input did not check correctly.");
			$this->toCourseSearch();
			return;
		}
		
		$accomodations = $_form->getInput("acco");

		$form = $this->buildPaymentForm($accomodations);
		$last_bill_data = $this->user_utils->getLastBillingDataMaybe();
		if ($last_bill_data) {
			foreach ($last_bill_data as $key => $value) {
				$form->getItemByPostVar($key)->setValue($value);
			}
		}
		
		return $form->getHTML();
	}
	
	protected function finalizeBookingWithPayment() {
		require_once("Services/GEV/Utils/classes/class.gevBillingUtils.php");
		$billing_utils = gevBillingUtils::getInstance();
		
		
		$form = $this->buildPaymentForm();
		
		$ok = false;
		if ($form->checkInput()) {
			$ok = true;
			if (!$form->getInput("agb")) {
				$ok = false;
				$form->getItemByPostvar("agb")->setAlert($this->lng->txt("gev_book_no_cond_accept"));
			}
			
			if ($ok) {
				$coupons = $form->getInput("coupons");
				$invalid_codes = array();
				foreach ($coupons as $key => $coupon) {
					if (!$coupon) {
						unset($coupons[$key]);
						continue;
					}
					if (!$billing_utils->isValidCouponCode($coupon)) {
						$invalid_codes[] = $coupon;
					}
				}
				if (count($invalid_codes) > 0) {
					$ok = false;
					$form->getItemByPostvar("coupons")->setAlert( sprintf( $this->lng->txt("gev_invalid_coupon_codes")
																		 , implode(", ", $invalid_codes)
																		 )
																);
				}
			}
		}
		
		if ($ok) {
			$accomodations = unserialize($form->getInput("accomodations"));
			$status = $this->finalizeBooking($accomodations);
			$billing_utils->createCourseBill( $this->user_id
											, $this->crs_id
											, $form->getInput("recipient")
											, $form->getInput("agency")
											, $form->getInput("street")
											, $form->getInput("housenumber")
											, $form->getInput("zipcode")
											, $form->getInput("city")
											, $form->getInput("costcenter")
											, $coupons
											, $form->getInput("email")
											);
			$this->finalizedBookingRedirect($status);
		}
		else {
			$form->setValuesByPost();
			return $form->getHTML();
		}
	}

	protected function finalizeBookingWithoutPayment() {
		$accomodations = $_form->getInput("acco");
		$status = $this->finalizeBooking($accomodations);
		$this->finalizedBookingRedirect($status);
	}
	
	protected function finalizeBooking($a_accomodations) {
		require_once("Services/CourseBooking/classes/class.ilCourseBooking.php");
		require_once("Services/Accomodations/classes/class.ilSetAccomodationsGUI.php");
		require_once("Services/Accomodations/classes/class.ilAccomodations.php");
		
		if (!$this->crs_utils->bookUser($this->user_id)) {
			$this->failAtFinalize("Someone managed to get here but not being able to book the course.");
		}
		$acco = ilSetAccomodationsGUI::formInputToAccomodationsArray($a_accomodations);	
		$acco_inst = ilAccomodations::getInstance($this->crs_utils->getCourse());
		$acco_inst->setAccomodationsOfUser($this->user_id, $acco);
		
		$status = $this->crs_utils->getBookingStatusOf($this->user_id);
		
		if ($status != ilCourseBooking::STATUS_BOOKED && $status != ilCourseBooking::STATUS_WAITING) {
			$this->failAtFinalize("Status was neither booked nor waiting.");
		}
		
		return $status;
	}
	
	protected function failAtFinalize($msg) {
		$this->log->write("gevBookingGUI::finalizeBooking: ".$msg);
		ilUtil::sendFailure($this->lng->txt("gev_finalize_booking_error"), true);
		$this->toCourseSearch();
		exit();
	}
	
	protected function finalizedBookingRedirect($a_status) {
		$booked = $a_status == ilCourseBooking::STATUS_BOOKED;
		
		if ($this->isSelfBooking()) {
			ilUtil::sendSuccess( sprintf( $booked ? $this->lng->txt("gev_was_booked_self")
												  : $this->lng->txt("gev_was_booked_waiting_self")
										, $this->crs_utils->getTitle()
										)
								, true
								);
			
			ilUtil::redirect("ilias.php?baseClass=gevDesktopGUI&cmdClass=toMyCourses");
		}
		else {
			ilUtil::sendSuccess( sprintf ($booked ? $this->lng->txt("gev_was_booked_employee")
										 		  : $this->lng->txt("gev_was_booked_waiting_employee")
										 , $this->user_utils->getFirstname()." ".$this->user_utils->getLastname()
										 , $this->crs_utils->getTitle()
										 )
								, true
								);
			$this->toCourseSearch();
		}
	}
}

?>