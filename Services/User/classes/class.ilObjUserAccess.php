<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectAccess.php");

/**
* Class ilObjUserAccess
*
*
* @author 		Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesUser
*/
class ilObjUserAccess extends ilObjectAccess
{

	function _getCommands()
	{
die();
	}
	
	function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
	{
die();
	}

	/**
	* check whether goto script will succeed
	*/
	function _checkGoto($a_target)
	{
		return true;
	}
}

?>
