<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject2.php";

/**
 * Wiki settings application class
 * 
 * @author Alex Killing <alex.killing@gmx.de> 
 * @version $Id$
 *
 * @ingroup ModulesWiki
 */
class ilObjWikiSettings extends ilObject2
{
	
	/**
	 * Constructor
	 * 
	 * @param	integer	reference_id or object_id
	 * @param	boolean	treat the id as reference_id (true) or object_id (false)
	 */
	function __construct($a_id = 0,$a_call_by_reference = true)
	{
		parent::__construct($a_id,$a_call_by_reference);
	}

	/**
	 * Init type
	 */
	function initType()
	{
		$this->type = "wiks";
	}

}
?>
