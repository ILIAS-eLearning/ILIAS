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
	var $import_id;			// +, array
	var $title;				// 1
	var $language;			// +, array
	var $description;		// +, array
	var $keyword;			// +, array
	var $coverage;			// ?, optional
	var $structure;			// "Atomic" | "Collection" | "Networked" | "Hierarchical" | "Linear"
	var $id;
	var $type;

	/**
	* Constructor
	* @access	public
	*/
	function ilMetaData($a_type = "", $a_id = 0)
	{
		global $ilias;

		$this->ilias =& $ilias;

		$import_id = array();
		$title = "";
		$language = array();
		$description = array();
		$keyword = array();
		$this->coverage = "";
		$this->structure = "";
		$this->type = $a_type;
		$this->id = $a_id;

		if($a_id != 0)
		{
			$this->read();
		}
	}

	function read()
	{
		$query = "SELECT * FROM meta_data ".
			"WHERE obj_id = '".$this->id."' AND obj_type='".$this->type."'";
		$meta_set = $this->ilias->db->query($query);
		$meta_rec = $meta_set->fetchRow(DB_FETCHMODE_ASSOC);

		$this->setTitle($meta_rec["title"]);
	}

	/**
	* set identifier catalog value
	* note: only one ID implemented currently
	*/
	function setImportIdentifierCatalog($a_cdata)
	{
		$this->import_id[0]["catalog"] = $a_data;
	}

	/**
	* set identifier entry ID
	* note: only one ID implemented currently
	*/
	function setImportIdentifierEntryID($a_id)
	{
		$this->import_id[0]["entry_id"] = $a_id;
	}

	/**
	* get identifier catalog value
	* note: only one ID implemented currently
	*/
	function getImportIdentifierCatalog()
	{
		return $this->import_id[0]["catalog"];
	}

	/**
	* get identifier entry ID
	* note: only one ID implemented currently
	*/
	function getImportIdentifierEntryID()
	{
		return $this->import_id[0]["entry_id"];
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
	function getTitle()
	{
		return $this->title;
	}

	/**
	* set id
	*/
	function setID($a_id)
	{
		$this->id = $a_id;
	}

	function getID()
	{
		return $this->id;
	}

	function setType($a_type)
	{
		$this->type = $a_type;
	}

	function getType()
	{
		return $this->type;
	}

	function create()
	{
		$query = "INSERT INTO meta_data (obj_id, obj_type, title) VALUES ".
			"('".$this->getId()."','".$this->getType()."','".$this->getTitle()."')";
		$this->ilias->db->query($query);
	}



}
?>
