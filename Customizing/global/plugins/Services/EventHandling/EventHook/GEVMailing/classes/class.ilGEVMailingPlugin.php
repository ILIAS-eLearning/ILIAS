<?php

require_once("./Services/EventHandling/classes/class.ilEventHookPlugin.php");
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

class ilGEVMailingPlugin extends ilEventHookPlugin
{
	const SELF_BOOKING = 0;
	const ADMIN_BOOKING = 1;
	const SUPERIOR_BOOKING = 2;
	
	final function getPluginName() {
		return "GEVMailing";
	}
	
	final function handleEvent($a_component, $a_event, $a_parameter) {
		switch ($a_component) {
			case "Services/CourseBooking":
				return $this->bookingEvent($a_event, $a_parameter);
			case "Services/ParticipationsStatus":
				return $this->participationStatusEvent($a_event, $a_parameter);
			case "Modules/Course":
				return $this->courseEvent($a_event, $a_parameter);
			default:
				break;
		}
	}
	
	protected function bookingEvent($a_event, $a_parameter) {
		if ($a_event !== "setStatus") {
			return;
		}
		require_once("Services/CourseBooking/classes/class.ilCourseBooking.php");
		require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
		
		$os = $a_parameter["old_status"];
		$ns = $a_parameter["new_status"];
		$usr_id = intval($a_parameter["user_id"]);
		$crs_id = intval($a_parameter["crs_obj_id"]);
		$bt = $this->getBookingType($usr_id, $crs_id);
		$mails = new gevCrsAutoMails($crs_id);
		
		if ($os == ilCourseBooking::STATUS_WAITING && $ns == ilCourseBooking::STATUS_BOOKED) {
			$mails->sendDeferred("participant_waiting_to_booked", array($usr_id));
			$mails->sendDeferred("invitation", array($usr_id));
		}
		
		// do not handle all booking events here, since the way the 
		// booking was made is crucial here. Mails will be send in the 
		// respective forms instead.
		
/*		if ($os == null) {
			if ($ns == ilCourseBooking::STATUS_BOOKED) {
				if ($os == null) {
					if ($bt == self::SELF_BOOKING) {
						$mails->sendDeferred("self_booking_to_booked", array($usr_id));
					}
					else if ($bt == self::ADMIN_BOOKING) {
						$mails->sendDeferred("admin_booking_to_booked", array($usr_id));
					}
					else if ($bt == self::SUPERIOR_BOOKING) {
						$mails->sendDeferred("superior_booking_to_booked", array($usr_id));	
					}
				}
				else if ($os == ilCourseBooking::STATUS_WAITING) {
					$mails->sendDeferred("participant_waiting_to_booked", array($usr_id));
				}
			}
			else if ($ns == ilCourseBooking::STATUS_WAITING) {
				if ($bt == self::SELF_BOOKING) {
					$mails->sendDeferred("self_booking_to_waiting", array($usr_id));
				}
				else if ($bt == self::ADMIN_BOOKING) {
					$mails->sendDeferred("admin_booking_to_waiting", array($usr_id));
				}
				else if ($bt == self::SUPERIOR_BOOKING) {
					$mails->sendDeferred("superior_booking_to_waiting", array($usr_id));	
				}
			}
			else if ($ns == ilCourseBooking::STATUS_STATUS_CANCELLED_WITH_COSTS) {
				if ($os == ilCourseBooking::STATUS_BOOKED) {
					if ($bt == self::SELF_BOOKING) {
						$mails->sendDeferred("self_cancel_booked_to_cancelled_with_costs", array($usr_id));
					}
					else if ($bt == self::ADMIN_BOOKING) {
						$mails->sendDeferred("admin_cancel_booked_to_cancelled_with_costs", array($usr_id));
					}
					else if ($bt == self::SUPERIOR_BOOKING) {
						$mails->sendDeferred("superior_cancel_booked_to_cancelled_with_costs", array($usr_id));	
					}
				}
			}
			else if ($ns == ilCourseBooking::STATUS_STATUS_CANCELLED_WITHOUT_COSTS) {
				if ($os == ilCourseBooking::STATUS_BOOKED) {
					if ($bt == self::SELF_BOOKING) {
						$mails->sendDeferred("self_cancel_booked_to_cancelled_without_costs", array($usr_id));
					}
					else if ($bt == self::ADMIN_BOOKING) {
						$mails->sendDeferred("admin_cancel_booked_to_cancelled_without_costs", array($usr_id));
					}
					else if ($bt == self::SUPERIOR_BOOKING) {
						$mails->sendDeferred("superior_cancel_booked_to_cancelled_without_costs", array($usr_id));	
					}
				}
				else if ($os == ilCourseBooking::STATUS_WAITING) {
					if ($bt == self::SELF_BOOKING) {
						$mails->sendDeferred("self_cancel_waiting_to_cancelled_without_costs", array($usr_id));
					}
					else if ($bt == self::ADMIN_BOOKING) {
						$mails->sendDeferred("admin_cancel_waiting_to_cancelled_without_costs", array($usr_id));
					}
					else if ($bt == self::SUPERIOR_BOOKING) {
						$mails->sendDeferred("superior_cancel_waiting_to_cancelled_without_costs", array($usr_id));	
					}
				}
			}
		}*/
	}
	
