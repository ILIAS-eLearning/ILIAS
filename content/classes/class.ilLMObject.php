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
		$this->setLMId($a_content_obj->getId());
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
		global $ilBench;

		$ilBench->start("ContentPresentation", "ilLMObject_read");

		if(!isset($this->data_record))
		{
			$ilBench->start("ContentPresentation", "ilLMObject_read_getData");
			$query = "SELECT * FROM lm_data WHERE obj_id = '".$this->id."'";
			$obj_set = $this->ilias->db->query($query);
			$this->data_record = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);
			$ilBench->stop("ContentPresentation", "ilLMObject_read_getData");
		}

		$this->type = $this->data_record["type"];
		$ilBench->start("ContentPresentation", "ilLMObject_read_getMeta");
		$this->meta_data =& new ilMetaData($this->type, $this->id);
		$ilBench->stop("ContentPresentation", "ilLMObject_read_getMeta");
		$this->setImportId($this->data_record["import_id"]);
		$this->setTitle($this->data_record["title"]);

		$ilBench->stop("ContentPresentation", "ilLMObject_read");
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


	function _lookupTitle($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT * FROM lm_data WHERE obj_id = '".$a_obj_id."'";
		$obj_set = $ilDB->query($query);
		$obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);

		return $obj_rec["title"];
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
		$query = "INSERT INTO lm_data (title, type, lm_id, import_id, create_date) ".
			"VALUES ('".ilUtil::prepareDBString($this->getTitle())."','".$this->getType()."', ".$this->getLMId().",'".$this->getImportId().
			"', now())";
		$this->ilias->db->query($query);
		$this->setId($this->ilias->db->getLastInsertId());
		
		// create history entry
		include_once("classes/class.ilHistory.php");
		ilHistory::_createEntry($this->getId(), "create", "",
			$this->content_object->getType().":pg");

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
		global $ilDB;

//$f = fopen("/opt/iliasdata/bb.txt", "a"); fwrite($f, "LMObject::updateMetaData(), start\n"); fclose($f);

		//$this->meta_data->update();
		if ($this->meta_data->section != "General")
		{
			$meta = $this->meta_data->getElement("Title", "General");
			$this->title = $meta[0]["Value"];
			$meta = $this->meta_data->getElement("Description", "General");
			$this->description = $meta[0]["Value"];
		}
		else
		{
			$this->setTitle($this->meta_data->getTitle());
			$this->setDescription($this->meta_data->getDescription());
		}
		$query = "UPDATE lm_data SET ".
			" title = ".$ilDB->quote($this->getTitle()).
			", last_update = now() WHERE obj_id = ".$ilDB->quote($this->getId());

		$this->ilias->db->query($query);
		$this->meta_data->update();
