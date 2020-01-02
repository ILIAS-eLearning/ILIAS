<?php
/**
 * Basic method collection should be implemented by a notificator
 * used by Individual assessment.
 * @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 */
require_once 'Services/User/classes/class.ilObjUser.php';
interface ilIndividualAssessmentNotificator
{

    /**
     * Define the member, that should recieve the message.
     *
     * @param	ilIndividualAssessmentMember	$member
     * @return	ilIndividualAssessmentNotificator	$this
     */
    public function withReciever(ilIndividualAssessmentMember $member);

    /**
     * Set message mode to failed.
     *
     * @return	ilIndividualAssessmentNotificator	$this
     */
    public function withOccasionFailed();

    /**
     * Set message mode to completed.
     *
     * @return	ilIndividualAssessmentNotificator	$this
     */
    public function withOccasionCompleted();

    /**
     * Send message.
     */
    public function send();
}
