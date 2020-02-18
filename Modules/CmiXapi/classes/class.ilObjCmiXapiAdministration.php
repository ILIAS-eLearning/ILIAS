<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilObjCmiXapiAdministration
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilObjCmiXapiAdministration extends ilObject
{
    public function __construct($a_id = 0, $a_reference = true)
    {
        parent::__construct($a_id, $a_reference);
        
        $this->type = "cmis";
    }
}