//$f = fopen("/opt/iliasdata/bb.txt", "a"); fwrite($f, "LMObject::updateMetaData(), end\n"); fclose($f);
	}

	/**
	* update complete object
	*/
	function update()
	{
		global $ilDB;

		$this->updateMetaData();

	}
	
	/**
	* update public access flags in lm_data for all pages of a content object
	* @static
	* @access	public
	* @param	array	page ids
	* @param	integer	content object id
	* @return	of the jedi
	*/
	function _writePublicAccessStatus($a_pages,$a_cont_obj_id)
	{
		global $ilDB,$ilLog,$ilErr;
		
		if (!is_array($a_pages))
		{$a_pages = array(0);
			/*$message = sprintf('ilLMObject::_writePublicAccessStatus(): Invalid parameter! $a_pages must be an array');
			$ilLog->write($message,$ilLog->WARNING);
			$ilErr->raiseError($message,$ilErr->MESSAGE);
			return false;*/
		}
		
		if (empty($a_cont_obj_id))
		{
			$message = sprintf('ilLMObject::_writePublicAccessStatus(): Invalid parameter! $a_cont_obj_id is empty');
			$ilLog->write($message,$ilLog->WARNING);
			$ilErr->raiseError($message,$ilErr->MESSAGE);
			return false;
		}
		
		// update public access status of all pages of cont_obj
		$q = "UPDATE lm_data SET " .
			 "public_access = CASE " .
			 "WHEN obj_id IN (".implode(',',$a_pages).") " .
			 "THEN 'y' ".
			 "ELSE 'n' ".
			 "END " .
			 "WHERE lm_id = ".$ilDB->quote($a_cont_obj_id)." " .
			 "AND type = 'pg'";
		$ilDB->query($q);

		return true;
	}
	
	function _isPagePublic($a_node_id,$a_check_public_mode = false)
	{
		global $ilDB,$ilLog;

		if (empty($a_node_id))
		{
			$message = sprintf('ilLMObject::_isPagePublic(): Invalid parameter! $a_node_id is empty');
			$ilLog->write($message,$ilLog->WARNING);
			return false;
		}
		
		if ($a_check_public_mode === true)
		{
			$lm_id = ilLMObject::_lookupContObjId($a_node_id);

			$q = "SELECT public_access_mode FROM content_object WHERE id=".$ilDB->quote($lm_id);
			$r = $ilDB->query($q);
			$row = $r->fetchRow();
			
			if ($row[0] == "complete")
			{
				return true;
			}
		}

		$q = "SELECT public_access FROM lm_data WHERE obj_id=".$ilDB->quote($a_node_id);
		$r = $ilDB->query($q);
		$row = $r->fetchRow();
		
		return ilUtil::yn2tf($row[0]);
	}

	/**
	* delete lm object data
	*/
	function delete($a_delete_meta_data = true)
	{
		if ($a_delete_meta_data)
		{
			/* Delete meta data in nested set table for given object and type */
			$nested = new ilNestedSetXML();
			$nested->init($this->getId(), $this->getType());
			$nested->deleteAllDBData();
		}

		$query = "DELETE FROM lm_data WHERE obj_id= '".$this->getId()."'";
		$this->ilias->db->query($query);
	}

	/**
	* get current object id for import id (static)
	*
	* import ids can exist multiple times (if the same learning module
	* has been imported multiple times). we get the object id of
	* the last imported object, that is not in trash
	*
	* @param	int		$a_import_id		import id
	*
	* @return	int		id
	*/
	function _getIdForImportId($a_import_id)
	{
		$q = "SELECT * FROM lm_data WHERE import_id = '".$a_import_id."'".
			" ORDER BY create_date DESC LIMIT 1";
		$obj_set = $this->ilias->db->query($q);
		while ($obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$lm_id = ilLMObject::_lookupContObjID($obj_rec["obj_id"]);

			// link only in learning module, that is not trashed
			if (ilObject::_hasUntrashedReference($lm_id))
			{
				return $obj_rec["obj_id"];
			}
		}

		return 0;
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


	/**
	* delete all objects of content object (digi book / learning module)
	*/
	function _deleteAllObjectData(&$a_cobj)
	{
		include_once './classes/class.ilNestedSetXML.php';

		$page_ids = ilNestedSetXML::_getAllChildIds($a_cobj->getId());

		$query = "SELECT * FROM lm_data ".
			"WHERE lm_id= '".$a_cobj->getId()."'";
		$obj_set = $this->ilias->db->query($query);

		require_once("content/classes/class.ilLMObjectFactory.php");
		while($obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$lm_obj =& ilLMObjectFactory::getInstance($a_cobj, $obj_rec["obj_id"]);
			if (is_object($lm_obj))
			{
				$lm_obj->delete(false);
			}
		}
		ilNestedSetXML::_deleteAllChildMetaData($page_ids);

		return true;
	}

	/**
	* get learning module / digibook id for lm object
	*/
	function _lookupContObjID($a_id)
	{
		global $ilDB;

		$query = "SELECT * FROM lm_data WHERE obj_id = '".$a_id."'";
		$obj_set = $ilDB->query($query);
		$obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);

		return $obj_rec["lm_id"];
	}

}
?>
