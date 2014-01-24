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
	 * Get type
	 *
	 * @param
	 * @return
	 */
	function initType()
	{
		$this->type = "wiks";
	}
	
}
?>
