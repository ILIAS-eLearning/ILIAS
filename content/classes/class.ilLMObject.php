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

require_once("classes/class.ilMetaData.php");

/**
* Class ilLMObject
*
* Base class for ilStructureObjects and ilPageObjects (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilLMObject
{
	var $ilias;
	var $lm_id;
	var $type;
	var $id;
	var $meta_data;
	var $data_record;		// assoc array of lm_data record
	var $content_object;
	var $title;
	var $description;

	/**
	* @param	object		$a_content_obj		content object (digi book or learning module)
	*/
	function ilLMObject(&$a_content_obj, $a_id = 0)
	{
		global $ilias;

		$this->ilias =& $ilias;
		$this->id = $a_id;
		$this->setContentObject($a_content_obj);
		if($a_id != 0)
		{
			$this->read();
		}
	}

	/**
	* this method should only be called by class ilLMObjectFactory
	*/
	function setDataRecord($a_record)
	{
		$this->data_record = $a_record;
	}

	function read()
	{
		if(!isset($this->data_record))
		{
			$query = "SELECT * FROM lm_data WHERE obj_id = '".$this->id."'";
			$obj_set = $this->ilias->db->query($query);
			$this->data_record = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);
		}

		$this->type = $this->data_record["type"];
		$this->meta_data =& new ilMetaData($this->type, $this->id);
		$this->setImportId($this->data_record["import_id"]);
		$this->setTitle($this->data_record["title"]);
	}

	/**
	*
	*/
	function setTitle($a_title)
	{
		$this->meta_data->setTitle($a_title);
		$this->title = $a_title;
	}

	function getTitle()
	{
		return $this->title ? $this->title : $this->meta_data->getTitle();
	}

	function setDescription($a_description)
	{
		$this->meta_data->setDescription($a_description);
		$this->description = $a_description;
	}

	function getDescription()
	{
		return $this->description ? $this->description : $this->meta_data->getDescription();
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

	function setContentObject(&$a_content_obj)
	{
		$this->content_object =& $a_content_obj;
	}

	function &getContentObject()
	{
		return $this->content_object;
	}

	function setId($a_id)
	{
		$this->id = $a_id;
	}

	function getId()
	{
		return $this->id;
	}

	function getImportId()
	{
		return $this->meta_data->getImportIdentifierEntryID();
	}

	function setImportId($a_id)
	{
		$this->meta_data->setImportIdentifierEntryID($a_id);
	}


	function create($a_upload = false)
	{
		// insert object data
		$query = "INSERT INTO lm_data (title, type, lm_id, import_id) ".
			"VALUES ('".$this->getTitle()."','".$this->getType()."', ".$this->getLMId().",'".$this->getImportId()."')";
		$this->ilias->db->query($query);
		$this->setId(getLastInsertId());

		if (!$a_upload)
		{
			// create meta data
			$this->meta_data->setId($this->getId());
			$this->meta_data->setType($this->getType());
			$this->meta_data->setTitle($this->getTitle());
			$this->meta_data->setDescription($this->getDescription());
			$this->meta_data->setObject($this);
			$this->meta_data->create();
		}
	}

	function assignMetaData(&$a_meta_data)
	{
		$this->meta_data =& $a_meta_data;
	}

	function &getMetaData()
	{
		return $this->meta_data;
	}


	/**
	* update meta data of object and lm_data table
	*/
	function updateMetaData()
	{
		// update object data
		$query = "UPDATE lm_data SET title = '".$this->getTitle()."'".
			", import_id = '".$this->getImportId()."'".
			" WHERE obj_id= '".$this->getId()."'";
		$this->ilias->db->query($query);

		// create meta data
		$this->meta_data->update();
	}

	/**
	* update complete object
	*/
	function update()
	{
		$this->updateMetaData();
	}


	function delete()
	{
		/* Delete meta data in nested set table for given object and type */
		$nested = new ilNestedSetXML();
		$nested->init($this->getId(), $this->getType());
		$nested->deleteAllDBData();

		$query = "DELETE FROM lm_data WHERE obj_id= '".$this->getId()."'";
		$this->ilias->db->query($query);
	}

	/**
	* static
	*/
	function getObjectList($lm_id, $type = "")
	{
		$type_str = ($type != "")
			? "AND type = '$type' "
			: "";
		$query = "SELECT * FROM lm_data ".
			"WHERE lm_id= '".$lm_id."'".
			$type_str." ".
			"ORDER BY title";
		$obj_set = $this->ilias->db->query($query);
		$obj_list = array();
		while($obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$obj_list[] = array("obj_id" => $obj_rec["obj_id"],
								"title" => $obj_rec["title"],
								"type" => $obj_rec["type"]);
		}
		return $obj_list;
	}

}
?>
