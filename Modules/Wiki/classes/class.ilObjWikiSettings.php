<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject.php";

/**
 * Wiki settings application class
 * 
 * @author Alex Killing <alex.killing@gmx.de> 
 * @version $Id$
 *
 * @ingroup ModulesWiki
 */
class ilObjWikiSettings extends ilObject
{
	/**
	 * Constructor
	 *
	 * @param	integer	reference_id or object_id
	 * @param	boolean	treat the id as reference_id (true) or object_id (false)
	 */
	public function __construct($a_id = 0, $a_call_by_reference = true)
	{
		$this->type = 'wiks';
		parent::__construct($a_id, $a_call_by_reference);
	}
}
?>
