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

require_once ("./Modules/Scorm2004/classes/class.ilSCORM2004PageNode.php");
require_once ("./Modules/Scorm2004/classes/class.ilSCORM2004Chapter.php");
require_once ("./Modules/Scorm2004/classes/class.ilSCORM2004SeqChapter.php");
require_once ("./Modules/Scorm2004/classes/class.ilSCORM2004Sco.php");

/**
* Class ilSCORM2004NodeFactory
*
* Factory for SCORM Editor Tree nodes (Chapters/SCOs/Pages)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScorm2004
*/
class ilSCORM2004NodeFactory
{
	static function getInstance($a_slm_object, $a_id = 0, $a_halt = true)
	{
		global $ilias, $ilDB;

		$query = "SELECT * FROM sahs_sc13_tree_node WHERE obj_id = ".
			$ilDB->quote($a_id, "integer");
		$obj_set = $ilDB->query($query);
		$obj_rec = $ilDB->fetchAssoc($obj_set);
		$obj = null;
		switch($obj_rec["type"])
		{
			case "chap":
				$obj =& new ilSCORM2004Chapter($a_slm_object);
				$obj->setId($obj_rec["obj_id"]);
				$obj->setDataRecord($obj_rec);
				$obj->read();
				break;

			case "seqc":
				$obj =& new ilSCORM2004SeqChapter($a_slm_object);
				$obj->setId($obj_rec["obj_id"]);
				$obj->setDataRecord($obj_rec);
				$obj->read();
				break;
					
			case "sco":
				$obj =& new ilSCORM2004Sco($a_slm_object);
				$obj->setId($obj_rec["obj_id"]);
				$obj->setDataRecord($obj_rec);
				$obj->read();
				break;

			case "page":
				$obj =& new ilSCORM2004PageNode($a_slm_object, 0, $a_halt);
				$obj->setId($obj_rec["obj_id"]);
				$obj->setDataRecord($obj_rec);
				$obj->read();
				break;
		}
		return $obj;
	}

}
?>
