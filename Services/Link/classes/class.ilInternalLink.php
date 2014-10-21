<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilInternalLink
*
* Some methods to handle internal links
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilInternalLink
{
	/**
	 * Delete all links of a given source
	 *
	 * @param string $a_source_type source type
	 * @param int $a_source_if source id
	 * @param int $a_lang source language
	 */
	function _deleteAllLinksOfSource($a_source_type, $a_source_id, $a_lang = "-")
	{
		global $ilias, $ilDB;

		$lang_where = "";
		if ($a_lang != "")
		{
			$lang_where = " AND source_lang = ".$ilDB->quote($a_lang, "text");
		}
		
		$q = "DELETE FROM int_link WHERE source_type = ".
			$ilDB->quote($a_source_type, "text")." AND source_id=".
			$ilDB->quote((int) $a_source_id, "integer").
			$lang_where;
		$ilDB->manipulate($q);
	}

	/**
	 * Delete all links to a given target
	 *
	 * @param	string		$a_target_type		target type
	 * @param	int			$a_target_id		target id
	 * @param	int			$a_target_inst		target installation id
	 */
	function _deleteAllLinksToTarget($a_target_type, $a_target_id, $a_target_inst = 0)
	{
		global $ilias, $ilDB;

		$ilDB->manipulateF("DELETE FROM int_link WHERE target_type = %s ".
			" AND target_id = %s AND target_inst = %s ",
			array("text", "integer", "integer"),
			array($a_target_type, (int) $a_target_id, (int) $a_target_inst));
	}

	/**
	 * save internal link information
	 *
	 * @param	string		$a_source_type		source type
	 * @param	int			$a_source_if		source id
	 * @param	string		$a_target_type		target type
	 * @param	int			$a_target_id		target id
	 * @param	int			$a_target_inst		target installation id
	 */
	function _saveLink($a_source_type, $a_source_id, $a_target_type, $a_target_id, $a_target_inst = 0,
		$a_source_lang = "-")
	{
		global $ilDB;

		$ilDB->replace("int_link",
			array(
				"source_type" => array("text", $a_source_type),
				"source_id" => array("integer", (int) $a_source_id),
				"source_lang" => array("text", $a_source_lang),
				"target_type" => array("text", $a_target_type),
				"target_id" => array("integer", (int) $a_target_id),
				"target_inst" => array("integer", (int) $a_target_inst)
			),
			array()
		);
	}

	/**
	 * get all sources of a link target
	 *
	 * @param	string		$a_target_type		target type
	 * @param	int			$a_target_id		target id
	 * @param	int			$a_target_inst		target installation id
	 *
	 * @return	array		sources (array of array("type", "id"))
	 */
	function _getSourcesOfTarget($a_target_type, $a_target_id, $a_target_inst)
	{
		global $ilias, $ilDB;

		$q = "SELECT * FROM int_link WHERE ".
			"target_type = ".$ilDB->quote($a_target_type, "text")." AND ".
			"target_id = ".$ilDB->quote((int) $a_target_id, "integer")." AND ".
			"target_inst = ".$ilDB->quote((int) $a_target_inst, "integer");
		$source_set = $ilDB->query($q);
		$sources = array();
		while ($source_rec = $ilDB->fetchAssoc($source_set))
		{
			$sources[$source_rec["source_type"].":".$source_rec["source_id"].":".$source_rec["source_lang"]] =
				array("type" => $source_rec["source_type"], "id" => $source_rec["source_id"],
					"lang" => $source_rec["source_lang"]);
		}

		return $sources;
	}

	/**
	 * Get all targets of a source object (e.g., a page)
	 *
	 * @param	string		$a_source_type		source type (e.g. "lm:pg" | "dbk:pg")
	 * @param	int			$a_source_id		source id
	 *
	 * @return	array		targets (array of array("type", "id", "inst"))
	 */
	function _getTargetsOfSource($a_source_type, $a_source_id, $a_source_lang = "-")
	{
		global $ilDB;

		$lang_where = "";
		if ($a_source_lang != "")
		{
			$lang_where = " AND source_lang = ".$ilDB->quote($a_source_lang, "text");
		}

		$q = "SELECT * FROM int_link WHERE ".
			"source_type = ".$ilDB->quote($a_source_type, "text")." AND ".
			"source_id = ".$ilDB->quote((int) $a_source_id, "integer").
			$lang_where;

		$target_set = $ilDB->query($q);
		$targets = array();
		while ($target_rec = $ilDB->fetchAssoc($target_set))
		{
			$targets[$target_rec["target_type"].":".$target_rec["target_id"].":".$target_rec["target_inst"]] =
				array("type" => $target_rec["target_type"], "id" => $target_rec["target_id"],
				"inst" => $target_rec["target_inst"]);
		}

		return $targets;
	}

	/**
	 * Get current id for an import id
	 *
	 * @param	string		$a_type			target type ("PageObject" | "StructureObject" |
	 *										"GlossaryItem" | "MediaObject")
	 * @param	string		$a_target		import target id (e.g. "il_2_pg_22")
	 *
	 * @return	string		current target id (e.g. "il__pg_244")
	 */
	function _getIdForImportId($a_type, $a_target)
	{
		switch($a_type)
		{
			case "PageObject":
				$id = ilLMObject::_getIdForImportId($a_target);
				if($id > 0)
				{
					return "il__pg_".$id;
				}
				break;

			case "StructureObject":
				$id = ilLMObject::_getIdForImportId($a_target);
				if($id > 0)
				{
					return "il__st_".$id;
				}
				break;

			case "GlossaryItem":
				$id = ilGlossaryTerm::_getIdForImportId($a_target);
				if($id > 0)
				{
					return "il__git_".$id;
				}
				break;

			case "WikiPage":
				// no import IDs for wiki pages (yet)
				//$id = ilGlossaryTerm::_getIdForImportId($a_target);
				$id = 0;
				if($id > 0)
				{
					return "il__wpage_".$id;
				}
				break;

			case "MediaObject":
				$id = ilObjMediaObject::_getIdForImportId($a_target);
				if($id > 0)
				{
					return "il__mob_".$id;
				}
				break;
				
			case "RepositoryItem":
				
				$tarr = explode("_", $a_target);
				$import_id = $a_target;
				
				// if a ref id part is given, strip this
				// since this will not be part of an import id
				if ($tarr[4] != "")
				{
					$import_id = $tarr[0]."_".$tarr[1]."_".$tarr[2]."_".$tarr[3]; 
				}
				if (ilInternalLink::_extractInstOfTarget($a_target) == IL_INST_ID
					&& IL_INST_ID > 0)
				{
					// does it have a ref id part?
					if ($tarr[4] != "")
					{
						return "il__obj_".$tarr[4];
					}
				}
				
				$id = ilObject::_getIdForImportId($import_id);
//echo "-$a_target-$id-";
				// get ref id for object id
				// (see ilPageObject::insertInstIntoIDs for the export procedure)
				if($id > 0)
				{
					$refs = ilObject::_getAllReferences($id);
//var_dump($refs);
					foreach ($refs as $ref)
					{
						return "il__obj_".$ref;
					}
				}
				break;

		}
		return false;
	}

	/**
	 * Check if internal link refers to a valid target
	 *
	 * @param	string		$a_type			target type ("PageObject" | "StructureObject" |
	 *										"GlossaryItem" | "MediaObject")
	 * @param	string		$a_target		target id, e.g. "il__pg_244")
	 *
	 * @return	boolean		true/false
	 */
	function _exists($a_type, $a_target)
	{
		global $tree;
		
		switch($a_type)
		{
			case "PageObject":
			case "StructureObject":
				return ilLMObject::_exists($a_target);
				break;

			case "GlossaryItem":
				return ilGlossaryTerm::_exists($a_target);
				break;

			case "MediaObject":
				return ilObjMediaObject::_exists($a_target);
				break;
				
			case "WikiPage":
				include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
				return ilWikiPage::_exists("wiki", (int)$a_target);
				break;
				
			case "RepositoryItem":
				if (is_int(strpos($a_target, "_")))
				{
					$ref_id = ilInternalLink::_extractObjIdOfTarget($a_target);
					return $tree->isInTree($ref_id);
				}
				break;
		}
		return false;
	}

	
	/**
	 * Extract installation id out of target
	 *
	 * @param	string		$a_target		import target id (e.g. "il_2_pg_22")
	 */
	function _extractInstOfTarget($a_target)
	{
		if (!is_int(strpos($a_target, "__")))
		{
			$target = explode("_", $a_target);
			if ($target[1] > 0)
			{
				return $target[1]; 
			}
		}
		return false;
	}
	
	/**
	 * Removes installation id from target string
	 *
	 * @param	string		$a_target		import target id (e.g. "il_2_pg_22")
	 */
	function _removeInstFromTarget($a_target)
	{
		if (!is_int(strpos($a_target, "__")))
		{
			$target = explode("_", $a_target);
			if ($target[1] > 0)
			{
				return "il__".$target[2]."_".$target[3]; 
			}
		}
		return false;
	}
	
	/**
	 * Extract object id out of target
	 *
	 * @param	string		$a_target		import target id (e.g. "il_2_pg_22")
	 */
	function _extractObjIdOfTarget($a_target)
	{
		$target = explode("_", $a_target);
		return $target[count($target) - 1];
	}

	/**
	 * Extract type out of target
	 *
	 * @param	string		$a_target		import target id (e.g. "il_2_pg_22")
	 */
	function _extractTypeOfTarget($a_target)
	{
		$target = explode("_", $a_target);
		return $target[count($target) - 2];
	}
}
?>
