<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

include_once("./Services/ContainerReference/classes/class.ilContainerReferenceAccess.php");

/** 
* 
* 
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
* 
* 
* @ingroup ModulesCategoryReference
*/

class ilObjCategoryReferenceAccess extends ilContainerReferenceAccess
{
	/**
	 * get commands
	 * 
	 * Depends on permissions
	 * 
	 * @param int $a_ref_id Reference id of course link
	 * 
	 * this method returns an array of all possible commands/permission combinations
	 * 
	 * example:	
	 * $commands = array
	 *	(
	 *		array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
	 *		array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
	 *	);
	 */
	function _getCommands($a_ref_id)
	{
		global $ilAccess;

		if($ilAccess->checkAccess('write','',$a_ref_id))
		{
			// Only local (reference specific commands)
			$commands = array
			(
				array("permission" => "visible", "cmd" => "", "lang_var" => "show","default" => true),
				array("permission" => "write", "cmd" => "editReference", "lang_var" => "edit")
			);
		}
		else
		{
			include_once('./Modules/Category/classes/class.ilObjCategoryAccess.php');
			$commands = ilObjCategoryAccess::_getCommands();
		}
		return $commands;
	}
	
} 
?>