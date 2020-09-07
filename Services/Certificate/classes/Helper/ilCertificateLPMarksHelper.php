<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateLPMarksHelper
{
    /**
     * @param int $userId
     * @param int $objectId
     * @return mixed
     */
    public function lookUpMark(int $userId, int $objectId)
    {
        return ilLPMarks::_lookupMark($userId, $objectId);
    }
}
