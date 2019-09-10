<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject.php";

/**
* Class ilObjMail
* contains all functions to manage mail settings of ILIAS3
* 
* @author	Stefan Meyer <meyer@leifos.com>
* @version	$Id$
*
* @extends	ilObject
*/
class ilObjMail extends ilObject
{
    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id,$a_call_by_reference = true)
    {
        $this->type = "mail";
        parent::__construct($a_id,$a_call_by_reference);
    }

} // END class.ilObjMail
?>
