<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateExerciseMembersHelper
{
    /**
     * @param int $objectId
     * @param int $userId
     * @return mixed
     */
    public function lookUpStatus(int $objectId, int $userId)
    {
        return ilExerciseMembers::_lookupStatus($objectId, $userId);
    }
}
