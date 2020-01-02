<?php

/**
 * Member related storage mechanism.
 * @author Denis Klöpfer <denis.kleofer@concepts-and-training.de>
 */
require_once 'Modules/IndividualAssessment/classes/class.ilObjIndividualAssessment.php';
interface ilIndividualAssessmentMembersStorage
{

    /**
     * Get ilIndividualAssessmentMembers-object containing meberinfo
     * associated with $obj.
     *
     * @param	ilObjIndividualAssessment	$obj
     * @return	ilIndividualAssessmentMembers
     */
    public function loadMembers(ilObjIndividualAssessment $obj);

    /**
     * Get ilIndividualAssessmentMember-object for each obj member
     * associated with $obj.
     *
     * @param	ilObjIndividualAssessment	$obj
     * @return	ilIndividualAssessmentMember[]
     */
    public function loadMembersAsSingleObjects(ilObjIndividualAssessment $obj, string $filter = null, string $sort = null);

    /**
     * Get ilIndividualAssessmentMember-object containing meberinfo
     * associated with $obj and $usr.
     *
     * @param	ilObjIndividualAssessment	$obj
     * @param	ilObjUser	$usr
     * @return	ilIndividualAssessmentMember
     */
    public function loadMember(ilObjIndividualAssessment $obj, ilObjUser $usr);

    /**
     * Create a new storage entry for member-object.
     *
     * @param	ilIndividualAssessmentMember	$member
     */
    public function updateMember(ilIndividualAssessmentMember $member);

    /**
     * Delete entries associated with members-object.
     *
     * @param	ilObjIndividualAssessment	$obj
     */
    public function deleteMembers(ilObjIndividualAssessment $obj);

    /**
     * Create a membership inside storage.
     *
     * @param	ilObjIndividualAssessment	$iass
     * @param	string|int[]	$record
     */
    public function insertMembersRecord(ilObjIndividualAssessment $iass, array $record);

    /**
     * Remove a membership associated with a IndividualAssessment object
     * inside storage.
     *
     * @param	ilObjIndividualAssessment	$iass
     * @param	string|int[]	$record
     */
    public function removeMembersRecord(ilObjIndividualAssessment $iass, array $record);
}
