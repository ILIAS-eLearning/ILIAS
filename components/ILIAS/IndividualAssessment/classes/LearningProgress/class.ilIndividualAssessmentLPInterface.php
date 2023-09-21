<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

class ilIndividualAssessmentLPInterface
{
    protected static ?ilIndividualAssessmentMembersStorageDB $members_storage = null;

    public static function updateLPStatusOfMember(ilIndividualAssessmentMember $member): void
    {
        ilLPStatusWrapper::_updateStatus($member->assessmentId(), $member->id());
    }

    public static function updateLPStatusByIds(int $iass_id, array $usr_ids): void
    {
        foreach ($usr_ids as $usr_id) {
            ilLPStatusWrapper::_updateStatus($iass_id, $usr_id);
        }
    }

    public static function determineStatusOfMember(int $iass_id, int $usr_id): int
    {
        if (self::$members_storage === null) {
            self::$members_storage = self::getMembersStorage();
        }

        $iass = new ilObjIndividualAssessment($iass_id, false);
        $members = $iass->loadMembers();
        $usr = new ilObjUser($usr_id);

        if ($members->userAllreadyMember($usr)) {
            $member = self::$members_storage->loadMember($iass, $usr);

            if ($member->finalized()) {
                return $member->LPStatus();
            } elseif (
                in_array($member->LPStatus(), [
                    ilIndividualAssessmentMembers::LP_FAILED,
                    ilIndividualAssessmentMembers::LP_COMPLETED
                ])
            ) {
                return ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
            }
        }

        return ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
    }

    protected static function getMembersStorage(): ilIndividualAssessmentMembersStorageDB
    {
        global $DIC;
        return new ilIndividualAssessmentMembersStorageDB($DIC['ilDB']);
    }

    public static function getMembersHavingStatusIn(int $iass_id, int $status): array
    {
        if (self::$members_storage === null) {
            self::$members_storage = self::getMembersStorage();
        }
        $members = self::$members_storage->loadMembers(new ilObjIndividualAssessment($iass_id, false));
        $return = array();
        foreach ($members as $usr_id => $record) {
            if (self::determineStatusOfMember($iass_id, $usr_id) === $status) {
                $return[] = $usr_id;
            }
        }
        return $return;
    }

    public static function isActiveLP(int $object_id): bool
    {
        return ilIndividualAssessmentLP::getInstance($object_id)->isActive();
    }
}
