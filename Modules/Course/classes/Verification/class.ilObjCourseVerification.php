<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Verification/classes/class.ilVerificationObject.php');

/**
* Course Verification
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*
* @version $Id$
*
* @ingroup ModulesCourse
*/
class ilObjCourseVerification extends ilVerificationObject
{
    protected function initType()
    {
        $this->type = "crsv";
    }

    protected function getPropertyMap()
    {
        return array("issued_on" => self::TYPE_DATE,
            "file" => self::TYPE_STRING
            );
    }
}
