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


require_once "classes/class.ilObject.php";

/**
* Class ilObjMediaObject
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @extends ilObject
* @package ilias-core
*/
class ilObjMediaObject extends ilObject
{

	var $meta_data;

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjMediaObject($a_id = 0, $a_call_by_reference = false)
	{
		$this->type = "mob";

		if($a_call_by_reference)
		{
			$this->ilias->raiseError("Can't instantiate media object via reference id.",$this->ilias->error_obj->FATAL);
		}

		parent::ilObject($a_id, false);
	}

	function setRefId()
	{
		$this->ilias->raiseError("Operation ilObjMedia::setRefId() not allowed.",$this->ilias->error_obj->FATAL);
	}

	function getRefId()
	{
		$this->ilias->raiseError("Operation ilObjMedia::getRefId() not allowed.",$this->ilias->error_obj->FATAL);
	}

	function putInTree()
	{
		$this->ilias->raiseError("Operation ilObjMedia::putInTree() not allowed.",$this->ilias->error_obj->FATAL);
	}

	function createReference()
	{
		$this->ilias->raiseError("Operation ilObjMedia::createReference() not allowed.",$this->ilias->error_obj->FATAL);
	}

	function setTitle($a_title)
	{
		$this->meta_data->setTitle($a_title);
	}

	function getTitle()
	{
		return $this->meta_data->getTitle();
	}

	/**
	* assign meta data object
	*/
	function assignMetaData(&$a_meta_data)
	{
		$this->meta_data =& $a_meta_data;
	}

	/**
	* get meta data object
	*/
	function &getMetaData()
	{
		return $this->meta_data;
	}

	function create()
	{
		parent::create();

		// create meta data
//echo "<b>CREATING OBJMEDIA</b>:".$this->getId().":<br>";
		$this->meta_data->setId($this->getId());
		$this->meta_data->setType($this->getType());
		$this->meta_data->create();
	}

	function update()
	{
		parent::update();

		// create meta data
		$this->meta_data->setId($this->getId());
		$this->meta_data->setType($this->getType());
		$this->meta_data->update();
	}


} // END class.ilObjMediaObject
?>
