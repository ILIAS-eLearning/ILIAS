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
							"perm" => array("text", $id2[1]),
							"module_id" => array("integer", 0)
							),
						array()
						);
				}
			}
		}
	}
	
	/**
	 * Save mapping entry
	 *
	 * @param
	 * @return
	 */
	static function saveMappingEntry($a_chap, $a_comp, $a_screen_id,
		$a_screen_sub_id, $a_perm, $a_module_id = 0)
	{
		global $ilDB;
		
		$ilDB->replace("help_map",
			array("chap" => array("integer", $a_chap),
				"component" => array("text", $a_comp),
				"screen_id" => array("text", $a_screen_id),
				"screen_sub_id" => array("text", $a_screen_sub_id),
				"perm" => array("text", $a_perm),
				"module_id" => array("integer", $a_module_id)
				),
			array()
			);
	}
	
	
	/**
	 * Remove screen ids of chapter
	 *
	 * @param
	 * @return
	 */
	function removeScreenIdsOfChapter($a_chap, $a_module_id = 0)
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM help_map WHERE ".
			" chap = ".$ilDB->quote($a_chap, "integer").
			" AND module_id = ".$ilDB->quote($a_module_id, "integer")
			);
	}
	
	/**
	 * Get screen ids of chapter
	 *
	 * @param
	 * @return
	 */
	function getScreenIdsOfChapter($a_chap, $a_module_id = 0)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM help_map ".
			" WHERE chap = ".$ilDB->quote($a_chap, "integer").
			" AND module_id = ".$ilDB->quote($a_module_id, "integer").
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
		global $ilDB, $ilAccess, $ilSetting, $rbacreview, $ilUser, $ilObjDataCache;

		if (OH_REF_ID > 0)
		{
			$module = 0;
		}
		else
		{
			$module = (int) $ilSetting->get("help_module");
			if ($module == 0)
			{
				return array();
			}
		}

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
			$set = $ilDB->query("SELECT chap, perm FROM help_map JOIN lm_tree".
				" ON (help_map.chap = lm_tree.child) ".
				" WHERE (component = ".$ilDB->quote($sc_id[0], "text").
				" OR component = ".$ilDB->quote("*", "text").")".
				" AND screen_id = ".$ilDB->quote($sc_id[1], "text").
				" AND screen_sub_id = ".$ilDB->quote($sc_id[2], "text").
				" AND module_id = ".$ilDB->quote($module, "integer").
				" ORDER BY lm_tree.lft"
				);
			while ($rec = $ilDB->fetchAssoc($set))
			{
				if ($rec["perm"] != "" && $rec["perm"] != "-")
				{
					// check special "create*" permission
					if ($rec["perm"] == "create*")
					{
						$has_create_perm = false;
						
						// check owner
						if ($ilUser->getId() == $ilObjDataCache->lookupOwner(ilObject::_lookupObjId($a_ref_id)))
						{
							$has_create_perm = true;
						}
						else if ($rbacreview->isAssigned($ilUser->getId(), SYSTEM_ROLE_ID)) // check admin
						{
							$has_create_perm = true;
						}
						else if ($ilAccess->checkAccess("read", "", (int) $a_ref_id))
						{
							$perm = $rbacreview->getUserPermissionsOnObject($ilUser->getId(), (int) $a_ref_id);
							foreach ($perm as $p)
							{
								if (substr($p, 0, 7) == "create_")
								{
									$has_create_perm = true;
								}
							}
						}
						if ($has_create_perm)
						{
							$chaps[] = $rec["chap"];
						}
					}
					else if ($ilAccess->checkAccess($rec["perm"], "", (int) $a_ref_id))
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
	 * Note: We removed the "ref_id" parameter here, since this method
	 * should be fast. It is used to decide whether the help button should
	 * appear or not. We assume that there is at least one section for
	 * users with the "read" permission.
	 *
	 * @param
	 * @return
	 */
	function hasScreenIdSections($a_screen_id)
	{
		global $ilDB, $ilAccess, $ilSetting, $ilUser;
		
		if ($ilUser->getLanguage() != "de")
		{
			return false;
		}
		
		if ($ilSetting->get("help_mode") == "2")
		{
			return false;
		}

		if (OH_REF_ID > 0)
		{
			$module = 0;
		}
		else
		{
			$module = (int) $ilSetting->get("help_module");
			if ($module == 0)
			{
				return false;
			}
		}

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
				" AND screen_sub_id = ".$ilDB->quote($sc_id[2], "text").
				" AND module_id = ".$ilDB->quote($module, "integer")
				);
			while ($rec = $ilDB->fetchAssoc($set))
			{
				return true;
				
				// no permission check, since it takes to much performance
				// getHelpSectionsForId() does the permission checks.
				/*if ($rec["perm"] != "" && $rec["perm"] != "-")
				{
					if ($ilAccess->checkAccess($rec["perm"], "", (int) $a_ref_id))
					{
						return true;
					}
				}
				else
				{
					return true;
				}*/
			}
		}
		return false;
	}
	
	/**
	 * Delete entries of module
	 *
	 * @param
	 * @return
	 */
	static function deleteEntriesOfModule($a_id)
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM help_map WHERE ".
			" module_id = ".$ilDB->quote($a_id, "integer"));
		
	}
	
	
}

?>
