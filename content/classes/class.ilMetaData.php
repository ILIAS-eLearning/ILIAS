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
* Class ilMetaData
*
* Handles Meta Data of ILIAS Learning Objects (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package application
*/
class ilMetaData
{
	var $ilias;

	// attributes of the "General" Section
	var $identifier;		// +, array
	var $title;				// 1
	var $language;			// +, array
	var $description;		// +, array
	var $keyword;			// +, array
	var $coverage;			// ?, optional
	var $structure;			// "Atomic" | "Collection" | "Networked" | "Hierarchical" | "Linear"

	/**
	* Constructor
	* @access	public
	*/
	function ilMetaData()
	{
		global $ilias;

		$this->ilias =& $ilias;

		$identifier = array();
		$title = "";
		$language = array();
		$description = array();
		$keyword = array();
		$this->coverage = "";
		$this->structure = "";
	}

	/**
	* set identifier catalog value
	* note: only one ID implemented currently
	*/
	function setIdentifierCatalog($a_cdata)
	{
		$this->identifier[0]["catalog"] = $a_data;
	}

	/**
	* set identifier entry ID
	* note: only one ID implemented currently
	*/
	function setIdentifierEntryID($a_id)
	{
		$this->identifier[0]["entry_id"] = $a_id;
	}

	/**
	* get identifier catalog value
	* note: only one ID implemented currently
	*/
	function getIdentifierCatalog()
	{
		return $this->identifier[0]["catalog"];
	}

	/**
	* get identifier entry ID
	* note: only one ID implemented currently
	*/
	function getIdentifierEntryID()
	{
		return $this->identifier[0]["entry_id"];
	}

	/**
	* set title
	*/
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	* get title
	*/
	function getTitle($a_title)
	{
		return $a_title;
	}


}
?>
