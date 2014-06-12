<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/Mailing/classes/class.ilAutoMails.php");
require_once("Services/Mailing/classes/class.ilMailLog.php");

/**
* Class vfCrsAutoMails
*
* @author Richard Klees <richard.klees@concepts-and-training>
*/

class gevCrsAutoMails extends ilAutoMails {
	public function __construct($a_obj_id) {
		$this->mail_data = array(
		  "self_booking_to_booked" => "gevSelfBookingToBooked"
		, "self_booking_to_waiting" => "gevSelfBookingToWaiting"
		, "superior_booking_to_booked" => "gevSuperiorBookingToBooked"
		, "superior_booking_to_waiting" => "gevSuperiorBookingToWaiting"
		, "admin_booking__to_booked" => "gevAdminBookingToBooked"
		, "admin_booking_to_waiting" => "gevAdminBookingToWaiting"
		, "self_cancel_booked_to_canceled_with_costs" => "gevSelfCancelBookedToCancelledWithCosts"
		, "self_cancel_booked_to_canceled_without_costs" => "gevSelfCancelBookedToCancelledWithoutCosts"
		, "self_cancel_waiting_to_canceled_without_costs" => "gevSelfCancelBookedToCancelledWithoutCosts"
		, "superior_cancel_booked_to_canceled_with_costs" => "gevSuperiorCancelBookedToCancelledWithCosts"
		, "superior_cancel_booked_to_canceled_without_costs" => "gevSuperiorCancelBookedToCancelledWithoutCosts"
		, "superior_cancel_waiting_to_canceled_with_costs" => "gevSuperiorCancelBookedToCancelledWithoutCosts"
		, "admin_cancel_booked_to_canceled_with_costs" => "gevAdminCancelBookedToCancelledWithCosts"
		, "admin_cancel_booked_to_canceled_without_costs" => "gevAdminCancelBookedToCancelledWithoutCosts"
		, "admin_cancel_waiting_to_canceled_without_costs" => "gevAdminCancelBookedToCancelledWithoutCosts"
		, "participant_left_corporation" => "gevParticipantLeftCorporation"
		, "participant_waiting_to_booked" => "gevParticipantWaitingToBooked"
		, "participant_successfull" => "gevParticipantSuccessfull"
		, "participant_absent_excused" => "gevParticipantAbsentExcused"
		, "participant_absent_not_excused" => "gevParticipantAbsentNotExcused"
		, "trainer_added" => "gevTrainerAdded"
		, "trainer_removed" => "gevTrainerRemoved"
		, "training_cancelled" => "gevTrainingCancelled"
		, "min_participants_not_reached" => "gevMinParticipantsNotReached"
		, "reminder_participants" => "gevReminderParticipants"
		, "reminder_trainer" => "gevReminderTrainer"
		, "list_for_accomodation" => "gevListForAccomodation"
		, "updated_list_for_accomodation" => "gevUpdatedListForAccomodation"
		, "participation_status_not_set" => "gevParticipationStatusNotSet"
		, "invitation" => "gevInvitation"
		);

		parent::__construct($a_obj_id);

		global $lng;
		$this->lng = &$lng;
	
		$this->lng->loadLanguageModule("mailing");
	}

	public function getTitle() {
		return "Automatische Mails";
	}

	public function getSubtitle() {
		return "";
	}

	public function getIds() {
		return array_keys($this->mail_data);
	}

	protected function createAutoMail($a_id) {
		if (!array_key_exists($a_id, $this->mail_data)) {
			throw new Exception("Unknown AutoMailID: ".$a_id);
		}
		
		require_once("./Services/GEV/Mailing/classes/CrsMails/class.".$this->mail_data[$a_id].".php");
		return new $this->mail_data[$a_id]($this->obj_id, $a_id);
	}

	public function getUserOccasion() {
		return $this->lng->txt("send_by").": ".parent::getUserOccasion();
	}

	public function getInvitationMailFor($a_function, $a_recipient) {
		$mail = $this->getAutoMail("participant_invitation");
		return $mail->getMailFor($a_function, $a_recipient);
	}
	
	protected function initMailLog() {
		$this->setMailLog(new ilMailLog($this->obj_id));
	}
}