<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

require_once("Services/MetaData/classes/class.ilMDLanguageItem.php");

/**
* Class ilLMObject
*
* Base class for ilStructureObjects and ilPageObjects (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
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
	var $active = true;

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
		global $ilBench, $ilDB;

		$ilBench->start("ContentPresentation", "ilLMObject_read");

		if(!isset($this->data_record))
		{
			$ilBench->start("ContentPresentation", "ilLMObject_read_getData");
			$query = "SELECT * FROM lm_data WHERE obj_id = ".$ilDB->quote($this->id);
			$obj_set = $this->ilias->db->query($query);
			$this->data_record = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);
			$ilBench->stop("ContentPresentation", "ilLMObject_read_getData");
		}

		$this->type = $this->data_record["type"];
		$this->setImportId($this->data_record["import_id"]);
		$this->setTitle($this->data_record["title"]);
		$this->setActive(ilUtil::yn2tf($this->data_record["active"]));

		$ilBench->stop("ContentPresentation", "ilLMObject_read");
	}

	/**
	* set title of lm object
	*
	* @param	string		$a_title	title of chapter or page
	*/
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	* get title of lm object
	*
	* @return	string		title of chapter or page
	*/
	function getTitle()
	{
		return $this->title;
	}


	function _lookupTitle($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT * FROM lm_data WHERE obj_id = ".$ilDB->quote($a_obj_id);
		$obj_set = $ilDB->query($query);
		$obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);

		return $obj_rec["title"];
	}
	
	function _lookupType($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT * FROM lm_data WHERE obj_id = ".$ilDB->quote($a_obj_id);
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


	function setDescription($a_description)
	{
		$this->description = $a_description;
	}

	function getDescription()
	{
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
	* set activation
	*
	* @param	boolean		$a_active	true/false for active or not
	*/
	function setActive($a_active)
	{
		$this->active = $a_active;
	}

	/**
	* get activation
	*
	* @return	boolean		true/false for active or not
	*/
	function getActive()
	{
		return $this->active;
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
		global $ilDB;

		// insert object data
		$query = "INSERT INTO lm_data (title, type, lm_id, import_id, create_date, active) ".
			"VALUES (".$ilDB->quote($this->getTitle()).",".$ilDB->quote($this->getType()).", ".
			$ilDB->quote($this->getLMId()).",".$ilDB->quote($this->getImportId()).
			", now(),".$ilDB->quote(ilUtil::tf2yn($this->getActive())).")";
		$this->ilias->db->query($query);
		$this->setId($this->ilias->db->getLastInsertId());

		// create history entry
		include_once("classes/class.ilHistory.php");
		ilHistory::_createEntry($this->getId(), "create", "",
			$this->content_object->getType().":".$this->getType());

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
			" ,active = ".$ilDB->quote(ilUtil::tf2yn($this->getActive())).
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
			 "WHEN obj_id IN (".implode(',',ilUtil::quoteArray($a_pages)).") " .
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
		global $ilDB;
		
		$query = "DELETE FROM lm_data WHERE obj_id= ".$ilDB->quote($this->getId());
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
		
		$q = "SELECT * FROM lm_data WHERE import_id = ".$ilDB->quote($a_import_id)." ".
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
		
		include_once("./Services/COPage/classes/class.ilInternalLink.php");
		if (is_int(strpos($a_id, "_")))
		{
			$a_id = ilInternalLink::_extractObjIdOfTarget($a_id);
		}
		
		$q = "SELECT * FROM lm_data WHERE obj_id = ".$ilDB->quote($a_id);
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
			? "AND type = ".$ilDB->quote($type)." "
			: "";
		$query = "SELECT * FROM lm_data ".
			"WHERE lm_id= ".$ilDB->quote($lm_id)." ".
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
		global $ilDB;
		
		include_once './classes/class.ilNestedSetXML.php';

		$query = "SELECT * FROM lm_data ".
			"WHERE lm_id= ".$ilDB->quote($a_cobj->getId())." ";
		$obj_set = $this->ilias->db->query($query);

		require_once("./Modules/LearningModule/classes/class.ilLMObjectFactory.php");
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

		$query = "SELECT * FROM lm_data WHERE obj_id = ".$ilDB->quote($a_id)."";
		$obj_set = $ilDB->query($query);
		$obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);

		return $obj_rec["lm_id"];
	}

	/**
	* lookup activation status
	*/
	function _lookupActive($a_id)
	{
		global $ilDB;

		$query = "SELECT * FROM lm_data WHERE obj_id = ".$ilDB->quote($a_id);
		$obj_set = $ilDB->query($query);
		$obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);

		return ilUtil::yn2tf($obj_rec["active"]);
	}

	/**
	* write activation status
	*/
	function _writeActive($a_id, $a_active)
	{
		global $ilDB;

		$query = "UPDATE lm_data ".
			" SET active = ".$ilDB->quote(ilUtil::tf2yn($a_active)).
			" WHERE obj_id = ".$ilDB->quote($a_id);
		$ilDB->query($query);
	}

	/**
	* put this object into content object tree
	*/
	static function putInTree($a_obj, $a_parent_id = "", $a_target_node_id = "")
	{
		$tree = new ilTree($a_obj->getContentObject()->getId());
		$tree->setTableNames('lm_tree', 'lm_data');
		$tree->setTreeTablePK("lm_id");

		// determine parent
		$parent_id = ($a_parent_id != "")
			? $a_parent_id
			: $tree->getRootId();

		// determine target
		if ($a_target_node_id != "")
		{
			$target = $a_target_node_id;
		}
		else
		{
			// determine last child that serves as predecessor
			if ($a_obj->getType() == "st")
			{
				$s_types = array("st", "pg");
				$childs =& $tree->getChildsByTypeFilter($parent_id, $s_types);
			}
			else
			{
				$s_types = "pg";
				$childs =& $tree->getChildsByType($parent_id, $s_types);
			}

			if (count($childs) == 0)
			{
				$target = IL_FIRST_NODE;
			}
			else
			{
				$target = $childs[count($childs) - 1]["obj_id"];
			}
		}

		if ($tree->isInTree($parent_id) && !$tree->isInTree($a_obj->getId()))
		{
			$tree->insertNode($a_obj->getId(), $parent_id, $target);
		}
	}

	/**
	* Get learningmodule tree
	*
	* @param	int		learning module object id
	*
	* @return	object		tree object
	*/
	static function getTree($a_cont_obj_id)
	{
		$tree = new ilTree($a_cont_obj_id);
		$tree->setTableNames('lm_tree', 'lm_data');
		$tree->setTreeTablePK("lm_id");
		$tree->readRootId();
		
		return $tree;
	}
	
	/**
	* Copy a set of chapters/pages into the clipboard
	*/
	function clipboardCut($a_cont_obj_id, $a_ids)
	{
		$tree = ilLMObject::getTree($a_cont_obj_id);
		
		if (!is_array($a_ids))
		{
			return false;
		}
		else
		{
			// get all "top" ids, i.e. remove ids, that have a selected parent
			foreach($a_ids as $id)
			{
				$path = $tree->getPathId($id);
				$take = true;
				foreach($path as $path_id)
				{
					if ($path_id != $id && in_array($path_id, $a_ids))
					{
						$take = false;
					}
				}
				if ($take)
				{
					$cut_ids[] = $id;
				}
			}
		}
		
		ilLMObject::clipboardCopy($a_cont_obj_id, $cut_ids);
		
		// remove the objects from the tree
		// note: we are getting chapters which are *not* in the tree
		// we do not delete any pages/chapters here
		foreach ($cut_ids as $id)
		{
			$curnode = $tree->getNodeData($id);
			if ($tree->isInTree($id))
			{
				$tree->deleteTree($curnode);
			}
		}

	}

	/**
	* Copy a set of chapters/pages into the clipboard
	*/
	static function clipboardCopy($a_cont_obj_id, $a_ids)
	{
		global $ilUser;
		
		$tree = ilLMObject::getTree($a_cont_obj_id);
		
		$ilUser->clipboardDeleteObjectsOfType("pg");
		$ilUser->clipboardDeleteObjectsOfType("st");
		
		// put them into the clipboard
		$time = date("Y-m-d H:i:s", time());
		foreach ($a_ids as $id)
		{
			$curnode = "";
			if ($tree->isInTree($id))
			{
				$curnode = $tree->getNodeData($id);
				$subnodes = $tree->getSubTree($curnode);
				foreach($subnodes as $subnode)
				{
					if ($subnode["child"] != $id)
					{
						$ilUser->addObjectToClipboard($subnode["child"],
							$subnode["type"], $subnode["title"],
							$subnode["parent"], $time, $subnode["lft"]);
					}
				}
			}
			$order = ($curnode["lft"] > 0)
				? $curnode["lft"]
				: (int) ($order + 1);
			$ilUser->addObjectToClipboard($id,
				ilLMObject::_lookupType($id), ilLMObject::_lookupTitle($id), 0, $time,
				$order);
		}
	}
	
	/**
	* Paste item (tree) from clipboard to current lm
	*/
	static function pasteTree($a_target_lm, $a_item_id, $a_parent_id, $a_target, $a_insert_time,
		$a_as_copy = false)
	{
		global $ilUser, $ilias;
		
		$item_lm_id = ilLMObject::_lookupContObjID($a_item_id);
		$item_type = ilLMObject::_lookupType($a_item_id);
		$lm_obj = $ilias->obj_factory->getInstanceByObjId($item_lm_id);
		if ($item_type == "st")
		{
			$item = new ilStructureObject($lm_obj, $a_item_id);
		}
		else
		{
			$item = new ilLMPageObject($lm_obj, $a_item_id);
		}

		if ($item_lm_id != $a_target_lm->getId() && !$a_as_copy)
		{
			// @todo: check whether st is NOT in tree
			
			// "move" metadata to new lm
			include_once("Services/MetaData/classes/class.ilMD.php");
			$md = new ilMD($item_lm_id, $item->getId(), $item->getType());
			$new_md = $md->cloneMD($a_target_lm->getId(), $item->getId(), $item->getType());
			
			// update lm object
			$item->setLMId($a_target_lm->getId());
			$item->setContentObject($a_target_lm);
			$item->update();
			
			// delete old meta data set
			$md->deleteAll();
			
			if ($item_type == "pg")
			{
				$page = $item->getPageObject();
				$page->buildDom();
				$page->setParentId($a_target_lm->getId());
				$page->update();
			}
		}

		if ($a_as_copy)
		{
			$target_item = $item->copy($a_target_lm);
		}
		else
		{
			$target_item = $item;
		}
		
//echo "-".$target_item->getId()."-".$a_parent_id."-".$a_target."-";
		ilLMObject::putInTree($target_item, $a_parent_id, $a_target);
		
		$childs = $ilUser->getClipboardChilds($item->getId(), $a_insert_time);
//echo "<br>-".$item->getId()."-".$a_insert_time."-";
//echo "-";

		foreach($childs as $child)
		{
			ilLMObject::pasteTree($a_target_lm, $child["id"], $target_item->getId(),
				IL_LAST_NODE, $a_insert_time,
				$a_as_copy);
		}
		
		return $target_item->getId();
		// @todo: write history (see pastePage)
	}

	/**
	* Save titles for lm objects
	*
	* @param	array		titles (key is ID, value is title)
	*/
	static function saveTitles($a_lm, $a_titles)
	{
		if (is_array($a_titles))
		{
			include_once("./Services/MetaData/classes/class.ilMD.php");
			foreach($a_titles as $id => $title)
			{
				$lmobj = ilLMObjectFactory::getInstance($a_lm, $id, false);
				if (is_object($lmobj))
				{
					// Update Title and description
					$md = new ilMD($a_lm->getId(), $id, $lmobj->getType());
					$md_gen = $md->getGeneral();
					$md_gen->setTitle($title);
					$md_gen->update();
					$md->update();
					ilLMObject::_writeTitle($id, $title);
				}
			}
		}
	}
}
?>
