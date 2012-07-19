<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject2.php";

/**
 * Help settings application class
 * 
 * @author Alex Killing <alex.killing@gmx.de> 
 * @version $Id$
 *
 * @ingroup ServicesHelp
 */
class ilObjHelpSettings extends ilObject2
{
	
	/**
	 * Constructor
	 * 
	 * @param	integer	reference_id or object_id
	 * @param	boolean	treat the id as reference_id (true) or object_id (false)
	 */
	function ilObjHelpSettings($a_id = 0,$a_call_by_reference = true)
	{
		parent::__construct($a_id,$a_call_by_reference);
	}

	/**
	 * Init type
	 */
	function initType()
	{
		$this->type = "hlps";
	}

}
?>