	protected function getBookingType($a_user_id, $a_crs_id) {
		global $ilUser;
		
		if ($ilUser->getId() == $a_user_id) {
			return self::SELF_BOOKING;
		}
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		if ( in_array($ilUser->getId(), gevCourseUtils::getInstance($a_crs_id)->getAdmins())) {
			return self::ADMIN_BOOKING;
		}
		
		return self::ADMIN_BOOKING;
	}
	
	protected function participationStatusEvent($a_event, $a_parameter) {
		if ($a_event == "deleteStatus") {
			require_once("Services/GEV/Mailing/classes/class.gevDeferredMails.php");
			gevDeferredMails::getInstance()->removeDeferredMails( array($a_parameter["crs_obj_id"])
																, array( "participant_successfull"
																	   , "participant_absent_excused"
																	   , "participant_absent_not_excused"
																	   )
																, array($a_parameter["user_id"])
																);
			return;
		}

		if ($a_event != "setStatusAndPoints") {
			return;
		}
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		require_once("Services/ParticipationStatus/classes/class.ilParticipationStatus.php");
		require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
		
		$usr_id = intval($a_parameter["user_id"]);
		$crs_id = intval($a_parameter["crs_obj_id"]);
		$status = gevCourseUtils::getInstance($crs_id)->getParticipationStatusOf($usr_id);
		$mails = new gevCrsAutoMails($crs_id);
		
		if ($status == ilParticipationStatus::STATUS_SUCCESSFUL) {
			$mails->sendDeferred("participant_successfull", array($usr_id));
		}
		else if ($status == ilParticipationStatus::STATUS_ABSENT_EXCUSED) {
			$mails->sendDeferred("participant_absent_excused", array($usr_id));
		}
		else if ($status == ilParticipationStatus::STATUS_ABSENT_NOT_EXCUSED) {
			$mails->sendDeferred("participant_absent_not_excused", array($usr_id));
		}
	}
	
	protected function courseEvent($a_event, $a_parameter) {
		if (  $a_event != "addParticipant"
		   && $a_event != "deleteParticipant") {
			return;
		}
		
		// #317
		return;
		
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		require_once("Services/ParticipationStatus/classes/class.ilParticipationStatus.php");
		require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
		
		$usr_id = intval($a_parameter["usr_id"]);
		$crs_id = intval($a_parameter["obj_id"]);
		$role_id = $a_parameter["role_id"];		
		$crs_utils = gevCourseUtils::getInstance($crs_id);
		
		if ($role_id != $crs_utils->getCourse()->getDefaultTutorRole()
		&&  $role_id != IL_CRS_TUTOR) {
			return;
		}
		
		$mails = new gevCrsAutoMails($crs_id);
		
		if ($a_event == "addParticipant") {
			$mails->sendDeferred("trainer_added", array($usr_id));
		}
		else if ($a_event == "deleteParticipant") {
			$mails->send("trainer_removed", array($usr_id));
		}
	}
}

?>