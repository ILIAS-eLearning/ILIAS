<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateObjectHelper
{
    /**
     * @param int $objectId
     * @param bool $stop_on_error
     * @return ilObject
     */
    public function getInstanceByObjId($objectId, bool $stop_on_error = true)
    {
        return ilObjectFactory::getInstanceByObjId($objectId, $stop_on_error);
    }


    /**
     * @param int $refId
     * @return int
     */
    public function lookupObjId(int $refId) : int
    {
        return (int) ilObject::_lookupObjId($refId);
    }

    /**
     * @param int $objectId
     * @return string
     */
    public function lookupType(int $objectId) : string
    {
        return ilObject::_lookupType($objectId);
    }

    /**
     * @param int $objectId
     * @return string
     */
    public function lookupTitle(int $objectId) : string
    {
        return (string) ilObject::_lookupTitle($objectId);
    }
}
