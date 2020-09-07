<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject.php";

/**
 * Class ilObjStyleSettings
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class ilObjStyleSettings extends ilObject
{
    /**
     * @param	integer	$a_id reference_id or object_id
     * @param	boolean	$a_call_by_reference treat the id as reference_id (true) or object_id (false)
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = "stys";
        parent::__construct($a_id, $a_call_by_reference);
    }
}
