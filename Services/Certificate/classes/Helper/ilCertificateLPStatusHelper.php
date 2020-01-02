<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateLPStatusHelper
{
    /**
     * @param int $objId
     * @param int $userId
     * @return mixed
     */
    public function lookupStatusChanged(int $objId, int $userId)
    {
        return ilLPStatus::_lookupStatusChanged($objId, $userId);
    }

    /**
     * @param $objectId
     * @param $userId
     * @return mixed
     */
    public function lookUpStatus($objectId, $userId)
    {
        return ilLPStatus::_lookupStatus($objectId, $userId);
    }
}
