<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilLMObjectFactory
*
* Creates StructureObject or PageObject by ID (see table lm_data)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
*/
class ilLMObjectFactory
{
	static function getInstance(&$a_content_obj, $a_id = 0, $a_halt = true)
	{
		global $DIC;

		$ilDB = $DIC->database();

		$query = "SELECT * FROM lm_data WHERE obj_id = ".
			$ilDB->quote($a_id, "integer");
		$obj_set = $ilDB->query($query);
		$obj_rec = $ilDB->fetchAssoc($obj_set);

		switch($obj_rec["type"])
		{
			case "st":
				$obj = new ilStructureObject($a_content_obj);
				$obj->setId($obj_rec["obj_id"]);
				$obj->setDataRecord($obj_rec);
				$obj->read();
				break;

			case "pg":
				$obj = new ilLMPageObject($a_content_obj, 0, $a_halt);
				$obj->setId($obj_rec["obj_id"]);
				$obj->setDataRecord($obj_rec);
				$obj->read();
				break;
		}
		return $obj;
	}

}
?>
