<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilTestSkillLevelThresholdImporter
{
	/*
	include_once("./Services/Skill/classes/class.ilBasicSkill.php");
	$r = ilBasicSkill::getLevelIdForImportId($a_source_inst_id,
	$a_level_import_id);
	
	$results[] = array("level_id" => $rec["id"], "creation_date" =>
	$rec["creation_date"]);
	*/
	
	/**
	 * @return bool
	 */
	public function	import()
	{
		return true;
	}
}