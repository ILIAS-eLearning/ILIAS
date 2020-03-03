<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Verification/classes/class.ilVerificationObject.php');

/**
* SCORM Verification
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilObjSCORMVerification extends ilVerificationObject
{
    protected function initType()
    {
        $this->type = "scov";
    }

    protected function getPropertyMap()
    {
        return array("issued_on" => self::TYPE_DATE,
            "file" => self::TYPE_STRING
            );
    }
}
