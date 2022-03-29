<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateObjectHelper
{
    /**
     * @param int  $objectId
     * @param bool $stop_on_error
     * @return null|ilObject
     * @throws ilDatabaseException
     * @throws ilObjectNotFoundException
     */
    public function getInstanceByObjId(int $objectId, bool $stop_on_error = true) : ?ilObject
    {
        return ilObjectFactory::getInstanceByObjId($objectId, $stop_on_error);
    }

    public function lookupObjId(int $refId) : int
    {
        return ilObject::_lookupObjId($refId);
    }

    public function lookupType(int $objectId) : string
    {
        return ilObject::_lookupType($objectId);
    }

    public function lookupTitle(int $objectId) : string
    {
        return ilObject::_lookupTitle($objectId);
    }
}
