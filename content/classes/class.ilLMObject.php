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
	* Meta data update listener
	*
	* Important note: Do never call create() or update()
	* method of ilObject here. It would result in an
	* endless loop: update object -> update meta -> update
	* object -> ...
	* Use static _writeTitle() ... methods instead.
	*
	* @param	string		$a_element
	*/
	function MDUpdateListener($a_element)
	{
		include_once 'Services/MetaData/classes/class.ilMD.php';

		switch($a_element)
		{
			case 'General':

				// Update Title and description
				$md = new ilMD($this->getLMId(), $this->getId(), $this->getType());
				$md_gen = $md->getGeneral();

				ilLMObject::_writeTitle($this->getId(),$md_gen->getTitle());

				foreach($md_gen->getDescriptionIds() as $id)
				{
					$md_des = $md_gen->getDescription($id);
//					ilLMObject::_writeDescription($this->getId(),$md_des->getDescription());
					break;
				}

				break;

			default:
		}
		return true;
	}


	/**
	* lookup named identifier (ILIAS_NID)
	*/
	function _lookupNID($a_lm_id, $a_lm_obj_id, $a_type)
	{
		include_once 'Services/MetaData/classes/class.ilMD.php';
//echo "-".$a_lm_id."-".$a_lm_obj_id."-".$a_type."-";
		$md = new ilMD($a_lm_id, $a_lm_obj_id, $a_type);
		$md_gen = $md->getGeneral();
		foreach($md_gen->getIdentifierIds() as $id)
		{
			$md_id = $md_gen->getIdentifier($id);
			if ($md_id->getCatalog() == "ILIAS_NID")
			{
				return $md_id->getEntry();
			}
		}
		
		return false;
	}


	/**
	* create meta data entry
	*/
	function createMetaData()
	{
		include_once 'Services/MetaData/classes/class.ilMDCreator.php';

		global $ilUser;

		$md_creator = new ilMDCreator($this->getLMId(), $this->getId(), $this->getType());
		$md_creator->setTitle($this->getTitle());
		$md_creator->setTitleLanguage($ilUser->getPref('language'));
		$md_creator->setDescription($this->getDescription());
		$md_creator->setDescriptionLanguage($ilUser->getPref('language'));
		$md_creator->setKeywordLanguage($ilUser->getPref('language'));
		$md_creator->setLanguage($ilUser->getPref('language'));
		$md_creator->create();

		return true;
	}

	/**
	* update meta data entry
	*/
	function updateMetaData()
	{
		include_once("Services/MetaData/classes/class.ilMD.php");
		include_once("Services/MetaData/classes/class.ilMDGeneral.php");
		include_once("Services/MetaData/classes/class.ilMDDescription.php");

		$md =& new ilMD($this->getLMId(), $this->getId(), $this->getType());
		$md_gen =& $md->getGeneral();
		$md_gen->setTitle($this->getTitle());

		// sets first description (maybe not appropriate)
		$md_des_ids =& $md_gen->getDescriptionIds();
		if (count($md_des_ids) > 0)
		{
			$md_des =& $md_gen->getDescription($md_des_ids[0]);
//			$md_des->setDescription($this->getDescription());
			$md_des->update();
		}
		$md_gen->update();

	}


	/**
	* delete meta data entry
	*/
	function deleteMetaData()
	{
		// Delete meta data
		include_once('Services/MetaData/classes/class.ilMD.php');
		$md = new ilMD($this->getLMId(), $this->getId(), $this->getType());
		$md->deleteAll();
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
/*
		$ilBench->start("ContentPresentation", "ilLMObject_read_getMeta");
		$this->meta_data =& new ilMetaData($this->type, $this->id);
		$ilBench->stop("ContentPresentation", "ilLMObject_read_getMeta");
*/
		$this->setImportId($this->data_record["import_id"]);
		$this->setTitle($this->data_record["title"]);

		$ilBench->stop("ContentPresentation", "ilLMObject_read");
	}

	/**
	*
	*/
	function setTitle($a_title)
	{
//		$this->meta_data->setTitle($a_title);
		$this->title = $a_title;
	}

	function getTitle()
	{
//		return $this->title ? $this->title : $this->meta_data->getTitle();
		return $this->title;
	}


	function _lookupTitle($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT * FROM lm_data WHERE obj_id = '".$a_obj_id."'";
		$obj_set = $ilDB->query($query);
		$obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);

		return $obj_rec["title"];
	}
	
	function _lookupType($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT * FROM lm_data WHERE obj_id = '".$a_obj_id."'";
		$obj_set = $ilDB->query($query);
		$obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);

		return $obj_rec["type"];
	}


	function _writeTitle($a_obj_id, $a_title)
	{
		global $ilDB;

		$query = "UPDATE lm_data SET ".
			" title = ".$ilDB->quote($a_title).
			" WHERE obj_id = ".$ilDB->quote($a_obj_id);
		$ilDB->query($query);
	}

