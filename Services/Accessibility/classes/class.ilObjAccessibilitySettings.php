<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject.php";

/**
* Class ilObjAccessibilitySettings
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesAccessibility
*/
class ilObjAccessibilitySettings extends ilObject
{
    /**
     * @var ilDB
     */
    protected $db;

    
    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->type = "accs";
        parent::__construct($a_id, $a_call_by_reference);
    }

    /**
    * update object data
    *
    * @access	public
    * @return	boolean
    */
    public function update()
    {
        $ilDB = $this->db;
        
        if (!parent::update()) {
            return false;
        }

        return true;
    }
    
    /**
    * read
    */
    public function read()
    {
        $ilDB = $this->db;

        parent::read();
    }
    

    
    

    /**
    * delete object and all related data
    *
    * @access	public
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete()
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }
        
        //put here your module specific stuff
        
        return true;
    }
}
