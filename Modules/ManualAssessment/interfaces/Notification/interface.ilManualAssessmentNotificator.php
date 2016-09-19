<?php
/**
 * Basic method collection should be implemented by a notificator
 * used by manual assessment.
 * @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 */
require_once 'Services/User/classes/class.ilObjUser.php';
interface ilManualAssessmentNotificator {

	/**
	 * Define the member, that should recieve the message.
	 *
	 * @param	ilManualAssessmentMember	$member
	 * @return	ilManualAssessmentNotificator	$this
	 */
	public function withReciever(ilManualAssessmentMember $member);

	/**
	 * Set message mode to failed.
	 *
	 * @return	ilManualAssessmentNotificator	$this
	 */
	public function withOccasionFailed();

	/**
	 * Set message mode to completed.
	 *
	 * @return	ilManualAssessmentNotificator	$this
	 */
	public function withOccasionCompleted();

	/**
	 * Send message.
	 */
	public function send();
}