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

}
?>
