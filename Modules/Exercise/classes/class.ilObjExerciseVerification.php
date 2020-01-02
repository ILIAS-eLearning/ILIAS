<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Verification/classes/class.ilVerificationObject.php');

/**
* Exercise Verification
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*
* @version $Id$
*
* @ingroup ModulesExercise
*/
class ilObjExerciseVerification extends ilVerificationObject
{
    protected function initType()
    {
        $this->type = "excv";
    }

    protected function getPropertyMap()
    {
        return array("issued_on" => self::TYPE_DATE,
            "file" => self::TYPE_STRING
            /*
            "success" => self::TYPE_BOOL,
            "mark" => self::TYPE_STRING,
            "comment" => self::TYPE_STRING
            */
            );
    }
}
