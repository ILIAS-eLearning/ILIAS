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

require_once("content/classes/class.ilMetaData.php");

/**
* Class ilStructreObject
*
* Handles StructureObjects of ILIAS Learning Modules (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package application
*/
class ilLMObject
{
	var $ilias;
	var $title;
	var $lm_id;
	var $type;
	var $id;
	var $meta_data;

	function ilLMObject($a_id = 0)
	{
		global $ilias;
	
		$this->ilias =& $ilias;
	}
	
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	function getTitle()
	{
		return $this->title;
	}

	function setType($a_type)
	{
		$this->type = $a_type;
	}

	function getType()
	{
		return $this->type;
	}

	function setLMId($a_lm_id)
	{
		$this->lm_id = $a_lm_id;
		
	}

	function getLMId()
	{
		return $this->lm_id;
	}
	
	function setId($a_id)
	{
		$this->id = $a_id;
	}
	
	function getId()
	{
		return $this->id;
	}

	function create()
	{
		// insert object data
		$query = "INSERT INTO lm_data (title, type, lm_id) ".
			"VALUES ('".$this->getTitle()."','".$this->getType()."', ".$this->getLMId().")";
		$this->ilias->db->query($query);
		$this->setId(getLastInsertId());
		
		// create meta data
		$this->meta_data->setId($this->getId());
		$this->meta_data->setType("pg");
		$this->meta_data->create();

	}
	
	function assignMetaData(&$a_meta_data)
	{
		$this->meta_data =& $a_meta_data;
	}

	
}
?>