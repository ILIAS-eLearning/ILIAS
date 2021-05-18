<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Skill tree object in skill management (repository object class)
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjSkillTree extends ilObject
{
    /**
     * Constructor
     * @access	public
     * @param	integer	reference_id or object_id
     * @param	boolean	treat the id as reference_id (true) or object_id (false)
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = "skee";
        parent::__construct($a_id, $a_call_by_reference);
    }
}
