<?php

/**
 * Member related storage mechanism.
 * @author Denis Klöpfer <denis.kleofer@concepts-and-training.de>
 */
require_once 'Modules/ManualAssessment/classes/class.ilObjManualAssessment.php';
interface ilManualAssessmentMembersStorage {

	/**
	 * Get ilManualAssessmentMembers-object containing meberinfo
	 * associated with $obj.
	 *
	 * @param	ilObjManualAssessment	$obj
	 * @return	ilManualAssessmentMembers
	 */
	public function loadMembers(ilObjManualAssessment $obj);

	/**
	 * Get ilManualAssessmentMember-object containing meberinfo
	 * associated with $obj and $usr.
	 *
	 * @param	ilObjManualAssessment	$obj
	 * @param	ilObjUser	$usr
	 * @return	ilManualAssessmentMember
	 */
	public function loadMember(ilObjManualAssessment $obj, ilObjUser $usr);

	/**
	 * Create a new storage entry for member-object.
	 *
	 * @param	ilManualAssessmentMember	$member
	 */
	public function updateMember(ilManualAssessmentMember $member);

	/**
	 * Delete entries associated with members-object.
	 *
	 * @param	ilObjManualAssessment	$obj
	 */
	public function deleteMembers(ilObjManualAssessment $obj);

	/**
	 * Create a membership inside storage.
	 *
	 * @param	ilObjManualAssessment	$mass
	 * @param	string|int[]	$record
	 */
	public function insertMembersRecord(ilObjManualAssessment $mass, array $record);

	/**
	 * Remove a membership associated with a ManualAssessment object
	 * inside storage.
	 *
	 * @param	ilObjManualAssessment	$mass
	 * @param	string|int[]	$record
	 */
	public function removeMembersRecord(ilObjManualAssessment $mass, array $record);
}