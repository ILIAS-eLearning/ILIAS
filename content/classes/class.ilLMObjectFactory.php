<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

require_once ("content/classes/class.ilPageObject.php");
require_once ("content/classes/class.ilStructureObject.php");

/**
* Class ilLMObjectFactory
*
* Creates StructureObject or PageObject by ID (see table lm_data)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilLMObjectFactory
{
	function getInstance($a_id = 0)
	{
		global $ilias;

		$query = "SELECT * FROM lm_data WHERE obj_id = '$a_id'";
		$obj_set = $ilias->db->query($query);
		$obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);

		switch($obj_rec["type"])
		{
			case "st":
				$obj =& new ilStructureObject();
				$obj->setId($obj_rec["obj_id"]);
				$obj->setDataRecord($obj_rec);
				$obj->read();
				break;

			case "pg":
				$obj =& new ilPageObject();
				$obj->setId($obj_rec["obj_id"]);
				$obj->setDataRecord($obj_rec);
				$obj->read();
				break;
		}
		return $obj;
	}
}
?>
