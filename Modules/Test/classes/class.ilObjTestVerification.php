<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Verification/classes/class.ilVerificationObject.php');

/**
* Test Verification
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*
* @version $Id$
*
* @ingroup ModulesTest
*/
class ilObjTestVerification extends ilVerificationObject
{
    protected function initType()
    {
        $this->type = "tstv";
    }

    protected function getPropertyMap()
    {
        return array("issued_on" => self::TYPE_DATE,
            "file" => self::TYPE_STRING
            /*
            "success" => self::TYPE_BOOL,
            "result" => self::TYPE_STRING,
            "mark" => self::TYPE_STRING
            */
            );
    }
}
