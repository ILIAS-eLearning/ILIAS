<?php

require_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';
require_once 'Modules/IndividualAssessment/classes/Members/class.ilIndividualAssessmentMembersStorageDB.php';
class ilIndividualAssessmentLPInterface
{
    protected static $members_storage = null;

    public static function updateLPStatusOfMember(ilIndividualAssessmentMember $member)
    {
        ilLPStatusWrapper::_refreshStatus($member->assessmentId(), array($member->id()));
    }


    public static function updateLPStatusByIds($iass_id, array $usr_ids)
    {
        ilLPStatusWrapper::_refreshStatus($iass_id, $usr_ids);
    }

    public static function determineStatusOfMember($iass_id, $usr_id)
    {
        if (self::$members_storage  === null) {
            self::$members_storage = self::getMembersStorage();
        }
        $iass = new ilObjIndividualAssessment($iass_id, false);
        $members = $iass->loadMembers($iass);
        $usr =  new ilObjUser($usr_id);
        if ($members->userAllreadyMember($usr)) {
            $member = self::$members_storage->loadMember($iass, $usr);
            if ($member->finalized()) {
                return $member->LPStatus();
            } elseif (in_array($member->LPStatus(), array(ilIndividualAssessmentMembers::LP_FAILED, ilIndividualAssessmentMembers::LP_COMPLETED))) {
                return ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
            }
        }
        return ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
    }

    protected static function getMembersStorage()
    {
        global $DIC;
        return new ilIndividualAssessmentMembersStorageDB($DIC['ilDB']);
    }

    public static function getMembersHavingStatusIn($iass_id, $status)
    {
        if (self::$members_storage  === null) {
            self::$members_storage = self::getMembersStorage();
        }
        $members = self::$members_storage->loadMembers(new ilObjIndividualAssessment($iass_id, false));
        $return = array();
        foreach ($members as $usr_id => $record) {
            if ((string) self::determineStatusOfMember($iass_id, $usr_id) === (string) $status) {
                $return[] = $usr_id;
            }
        }
        return $return;
    }

    public static function isActiveLP($a_object_id)
    {
        require_once 'Modules/IndividualAssessment/classes/class.ilIndividualAssessmentLP.php';
        return ilIndividualAssessmentLP::getInstance($a_object_id)->isActive();
    }
}
