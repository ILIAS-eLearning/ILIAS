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
* Class ilPageContent
*
* Content object of ilPageObject (see ILIAS DTD). Every concrete object
* should be an instance of a class derived from ilPageContent (e.g. ilParagraph,
* ilMediaObject, ...)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilPageContent
{
	var $ilias;
	var $type;
	var $ed_id; 		// hierarchical editing id

	/**
	* Constructor
	* @access	public
	*/
	function ilPageContent()
	{
		global $ilias;

		$this->ilias =& $ilias;
	}

	/**
	* abstract function, must be implemented by derived classes
	*/
	function getXML($a_utf8_encoded = false, $a_short_mode = false, $a_incl_ed_ids = false)
	{
	}

	function setType($a_type)
	{
		$this->type = $a_type;
	}

	function getType()
	{
		return $this->type;
	}

	/**
	* set editing id
	*/
	function setEdId($a_ed_id)
	{
		$this->ed_id = $a_ed_id;
	}

	/**
	* get editing id
	*/
	function getEdId()
	{
		return $this->ed_id;
	}

	/**
	* static class method
	* increases an hierarchical editing id at lowest level (last number)
	*/
	function incEdId($ed_id)
	{
		$id = explode("_", $ed_id);
		$id[count($id) - 1]++;
		return implode($id, "_");
	}
}
?>
