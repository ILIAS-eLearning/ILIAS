<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Help mapping 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup 
 */
class ilHelpMapping
{
	/**
	 * Save screen ids for chapter
	 *
	 * @param
	 * @return
	 */
	function saveScreenIdsForChapter($a_chap, $a_ids)
	{
		global $ilDB;
		
		self::removeScreenIdsOfChapter($a_chap);
		if (is_array($a_ids))
		{
			foreach ($a_ids as $id)
			{
				$id = trim($id);
				$id = explode("/", $id);
				if ($id[0] != "")
				{
					if ($id[1] == "")
					{
						$id[1] = "-";
					}
					$id2 = explode("#", $id[2]);
					if ($id2[0] == "")
					{
						$id2[0] = "-";
					}
					if ($id2[1] == "")
					{
						$id2[1] = "-";
					}
					$ilDB->replace("help_map",
						array("chap" => array("integer", $a_chap),
							"component" => array("text", $id[0]),
							"screen_id" => array("text", $id[1]),
							"screen_sub_id" => array("text", $id2[0]),
							"perm" => array("text", $id2[1])
							),
						array()
						);
				}
			}
		}
	}
	
	/**
	 * Remove screen ids of chapter
	 *
	 * @param
	 * @return
	 */
	function removeScreenIdsOfChapter($a_chap)
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM help_map WHERE ".
			" chap = ".$ilDB->quote($a_chap, "integer")
			);
	}
	
	/**
	 * Get screen ids of chapter
	 *
	 * @param
	 * @return
	 */
	function getScreenIdsOfChapter($a_chap)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM help_map ".
			" WHERE chap = ".$ilDB->quote($a_chap, "integer").
			" ORDER BY component, screen_id, screen_sub_id"
			);
		$screen_ids = array();
		while ($rec  = $ilDB->fetchAssoc($set))
		{
			if ($rec["screen_id"] == "-")
			{
				$rec["screen_id"] = "";
			}
			if ($rec["screen_sub_id"] == "-")
			{
				$rec["screen_sub_id"] = "";
			}
			$id = $rec["component"]."/".$rec["screen_id"]."/".$rec["screen_sub_id"];
			if ($rec["perm"] != "" && $rec["perm"] != "-")
			{
				$id.= "#".$rec["perm"];
			}
			$screen_ids[] = $id;
		}
		return $screen_ids;
	}
	
	/**
	 * Get help sections for screen id
	 *
	 * @param
	 * @return
	 */
	static function getHelpSectionsForId($a_screen_id, $a_ref_id)
	{
		global $ilDB, $ilAccess;
		
		$sc_id = explode("/", $a_screen_id);
		$chaps = array();
		if ($sc_id[0] != "")
		{
			if ($sc_id[1] == "")
			{
				$sc_id[1] = "-";
			}
			if ($sc_id[2] == "")
			{
				$sc_id[2] = "-";
			}
			$set = $ilDB->query("SELECT chap, perm FROM help_map ".
				" WHERE (component = ".$ilDB->quote($sc_id[0], "text").
				" OR component = ".$ilDB->quote("*", "text").")".
				" AND screen_id = ".$ilDB->quote($sc_id[1], "text").
				" AND screen_sub_id = ".$ilDB->quote($sc_id[2], "text")
				);
			while ($rec = $ilDB->fetchAssoc($set))
			{
				if ($rec["perm"] != "" && $rec["perm"] != "-")
				{
					if ($ilAccess->checkAccess($rec["perm"], "", (int) $a_ref_id))
					{
						$chaps[] = $rec["chap"];
					}
				}
				else
				{
					$chaps[] = $rec["chap"];
				}
			}
		}
		return $chaps;
	}
	
	/**
	 * Has given screen Id any sections?
	 *
	 * @param
	 * @return
	 */
	function hasScreenIdSections($a_screen_id, $a_ref_id)
	{
				
		global $ilDB, $ilAccess;
		
		$sc_id = explode("/", $a_screen_id);
		if ($sc_id[0] != "")
		{
			if ($sc_id[1] == "")
			{
				$sc_id[1] = "-";
			}
			if ($sc_id[2] == "")
			{
				$sc_id[2] = "-";
			}
			$set = $ilDB->query("SELECT chap, perm FROM help_map ".
				" WHERE (component = ".$ilDB->quote($sc_id[0], "text").
				" OR component = ".$ilDB->quote("*", "text").")".
				" AND screen_id = ".$ilDB->quote($sc_id[1], "text").
				" AND screen_sub_id = ".$ilDB->quote($sc_id[2], "text")
				);
			while ($rec = $ilDB->fetchAssoc($set))
			{
				if ($rec["perm"] != "" && $rec["perm"] != "-")
				{
					if ($ilAccess->checkAccess($rec["perm"], "", (int) $a_ref_id))
					{
						return true;
					}
				}
				else
				{
					return true;
				}
			}
		}
		return false;
	}
	
}

?>
