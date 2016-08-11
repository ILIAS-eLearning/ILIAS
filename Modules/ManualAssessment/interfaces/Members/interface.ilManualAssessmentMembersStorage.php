<?php
require_once 'Modules/ManualAssessment/classes/class.ilObjManualAssessment.php';
interface ilManualAssessmentMembersStorage {

	const FIELD_FIRSTNAME = 'firstname';
	const FIELD_LASTNAME = 'lastname';
	const FIELD_LOGIN = 'login';
	const FIELD_USR_ID = 'usr_id';
	const FIELD_GRADE = 'grade';
	const FIELD_EXAMINER_ID = 'examiner_id';
	const FIELD_EXAMINER_FIRSTNAME = 'examiner_firstname';
	const FIELD_EXAMINER_LASTNAME = 'examiner_lastname';
	const FIELD_RECORD = 'record';
	const FIELD_INTERNAL_NOTE = 'internal_note';
	const FIELD_NOTIFY = 'notify';

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
	 * Update storage entry associated with member-object.
	 *
	 * @param	ilManualAssessmentMember	$member
	 */
	public function updateMembers(ilManualAssessmentMembers $members);

	/**
	 * Delete entries associated with members-object.
	 *
	 * @param	ilManualAssessmentMember	$member
	 */
	public function deleteMembers(ilObjManualAssessment $obj);

}