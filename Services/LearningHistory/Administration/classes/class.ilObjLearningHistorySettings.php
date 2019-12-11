<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Notes settings
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilObjLearningHistorySettings extends ilObject
{

     /**
     * Constructor
     * ilObjNotesSettings constructor.
     * @param int $a_id
     * @param bool $a_call_by_reference
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        global $DIC;

        $this->type = "lhts";
        parent::__construct($a_id, $a_call_by_reference);
    }
}
