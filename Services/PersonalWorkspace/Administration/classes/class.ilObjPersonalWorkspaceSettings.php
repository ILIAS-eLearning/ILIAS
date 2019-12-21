<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Personal Workspace settings
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilObjPersonalWorkspaceSettings extends ilObject
{

     /**
      * @inheritDoc
      */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = "prss";
        parent::__construct($a_id, $a_call_by_reference);
    }
}
