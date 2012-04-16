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
	 * Get all tooltips
	 *
	 * @param
	 * @return
	 */
	static function getAllTooltips()
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM help_tooltip");
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
		
		$nid = $ilDB->nextId("help_tooltip");
		$ilDB->manipulate("INSERT INTO help_tooltip ".
			"(id, tt_text, tt_id) VALUES (".
			$ilDB->quote($nid, "integer").",".
			$ilDB->quote($a_text, "text").",".
			$ilDB->quote($a_tt_id, "text").
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
		
		$ilDB->manipulate("UPDATE help_tooltip SET ".
			" tt_text = ".$ilDB->quote($a_text, "text").", ".
			" tt_id = ".$ilDB->quote($a_tt_id, "text").
			" WHERE id = ".$ilDB->quote($a_id, "integer")
			);
	}
	
}
?>