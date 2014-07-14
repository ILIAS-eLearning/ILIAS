<?php

require_once("./Services/EventHandling/classes/class.ilEventHookPlugin.php");


class ilGEVBillingPlugin extends ilEventHookPlugin
{
	final function getPluginName() {
		return "GEVBilling";
	}
	
	final function handleEvent($a_component, $a_event, $a_parameter) {
		if ($a_component == "Services/Billing" && $a_event == "billFinalized") {
			$this->billFinalized($a_parameter["bill"]);
		}
		else if ($a_component = "Services/CourseBooking" && $a_event == "setStatus") {
			$this->bookingStatusChanged($a_parameter["crs_obj_id"], $a_parameter["user_id"]);
		}
	}
	
	protected function billFinalized($a_bill) {
		//TODO: send bill as email to user
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
		
		if (!$usr_utils->paysFee()) {
			// Nothing to do with billing here either.
			return;
		}
		
		require_once("Services/CourseBooking/classes/class.ilCourseBooking.php");
		require_once("Services/Billing/classes/class.ilBill.php");
		
		$status = $crs_utils->getBookingStatusOf($a_user_id);
		$bills = ilBill::getInstancesByUserAndContext($a_user_id, $a_crs_id);
		$amount_bills = count($bills);
		
		if ($amount_bills == 0) {
			// there is no bill for the user at the course, so we don't need 
			// to do anything.
			return;
		}
		
		if ($amount_bills > 1) {
			// this is an assumption about the booking process. There should
			// never be more than one bill per course and user.
			$ilLog->write("ilGEVBillingPlugin::bookingStatusChanged: ".
						  "There is more than one bill for user ".$a_user_id.
						  " at course ".$a_crs_id.", this violates a crucial".
						  "assumption about the booking process."
						  );
			return;
		}
		
		$bill = $bill[0];
		
		if ($bill->isFinalized()) {
			// this is an assumption about the booking process. The bill should
			// be finalized only after a training where booking status can't 
			// change anymore.
			$ilLog->write("ilGEVBillingPlugin::bookingStatusChanged: ".
						  "Th bill for user ".$a_user_id." at course ".
						  $a_crs_id." is already finalized, which violates a crucial".
						  "assumption about the booking process.");
			return;
		}
		
		if ($status == ilCourseBooking::STATUS_CANCELLED_WITHOUT_COSTS) {
			$this->cancelBill($a_crs_id, $a_user_id, $bill);
		}
		else if ($status == ilCourseBooking::STATUS_CANCELLED_WITH_COSTS) {
			$this->createCancellationBillAndCoupons($a_crs_id, $a_user_id, $bill);
		}
		else {
			// Nothing to do here, bill was created in booking process if user or
			// superior did the booking. There should be no bills if admin books
			// a user.
		}
	}
	
	protected function cancelBill($a_crs_id, $a_user_id, $a_bill) {
		global $ilLog;
		require_once("Services/GEV/Utils/classes/class.gevBillingUtils.php");
		$items = $a_bill->getItems();
		gevBillingUtils::getInstance()->resetCouponValuesFromItems($items);
		foreach ($items as $item) {
			if ($item->isFinalized()) {
				$ilLog->write("ilGEVBillingPlugin::cancelBill: item '".$item->getId()."' ".
							  "in bill '".$a_bill->getId()."' already finalized. This should ".
							  "not happen...");
			}
			else {
				$item->delete();
			}
		}
		$a_bill->delete();
		$ilLog->write("ilGEVBillingPlugin::cancelBill: deleted bill ".$a_bill->getId()." for user ".$a_user_id.
					  " at course ".$a_crs_id.".");
	}
	
	protected function createCancellationBill($a_crs_id, $a_user_id, $a_bill) {
		// TODO: this needs to be implemented...
	}
}

?>