<?php
require_once "Modules/ManualAssessment/exceptions/class.ilManualAssessmentException.php";
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
	public function saveMember(ilManualAssessmentMember $member);

	/**
	 * Update storage entry associated with member-object.
	 *
	 * @param	ilManualAssessmentMember	$member
	 */
	public function updateMember(ilManualAssessmentMember $member);
}