<?php
require_once 'Services/User/classes/class.ilObjUser.php';
require_once 'Modules/ManualAssessment/classes/class.ilObjManualAssessment.php';
/**
 * Mechanic regarding the access controll and roles of an objcet goes here.
 * @author Denis Klöpfer <denis.kleofer@concepts-and-training.de>
 */
interface ManualAssessmentAccessHandler {

	/**
	 * Can an user perform an operation on some manual assessment? 
	 *
	 * @param	ilObjUser	$usr
	 * @param	ilObjManualAssessment	$mass
	 * @param	string	$operation
	 * @return bool
	 */
	public function checkAccessOfUserToObj(ilObjUser $usr, ilObjManualAssessment $mass, $operation);

	/**
	 * Create default roles at an object
	 *
	 * @param	ilObjManualAssessment	$mass
	 */
	public function initDefaultRolesForObject(ilObjManualAssessment $mass);

	/**
	 * Assign a user to the member role at an manual assessment
	 *
	 * @param	ilObjManualAssessment	$mass
	 * @param	ilObjUser	$usr
	 */
	public function assignUserToMemberRole(ilObjUser $usr, ilObjManualAssessment $mass);

	/**
	 * Deasign a user from the member role at an manual assessment
	 *
	 * @param	ilObjManualAssessment	$mass
	 * @param	ilObjUser	$usr
	 */
	public function deassignUserFromMemberRole(ilObjUser $usr, ilObjManualAssessment $mass);
}