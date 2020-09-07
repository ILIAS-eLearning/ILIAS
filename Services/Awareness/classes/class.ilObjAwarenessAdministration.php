<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject.php";

/**
 * Class ilObjAwarenessAdministration
 *
 * @author Alex Killing <killing@leifos.com>
 * @version $Id$
 *
 * @package ServicesAwareness
 */
class ilObjAwarenessAdministration extends ilObject
{
    /**
     * Constructor
     *
     * @param	integer	reference_id or object_id
     * @param	boolean	treat the id as reference_id (true) or object_id (false)
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = "awra";
        parent::__construct($a_id, $a_call_by_reference);

        $this->lng->loadLanguageModule("awrn");
    }

    /**
     * update object data
     *
     * @return	boolean
     */
    public function update()
    {
        if (!parent::update()) {
            return false;
        }

        // put here object specific stuff
        
        return true;
    }
}