/*
	function _writeDescription($a_obj_id, $a_desc)
	{
		global $ilDB;

		$query = "UPDATE lm_data SET ".
			" description = ".$ilDB->quote($a_desc).
			" WHERE obj_id = ".$ilDB->quote($a_obj_id);
		$ilDB->query($query);
	}
*/

	function setDescription($a_description)
	{
//		$this->meta_data->setDescription($a_description);
		$this->description = $a_description;
	}

	function getDescription()
	{
//		return $this->description ? $this->description : $this->meta_data->getDescription();
		return $this->description;
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
		return $this->import_id;
	}

	function setImportId($a_id)
	{
		$this->import_id = $a_id;
	}

	/**
	* write import id to db (static)
	*
	* @param	int		$a_id				lm object id
	* @param	string	$a_import_id		import id
	* @access	public
	*/
	function _writeImportId($a_id, $a_import_id)
	{
		global $ilDB;

		$q = "UPDATE lm_data ".
			"SET ".
			"import_id = ".$ilDB->quote($a_import_id).",".
			"last_update = now() ".
			"WHERE obj_id = ".$ilDB->quote($a_id);

		$ilDB->query($q);
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
			$this->createMetaData();
		}

	}

	/**
	* update complete object
	*/
	function update()
	{
		global $ilDB;

		$this->updateMetaData();

		$query = "UPDATE lm_data SET ".
			" lm_id = ".$ilDB->quote($this->getLMId()).
			" ,title = ".$ilDB->quote($this->getTitle()).
			" WHERE obj_id = ".$ilDB->quote($this->getId());

		$ilDB->query($query);
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
		global $ilDB,$ilLog,$ilErr,$ilTree;
		
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
		
		// update structure entries: if at least one page of a chapter is public set chapter to public too
		$lm_tree = new ilTree($a_cont_obj_id);
		$lm_tree->setTableNames('lm_tree','lm_data');
		$lm_tree->setTreeTablePK("lm_id");
		$lm_tree->readRootId();
		
		// get all st entries of cont_obj
		$q = "SELECT obj_id FROM lm_data " . 
			 "WHERE lm_id = ".$ilDB->quote($a_cont_obj_id)." " .
			 "AND type = 'st'";
		$r = $ilDB->query($q);
		
		// add chapters with a public page to a_pages
		while ($row = $r->fetchRow())
		{
			$childs = $lm_tree->getChilds($row[0]);
			
			foreach ($childs as $page)
			{
				if ($page["type"] == "pg" and in_array($page["obj_id"],$a_pages))
				{
					array_push($a_pages, $row[0]);
					break;
				}
			}
		}
		
		// update public access status of all pages of cont_obj
		$q = "UPDATE lm_data SET " .
			 "public_access = CASE " .
			 "WHEN obj_id IN (".implode(',',$a_pages).") " .
			 "THEN 'y' ".
			 "ELSE 'n' ".
			 "END " .
			 "WHERE lm_id = ".$ilDB->quote($a_cont_obj_id)." " .
			 "AND type IN ('pg','st')";
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
		$query = "DELETE FROM lm_data WHERE obj_id= '".$this->getId()."'";
		$this->ilias->db->query($query);

		$this->deleteMetaData();
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
		global $ilDB;
		
		$q = "SELECT * FROM lm_data WHERE import_id = '".$a_import_id."'".
			" ORDER BY create_date DESC LIMIT 1";
		$obj_set = $ilDB->query($q);
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
	* checks wether a lm content object with specified id exists or not
	*
	* @param	int		$id		id
	*
	* @return	boolean		true, if lm content object exists
	*/
	function _exists($a_id)
	{
		global $ilDB;
		
		include_once("content/classes/Pages/class.ilInternalLink.php");
		if (is_int(strpos($a_id, "_")))
		{
			$a_id = ilInternalLink::_extractObjIdOfTarget($a_id);
		}
		
		$q = "SELECT * FROM lm_data WHERE obj_id = '".$a_id."'";
		$obj_set = $ilDB->query($q);
		if ($obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			return true;
		}
		else
		{
			return false;
		}

	}

	/**
	* static
	*/
	function getObjectList($lm_id, $type = "")
	{
		global $ilDB;
		
		$type_str = ($type != "")
			? "AND type = '$type' "
			: "";
		$query = "SELECT * FROM lm_data ".
			"WHERE lm_id= '".$lm_id."'".
			$type_str." ".
			"ORDER BY title";
		$obj_set = $ilDB->query($query);
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

		$query = "SELECT * FROM lm_data ".
			"WHERE lm_id= '".$a_cobj->getId()."'";
		$obj_set = $this->ilias->db->query($query);

		require_once("content/classes/class.ilLMObjectFactory.php");
		while($obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$lm_obj =& ilLMObjectFactory::getInstance($a_cobj, $obj_rec["obj_id"],false);

			if (is_object($lm_obj))
			{
				$lm_obj->delete(true);
			}
		}

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
