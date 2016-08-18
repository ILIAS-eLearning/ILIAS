<?php
require_once 'Services/User/classes/class.ilObjUser.php';
interface ilManualAssessmentNotificator {
	public function withReciever(ilManualAssessmentMember $member);
	public function withOccasionFailed();
	public function withOccasionCompleted();
	public function send();
}