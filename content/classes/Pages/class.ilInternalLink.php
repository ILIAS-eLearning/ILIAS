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

/**
* Class ilInternalLink
*
* Some methods to handle internal links
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilInternalLink
{
	/**
	* delete all links of a given source
	*
	* @param	string		$a_source_type		source type
	* @param	int			$a_source_if		source id
	*/
	function _deleteAllLinksOfSource($a_source_type, $a_source_id)
	{
		global $ilias;

		$q = "DELETE FROM int_link WHERE source_type='$a_source_type' AND source_id='$a_source_id'";
		$ilias->db->query($q);
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
	function _saveLink($a_source_type, $a_source_id, $a_target_type, $a_target_id, $a_target_inst = 0)
	{
		global $ilias;

		$q = "REPLACE INTO int_link (source_type, source_id, target_type, target_id, target_inst) VALUES".
			" ('$a_source_type', '$a_source_id', '$a_target_type', '$a_target_id', '$a_target_inst')";
		$ilias->db->query($q);
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
		global $ilias;

		$q = "SELECT * FROM int_link WHERE ".
			"target_type = '$a_target_type' AND ".
			"target_id = '$a_target_id' AND ".
			"target_inst = '$a_target_inst'";
		$source_set = $ilias->db->query($q);
		$sources = array();
		while ($source_rec = $source_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$sources[$source_rec["source_type"].":".$source_rec["source_id"]] =
				array("type" => $source_rec["source_type"], "id" => $source_rec["source_id"]);
		}

		return $sources;
	}

	/**
	* get current id for an import id
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
					return "il__pg_".$id;
				}
				break;

			case "GlossaryItem":
				$id = ilGlossaryTerm::_getIdForImportId($a_target);
				if($id > 0)
				{
					return "il__git_".$id;
				}
				break;

			case "MediaObject":
				$id = ilObjMediaObject::_getIdForImportId($a_target);
				if($id > 0)
				{
					return "il__mob_".$id;
				}
				break;
		}
		return false;
	}
}
?>
