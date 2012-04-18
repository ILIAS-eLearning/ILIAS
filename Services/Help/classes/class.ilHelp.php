<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Online help application class
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 */
class ilHelp
{
	/**
	 * Get tooltip for id
	 *
	 * @param
	 * @return
	 */
	static function getTooltipPresentationText($a_tt_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT tt_text FROM help_tooltip ".
			" WHERE tt_id = ".$ilDB->quote($a_tt_id, "text")
			);
		$rec = $ilDB->fetchAssoc($set);
		if ($rec["tt_text"] != "")
		{
			return $rec["tt_text"];
		}
		return "<i>".$a_tt_id."</i>";
	}

		/**
	 * Get tab tooltip text
	 *
	 * @param string $a_tab_id tab id
	 * @return string tooltip text
	 */
	static function getObjCreationTooltipText($a_type)
	{
		return self::getTooltipPresentationText($a_type."_create");
	}

	
	/**
	 * Get all tooltips
	 *
	 * @param
	 * @return
	 */
	static function getAllTooltips($a_comp = "")
	{
		global $ilDB;
		
		$q = "SELECT * FROM help_tooltip";
		if ($a_comp != "")
		{
			$q.= " WHERE comp = ".$ilDB->quote($a_comp, "text");
		}
		$set = $ilDB->query($q);
		$tts = array();
		while ($rec  = $ilDB->fetchAssoc($set))
		{
			$tts[$rec["id"]] = array("id" => $rec["id"], "text" => $rec["tt_text"],
				"tt_id" => $rec["tt_id"]);
		}
		return $tts;
	}
	
	/**
	 * Add tooltip
	 *
	 * @param
	 * @return
	 */
	function addTooltip($a_tt_id, $a_text)
	{
		global $ilDB;
		
		$fu = strpos($a_tt_id, "_");
		$comp = substr($a_tt_id, 0, $fu);
		
		$nid = $ilDB->nextId("help_tooltip");
		$ilDB->manipulate("INSERT INTO help_tooltip ".
			"(id, tt_text, tt_id, comp) VALUES (".
			$ilDB->quote($nid, "integer").",".
			$ilDB->quote($a_text, "text").",".
			$ilDB->quote($a_tt_id, "text").",".
			$ilDB->quote($comp, "text").
			")");
	}
	
	/**
	 * Update tooltip
	 *
	 * @param
	 * @return
	 */
	static function updateTooltip($a_id, $a_text, $a_tt_id)
	{
		global $ilDB;

		$fu = strpos($a_tt_id, "_");
		$comp = substr($a_tt_id, 0, $fu);
		
		$ilDB->manipulate("UPDATE help_tooltip SET ".
			" tt_text = ".$ilDB->quote($a_text, "text").", ".
			" tt_id = ".$ilDB->quote($a_tt_id, "text").", ".
			" comp = ".$ilDB->quote($comp, "text").
			" WHERE id = ".$ilDB->quote($a_id, "integer")
			);
	}
	
	
	/**
	 * Get all tooltip components
	 *
	 * @param
	 * @return
	 */
	static function getTooltipComponents()
	{
		global $ilDB, $lng;
		
		$set = $ilDB->query("SELECT DISTINCT comp FROM help_tooltip ".
			" ORDER BY comp ");
		$comps[""] = "- ".$lng->txt("help_all")." -";
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$comps[$rec["comp"]] = $rec["comp"];
		}
		return $comps;
	}
	
	/**
	 * Delete tooltip
	 *
	 * @param
	 * @return
	 */
	static function deleteTooltip($a_id)
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM help_tooltip WHERE ".
			" id = ".$ilDB->quote($a_id, "integer")
			);
	}
	
}
?>