<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateObjectLPHelper
{
    public function getInstance(int $objId) : ilObjectLP
    {
        return ilObjectLP::getInstance($objId);
    }

    public function getTypeClass(string $type) : string
    {
        return ilObjectLP::getTypeClass($type);
    }

    public function isSupportedObjectType(string $type) : bool
    {
        return ilObjectLP::isSupportedObjectType($type);
    }
}
