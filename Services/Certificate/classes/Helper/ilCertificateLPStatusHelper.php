<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateLPStatusHelper
{
    public function lookupStatusChanged(int $objId, int $userId) : string
    {
        return (string) ilLPStatus::_lookupStatusChanged($objId, $userId);
    }

    public function lookUpStatus(int $objectId, int $userId) : int
    {
        return (int) ilLPStatus::_lookupStatus($objectId, $userId);
    }
}
