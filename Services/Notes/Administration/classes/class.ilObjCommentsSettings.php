<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Comments settings
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilObjCommentsSettings extends ilObject
{

     /**
     * Constructor
     * @param int $a_id
     * @param bool $a_call_by_reference
     */
    function __construct($a_id = 0,$a_call_by_reference = true)
    {
        $this->type = "coms";
        parent::__construct($a_id,$a_call_by_reference);
    }


}
?>
