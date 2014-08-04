<?php

require_once("./Services/EventHandling/classes/class.ilEventHookPlugin.php");
require_once("Services/Billing/classes/class.ilBill.php");


class ilGEVBillingPlugin extends ilEventHookPlugin
{
	final function getPluginName() {
		return "GEVBilling";
	}
	
	final function handleEvent($a_component, $a_event, $a_parameter) {
		//global $ilLog;
		//$ilLog->write(print_r(array($a_component, $a_event, $a_parameter), true));
		if ($a_component = "Services/CourseBooking" && $a_event == "setStatus") {
			$this->bookingStatusChanged($a_parameter["crs_obj_id"], $a_parameter["user_id"]);
		}
		if ($a_component = "Services/Participation" && $a_event == "setStatusAndPoints") {
			$this->participationStatusChanged($a_parameter["crs_obj_id"], $a_parameter["user_id"]);
		}
		if ($a_component = "Services/Billing" && $a_event == "billFinalized") {
			$this->billFinalized($a_parameter["bill"]);
		}
	}
	
	protected function bookingStatusChanged($a_crs_id, $a_user_id) {
		global $ilLog;
		
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		
		$crs_utils = gevCourseUtils::getInstance($a_crs_id);
		
		if (!$crs_utils->getFee()) {
			// Nothing to do with billing here.
			return;
		}
		
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$usr_utils = gevUserUtils::getInstance($a_user_id);
		
		if (!$usr_utils->paysFees()) {
			// Nothing to do with billing here too.
			return;
		}
		
		require_once("Services/CourseBooking/classes/class.ilCourseBooking.php");
		require_once("Services/Billing/classes/class.ilBill.php");
		
		$status = $crs_utils->getBookingStatusOf($a_user_id);
		//$ilLog->write("status = ".$status);
		
		require_once("Services/GEV/Utils/classes/class.gevBillingUtils.php");
		
		$billing_utils = gevBillingUtils::getInstance();
		
		if ($status == ilCourseBooking::STATUS_CANCELLED_WITHOUT_COSTS) {
			$billing_utils->cancelBill($a_crs_id, $a_user_id);
		}
		else if ($status == ilCourseBooking::STATUS_CANCELLED_WITH_COSTS) {
			$billing_utils->createCancellationBillAndCoupon($a_crs_id, $a_user_id);
		}
		else {
			// Nothing to do here, bill was created in booking process if user or
			// superior did the booking. There should be no bills if admin books
			// a user.
		}
	}
	
	protected function participationStatusChanged($a_crs_id, $a_user_id) {
		global $ilLog;
		
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		$crs_utils = gevCourseUtils::getInstance($a_crs_id);
		
		if (!$crs_utils->getFee()) {
			// No billing here.
			return;
		}
		
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$user_utils = gevUserUtils::getInstance($a_crs_id);
		
		if (!$user_utils->paysFees()) {
			// no billing here too.
			return;
		}
		
		require_once("Services/GEV/Utils/classes/class.gevBillingUtils.php");
		$billing_utils = gevBillingUtils::getInstance();
		
		$status = $crs_utils->getParticipationStatusOf($a_user_id);
		if ($status == ilParticipationStatus::STATUS_SUCCESSFUL) {
			$billing_utils->finalizeBill($a_crs_id, $a_user_id);
		}
		else if (  $status == ilParticipationStatus::STATUS_ABSENT_EXCUSED
			    || $status == ilParticipationStatus::STATUS_ABSENT_NOT_EXCUSED) {
			$billing_utils->finalizeNoShowBill($a_crs_id, $a_user_id);
		}
	}
	
	protected function billFinalized(ilBill $a_bill) {
		global $ilLog;
		
		require_once("Services/GEV/Utils/classes/class.gevBillStorage.php");
		gevBillStorage::getInstance()->storeBill($a_bill);
		
		$context_id = $a_bill->getContextId();
		
		if ($context_id) {
			require_once("Services/GEV/Mailing/classes/CrsMails/class.gevCrsBillMail.php");
			$automail = new gevCrsBillMail($context_id);
			$automail->sendBill($a_bill);
		}
		else {
			$ilLog->write("ilGEVBillingPlugin::billFinalized: Bill ".$a_bill->getId()." has no context id.");
		}
	}
}

?>