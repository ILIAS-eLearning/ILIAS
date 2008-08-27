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

/**
* Forum listener. Listens to events of other components.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ModulesForum
*/
class ilTaggingAppEventListener
{
	/**
	* Handle an event in a listener.
	*
	* @param	string	$a_component	component, e.g. "Modules/Forum" or "Services/User"
	* @param	string	$a_event		event e.g. "createUser", "updateUser", "deleteUser", ...
	* @param	array	$a_parameter	parameter array (assoc), array("name" => ..., "phone_office" => ...)
	*/
	static function handleEvent($a_component, $a_event, $a_parameter)
	{
		include_once("./Services/Tagging/classes/class.ilTagging.php");
		
		switch($a_component)
		{
			case "Services/Object":
				switch ($a_event)
				{
					case "toTrash":
						if (!ilObject::_hasUntrashedReference($a_parameter["obj_id"]))
						{
							ilTagging::setTagsOfObjectOffline($a_parameter["obj_id"],
								ilObject::_lookupType($a_parameter["obj_id"]), 0, "");
						}
						break;

					case "undelete":
						ilTagging::setTagsOfObjectOffline($a_parameter["obj_id"],
							ilObject::_lookupType($a_parameter["obj_id"]), 0, "", false);
						break;

					case "delete":
						$ref_ids  = ilObject::_getAllReferences($a_parameter["obj_id"]);
						if (count($ref_ids) == 0)
						{
							ilTagging::deleteTagsOfObject($a_parameter["obj_id"],
								$a_parameter["type"], 0, "");
						}
						break;
				}
				break;
		}
	}
}
?>
