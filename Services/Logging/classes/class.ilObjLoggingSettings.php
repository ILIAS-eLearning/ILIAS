<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesLogging
*/

class ilObjLoggingSettings extends ilObject
{
    /**
     * Constructor
     * @access	public
     * @param	integer	$a_id reference_id or object_id
     * @param	boolean	$a_call_by_reference treat the id as reference_id (true) or object_id (false)
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = "logs";
        parent::__construct($a_id, $a_call_by_reference);
    }
} // END class.ilObjLoggingSettings
