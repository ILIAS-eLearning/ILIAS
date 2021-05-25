<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilObjObjectFolder
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilObjObjectFolder extends ilObject
{
    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id, $a_call_by_reference = true)
    {
        $this->type = "objf";
        parent::__construct($a_id, $a_call_by_reference);
    }


    /**
    * delete objectfolder and all related data
    * DISABLED
    * @access	public
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete()
    {
        // DISABLED
        return false;

        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }
        
        // put here objectfolder specific stuff
        
        // always call parent delete function at the end!!
        return true;
    }
} // END class.ilObjObjectFolder
