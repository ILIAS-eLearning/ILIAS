<?php
require_once 'Services/User/classes/class.ilObjUser.php';
require_once 'Modules/IndividualAssessment/classes/class.ilObjIndividualAssessment.php';
/**
 * Mechanic regarding the access controll and roles of an objcet goes here.
 * @author Denis Klöpfer <denis.kleofer@concepts-and-training.de>
 */
interface IndividualAssessmentAccessHandler
{

    /**
     * Can an user perform an operation on some Individual assessment?
     *
     * @param	string	$operation
     * @return bool
     */
    public function checkAccessToObj($operation);

    /**
     * Create default roles at an object
     *
     * @param	ilObjIndividualAssessment	$iass
     */
    public function initDefaultRolesForObject(ilObjIndividualAssessment $iass);

    /**
     * Assign a user to the member role at an Individual assessment
     *
     * @param	ilObjIndividualAssessment	$iass
     * @param	ilObjUser	$usr
     */
    public function assignUserToMemberRole(ilObjUser $usr, ilObjIndividualAssessment $iass);

    /**
     * Deasign a user from the member role at an Individual assessment
     *
     * @param	ilObjIndividualAssessment	$iass
     * @param	ilObjUser	$usr
     */
    public function deassignUserFromMemberRole(ilObjUser $usr, ilObjIndividualAssessment $iass);

    /**
     * Check whether user is system admin.
     *
     * @return bool
     */
    public function isSystemAdmin();
}
