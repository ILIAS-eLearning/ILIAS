<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilObjCmiXapiVerfication
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilObjCmiXapiVerification extends ilVerificationObject
{
    protected function initType()
    {
        $this->type = "cmxv";
    }
    
    protected function getPropertyMap()
    {
        return array("issued_on" => self::TYPE_DATE,
            "file" => self::TYPE_STRING
        );
    }
}
