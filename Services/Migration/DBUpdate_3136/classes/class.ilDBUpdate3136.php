<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Update class for step 3136
 *
 * @author alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesMigration
 */
class ilDBUpdate3136
{
	/**
	 * Create style class GlossaryLink, link, IntLink
	 *
	 * @param
	 * @return
	 */
	static function copyStyleClass($a_orig_class, $a_class, $a_type, $a_tag, $a_hide = 0)
	{
		global $ilDB;
	
		$set = $ilDB->query("SELECT * FROM object_data WHERE type = 'sty'");
		while ($rec = $ilDB->fetchAssoc($set))	// all styles
		{
			$set2 = $ilDB->query("SELECT * FROM style_char WHERE ".
				"style_id = ".$ilDB->quote($rec["obj_id"], "integer")." AND ".
				"characteristic = ".$ilDB->quote($a_class, "text")." AND ".
				"type = ".$ilDB->quote($a_type, "text"));
			if (!$ilDB->fetchAssoc($set2))
			{
				$q = "INSERT INTO style_char (style_id, type, characteristic, hide)".
					" VALUES (".
					$ilDB->quote($rec["obj_id"], "integer").",".
					$ilDB->quote($a_type, "text").",".
					$ilDB->quote($a_class, "text").",".
					$ilDB->quote($a_hide, "integer").")";
				$ilDB->manipulate($q);
				
				$set3 = $ilDB->query("SELECT * FROM style_parameter WHERE ".
					"style_id = ".$ilDB->quote($rec["obj_id"], "integer")." AND ".
					"type = ".$ilDB->quote($a_type, "text")." AND ".
					"class = ".$ilDB->quote($a_orig_class, "text")." AND ".
					"tag = ".$ilDB->quote($a_tag, "text")
					);
				while ($rec3 = $ilDB->fetchAssoc($set3))	// copy parameters
				{
					$spid = $ilDB->nextId("style_parameter");
					$q = "INSERT INTO style_parameter (id, style_id, type, class, tag, parameter, value)".
						" VALUES (".
						$ilDB->quote($spid, "integer").",".
						$ilDB->quote($rec["obj_id"], "integer").",".
						$ilDB->quote($rec3["type"], "text").",".
						$ilDB->quote($a_class, "text").",".
						$ilDB->quote($a_tag, "text").",".
						$ilDB->quote($rec3["parameter"], "text").",".
						$ilDB->quote($rec3["value"], "text").
						")";
					$ilDB->manipulate($q);
				}	
			}
		}
	}
	
	/**
	 * Add style class
	 *
	 * @param
	 * @return
	 */
	function addStyleClass($a_class, $a_type, $a_tag, $a_parameters = "", $a_hide = 0)
	{
		global $ilDB;
		
		if ($a_parameters == "")
		{
			$a_parameters = array();
		}

		$set = $ilDB->query("SELECT * FROM object_data WHERE type = 'sty'");
		while ($rec = $ilDB->fetchAssoc($set))	// all styles
		{
			$set2 = $ilDB->query("SELECT * FROM style_char WHERE ".
				"style_id = ".$ilDB->quote($rec["obj_id"], "integer")." AND ".
				"characteristic = ".$ilDB->quote($a_class, "text")." AND ".
				"type = ".$ilDB->quote($a_type, "text"));
			if (!$ilDB->fetchAssoc($set2))
			{
				$q = "INSERT INTO style_char (style_id, type, characteristic, hide)".
					" VALUES (".
					$ilDB->quote($rec["obj_id"], "integer").",".
					$ilDB->quote($a_type, "text").",".
					$ilDB->quote($a_class, "text").",".
					$ilDB->quote($a_hide, "integer").")";
	
				$ilDB->manipulate($q);
				foreach ($a_parameters as $k => $v)
				{
					$spid = $ilDB->nextId("style_parameter");
					$q = "INSERT INTO style_parameter (id, style_id, type, class, tag, parameter, value)".
						" VALUES (".
						$ilDB->quote($spid, "integer").",".
						$ilDB->quote($rec["obj_id"], "integer").",".
						$ilDB->quote($a_type, "text").",".
						$ilDB->quote($a_class, "text").",".
						$ilDB->quote($a_tag, "text").",".
						$ilDB->quote($k, "text").",".
						$ilDB->quote($v, "text").
						")";
	
					$ilDB->manipulate($q);
				}
			}
		}
	}

}
?>
