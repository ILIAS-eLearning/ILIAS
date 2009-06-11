<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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

require_once("./Services/MetaData/classes/class.ilMDLanguageItem.php");

/**
* Class 
*
* Base class for Scorm 2004 Nodes (Chapters, Pages, SCOs)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScorm2004
*/
class ilSCORM2004Node
{
	var $slm_id;
	var $type;
	var $id;
	var $slm_object;

	/**
	* @param	object		$a_slm_object		ilObjScorm2004LearningModule object
	*/
	function ilSCORM2004Node($a_slm_object, $a_id = 0)
	{
		$this->id = $a_id;
		$this->setSLMObject($a_slm_object);
		$this->setSLMId($a_slm_object->getId());
		
		$this->tree = new ilTree($a_slm_object->getId());
		$this->tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
		$this->tree->setTreeTablePK("slm_id");

		if($a_id != 0)
		{
			$this->read();
		}
	}

	/**
	* Set title
	*
	* @param	string		$a_title	title
	*/
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	* Get title
	*
	* @return	string		title
	*/
	function getTitle()
	{
		return $this->title;
	}

	/**
	* Set description
	*
	* @param	string		Description
	*/
	function setDescription($a_description)
	{
		$this->description = $a_description;
	}

	/**
	* Get description
	*
	* @return	string		Description
	*/
	function getDescription()
	{
		return $this->description;
	}

	/**
	* Set type
	*
	* @param	string		Type
	*/
	function setType($a_type)
	{
		$this->type = $a_type;
	}

	/**
	* Get type
	*
	* @return	string		Type
	*/
	function getType()
	{
		return $this->type;
	}

	/**
	* Set ID of parent Scorm Learning Module Object
	*
	* @param	int		Scorm LM ID
	*/
	function setSLMId($a_slm_id)
	{
		$this->slm_id = $a_slm_id;

	}

	/**
	* Get ID of parent Scorm Learning Module Object
	*
	* @param	int		Scorm LM ID
	*/
	function getSLMId()
	{
		return $this->slm_id;
	}

	/**
	* Set Scorm Learning Module Object
	*
	* @param	int		Scorm LM Object
	*/
	function setSLMObject($a_slm_obj)
	{
		$this->slm_object = $a_slm_obj;
	}

	/**
	* Get Scorm Learning Module Object
	*
	* @return	int		Scorm LM Object
	*/
	function getContentObject()
	{
		return $this->slm_object;
	}

	/**
	* Set Node ID
	*
	* @param	int		Node ID
	*/
	function setId($a_id)
	{
		$this->id = $a_id;
	}

	/**
	* Get Node ID
	*
	* @param	int		Node ID
	*/
	function getId()
	{
		return $this->id;
	}

	/**
	* Set Import ID
	*
	* @param	int		Import ID
	*/
	function getImportId()
	{
		return $this->import_id;
	}

	/**
	* Get Import ID
	*
	* @param	int		Import ID
	*/
	function setImportId($a_id)
	{
		$this->import_id = $a_id;
	}

	/**
	* Read Data of Node
	*/
	function read()
	{
		global $ilBench, $ilDB;

		if(!isset($this->data_record))
		{
			$query = "SELECT * FROM sahs_sc13_tree_node WHERE obj_id = ".$ilDB->quote($this->id);
			$obj_set = $ilDB->query($query);
			$this->data_record = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);
		}

		$this->type = $this->data_record["type"];
		$this->setImportId($this->data_record["import_id"]);
		$this->setTitle($this->data_record["title"]);
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
				$md = new ilMD($this->getSLMId(), $this->getId(), $this->getType());
				$md_gen = $md->getGeneral();

				ilSCORM2004Node::_writeTitle($this->getId(), $md_gen->getTitle());

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
	* create meta data entry
	*/
	function createMetaData()
	{
		global $ilUser;

		include_once 'Services/MetaData/classes/class.ilMDCreator.php';
		$md_creator = new ilMDCreator($this->getSLMId(), $this->getId(), $this->getType());
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

		$md = new ilMD($this->getSLMId(), $this->getId(), $this->getType());
		$md_gen = $md->getGeneral();
		$md_gen->setTitle($this->getTitle());

		// sets first description
		$md_des_ids = $md_gen->getDescriptionIds();
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
		$md = new ilMD($this->getSLMId(), $this->getId(), $this->getType());
		$md->deleteAll();
	}

	/**
	* this method should only be called by class ilSCORM2004NodeFactory
	*/
	function setDataRecord($a_record)
	{
		$this->data_record = $a_record;
	}

	/**
	* Lookup Title
	*
	* @param	int			Node ID
	* @return	string		Title
	*/
	static function _lookupTitle($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT * FROM sahs_sc13_tree_node WHERE obj_id = ".$ilDB->quote($a_obj_id);
		$obj_set = $ilDB->query($query);
		$obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);

		return $obj_rec["title"];
	}
	
	/**
	* Lookup Type
	*
	* @param	int			Node ID
	* @return	string		Type
	*/
	static function _lookupType($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT * FROM sahs_sc13_tree_node WHERE obj_id = ".$ilDB->quote($a_obj_id);
		$obj_set = $ilDB->query($query);
		$obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);

		return $obj_rec["type"];
	}

	/**
	* Write Title
	*
	* @param	int			Node ID
	* @param	string		Title
	*/
	static function _writeTitle($a_obj_id, $a_title)
	{
		global $ilDB;

		$query = "UPDATE sahs_sc13_tree_node SET ".
			" title = ".$ilDB->quote($a_title).
			" WHERE obj_id = ".$ilDB->quote($a_obj_id);
		$ilDB->query($query);
	}

	/**
	* Write import ID.
	*
	* @param	int		$a_id				Node ID
	* @param	string	$a_import_id		Import ID
	*/
	static function _writeImportId($a_id, $a_import_id)
	{
		global $ilDB;

		$q = "UPDATE sahs_sc13_tree_node ".
			"SET ".
			"import_id = ".$ilDB->quote($a_import_id).",".
			"last_update = now() ".
			"WHERE obj_id = ".$ilDB->quote($a_id);

		$ilDB->query($q);
	}

	/**
	* Create Node
	*
	* @param	boolean		Upload Mode
	*/
	function create($a_upload = false)
	{
		global $ilDB;

		// insert object data
		$query = "INSERT INTO sahs_sc13_tree_node (title, type, slm_id, import_id, create_date) ".
			"VALUES (".$ilDB->quote($this->getTitle()).",".$ilDB->quote($this->getType()).", ".
			$ilDB->quote($this->getSLMId()).",".$ilDB->quote($this->getImportId()).
			", now())";
		$ilDB->query($query);
		$this->setId($ilDB->getLastInsertId());

		if (!$a_upload)
		{
			$this->createMetaData();
		}
	}

	/**
	* Update Node
	*/
	function update()
	{
		global $ilDB;

		$this->updateMetaData();

		$query = "UPDATE sahs_sc13_tree_node SET ".
			" slm_id = ".$ilDB->quote($this->getSLMId()).
			" ,title = ".$ilDB->quote($this->getTitle()).
			" WHERE obj_id = ".$ilDB->quote($this->getId());

		$ilDB->query($query);
	}

	/**
	* Delete Node
	*/
	function delete()
	{
		global $ilDB;
		
		$query = "DELETE FROM sahs_sc13_tree_node WHERE obj_id= ".$ilDB->quote($this->getId());
		$ilDB->query($query);

		$this->deleteMetaData();
	}

	/**
	* Get Node ID for import ID (static)
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
		
		$q = "SELECT * FROM sahs_sc13_tree_node WHERE import_id = ".$ilDB->quote($a_import_id)." ".
			" ORDER BY create_date DESC LIMIT 1";
		$obj_set = $ilDB->query($q);
		while ($obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$slm_id = ilSCORM2004Node::_lookupSLMID($obj_rec["obj_id"]);

			// link only in learning module, that is not trashed
			if (ilObject::_hasUntrashedReference($slm_id))
			{
				return $obj_rec["obj_id"];
			}
		}

		return 0;
	}
	
	/**
	* Checks wether a node exists
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
		
		$q = "SELECT * FROM sahs_sc13_tree_node WHERE obj_id = ".$ilDB->quote($a_id);
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
	* Delete all nodes of Scorm Learning Module
	*
	* @param	object		Scorm 2004 Learning Module Object
	*/
	function _deleteAllSLMNodes($a_slm_object)
	{
		global $ilDB;
		
		$query = "SELECT * FROM sahs_sc13_tree_node ".
			"WHERE slm_id= ".$ilDB->quote($a_slm_object->getId())." ";
		$obj_set = $ilDB->query($query);

		require_once("./Modules/LearningModule/classes/class.ilScorm2004NodeFactory.php");
		while($obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$node_obj = ilSCORM2004NodeFactory::getInstance($a_slm_object, $obj_rec["obj_id"],false);

			if (is_object($node_obj))
			{
				$node_obj->delete();
			}
		}

		return true;
	}

	/**
	* Lookup Scorm Learning Module ID for node id
	*/
	function _lookupSLMID($a_id)
	{
		global $ilDB;

		$query = "SELECT * FROM sahs_sc13_tree_node WHERE obj_id = ".$ilDB->quote($a_id)."";
		$obj_set = $ilDB->query($query);
		$obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);

		return $obj_rec["slm_id"];
	}

	/**
	* put this object into content object tree
	*/
	static function putInTree($a_obj, $a_parent_id = "", $a_target_node_id = "")
	{
		$tree =& new ilTree($a_obj->getSLMId());
		$tree->setTreeTablePK("slm_id");
		$tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');

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
			$childs =& $tree->getChilds($parent_id);

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
	* Get scorm module editing tree
	*
	* @param	int		scorm module object id
	*
	* @return	object		tree object
	*/
	static function getTree($a_slm_obj_id)
	{
		$tree = new ilTree($a_slm_obj_id);
		$tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
		$tree->setTreeTablePK("slm_id");
		$tree->readRootId();
		
		return $tree;
	}

	/**
	* Copy a set of chapters/pages/scos into the clipboard
	*/
	static function clipboardCopy($a_slm_obj_id, $a_ids)
	{
		global $ilUser;
		
		$tree = ilSCORM2004Node::getTree($a_slm_obj_id);
		
		$ilUser->clipboardDeleteObjectsOfType("page");
		$ilUser->clipboardDeleteObjectsOfType("chap");
		$ilUser->clipboardDeleteObjectsOfType("sco");
		
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
				ilSCORM2004Node::_lookupType($id), ilSCORM2004Node::_lookupTitle($id), 0, $time,
				$order);
		}
	}

	/**
	* Cut and copy a set of chapters/pages into the clipboard
	*/
	function clipboardCut($a_slm_obj_id, $a_ids)
	{
		$tree = ilSCORM2004Node::getTree($a_slm_obj_id);
		
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
		
		ilSCORM2004Node::clipboardCopy($a_slm_obj_id, $cut_ids);
		
		// remove the objects from the tree
		// note: we are getting chapters, scos and pages which are *not* in the tree
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
	* Check for unique types (all pages or all chapters or all scos)
	*/
	static function uniqueTypesCheck($a_items)
	{
		$types = array();
		if (is_array($a_items))
		{
			foreach($a_items as $item)
			{
				$type = ilSCORM2004Node::_lookupType($item);
				$types[$type] = $type;
			}
		}

		if (count($types) > 1)
		{
			return false;
		}
		return true;
	}

	/**
	* Insert pages from clipboard
	*/
	static function insertPageClip($a_slm_obj)
	{
		global $ilCtrl, $ilUser;
		
		// @todo: move this to a service since it can be used here, too
		include_once("./Modules/LearningModule/classes/class.ilEditClipboard.php");

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004OrganizationHFormGUI.php");
		$node_id = ilSCORM2004OrganizationHFormGUI::getPostNodeId();
		$first_child = ilSCORM2004OrganizationHFormGUI::getPostFirstChild();
		
		$tree = ilSCORM2004Node::getTree($a_slm_obj->getId());
		
		if (!$first_child)	// insert after node id
		{
			$parent_id = $tree->getParentId($node_id);
			$target = $node_id;
		}
		else													// insert as first child
		{
			$parent_id = $node_id;
			$target = IL_FIRST_NODE;
		}

		// cut and paste
		$pages = $ilUser->getClipboardObjects("page");
		$copied_nodes = array();
		foreach ($pages as $pg)
		{
			$cid = ilSCORM2004Node::pasteTree($a_slm_obj, $pg["id"], $parent_id, $target,
				$pg["insert_time"], $copied_nodes,
				(ilEditClipboard::getAction() == "copy"));
			$target = $cid;
		}
		//ilLMObject::updateInternalLinks($copied_nodes);

		if (ilEditClipboard::getAction() == "cut")
		{
			$ilUser->clipboardDeleteObjectsOfType("page");
			$ilUser->clipboardDeleteObjectsOfType("chap");
			$ilUser->clipboardDeleteObjectsOfType("sco");
			ilEditClipboard::clear();
		}
	}

	/**
	* Insert scos from clipboard
	*/
	static function insertScoClip($a_slm_obj)
	{
		global $ilCtrl, $ilUser;
		
		// @todo: move this to a service since it can be used here, too
		include_once("./Modules/LearningModule/classes/class.ilEditClipboard.php");

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004OrganizationHFormGUI.php");
		$node_id = ilSCORM2004OrganizationHFormGUI::getPostNodeId();
		$first_child = ilSCORM2004OrganizationHFormGUI::getPostFirstChild();
		
		$tree = ilSCORM2004Node::getTree($a_slm_obj->getId());
		
		if (!$first_child)	// insert after node id
		{
			$parent_id = $tree->getParentId($node_id);
			$target = $node_id;
		}
		else													// insert as first child
		{
			$parent_id = $node_id;
			$target = IL_FIRST_NODE;
		}

		// cut and paste
		$scos = $ilUser->getClipboardObjects("sco");
		$copied_nodes = array();
		foreach ($scos as $sco)
		{
			$cid = ilSCORM2004Node::pasteTree($a_slm_obj, $sco["id"], $parent_id, $target,
				$sco["insert_time"], $copied_nodes,
				(ilEditClipboard::getAction() == "copy"));
			$target = $cid;
		}
		//ilLMObject::updateInternalLinks($copied_nodes);

		if (ilEditClipboard::getAction() == "cut")
		{
			$ilUser->clipboardDeleteObjectsOfType("page");
			$ilUser->clipboardDeleteObjectsOfType("chap");
			$ilUser->clipboardDeleteObjectsOfType("sco");
			ilEditClipboard::clear();
		}
	}

	/**
	* Insert Chapter from clipboard
	*/
	function insertChapterClip($a_slm_obj, $a_as_sub = false)
	{
		global $ilUser, $ilCtrl, $ilLog;
		
		// @todo: move this to a service since it can be used here, too
		include_once("./Modules/LearningModule/classes/class.ilEditClipboard.php");
		
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004OrganizationHFormGUI.php");
		$node_id = ilSCORM2004OrganizationHFormGUI::getPostNodeId();
		$first_child = ilSCORM2004OrganizationHFormGUI::getPostFirstChild();
		
		$tree = ilSCORM2004Node::getTree($a_slm_obj->getId());
		
		if ($a_as_sub)		// as subchapter
		{
			if (!$first_child)	// insert under parent
			{
				$parent_id = $node_id;
				$target = "";
			}
			else													// we shouldnt end up here
			{
				return;
			}
		}
		else	// as chapter
		{
			if (!$first_child)	// insert after node id
			{
				$parent_id = $tree->getParentId($node_id);
				$target = $node_id;
			}
			else													// insert as first child
			{
				$parent_id = $node_id;
				$target = IL_FIRST_NODE;
				
				// do not move a chapter in front of a sco (maybe never needed)
				$childs = $tree->getChildsByType($parent_id, "sco");
				if (count($childs) != 0)
				{
					$target = $childs[count($childs) - 1]["obj_id"];
				}
			}
		}
		
		// copy and paste
		$chapters = $ilUser->getClipboardObjects("chap", true);
		$copied_nodes = array();
		foreach ($chapters as $chap)
		{
			$cid = ilSCORM2004Node::pasteTree($a_slm_obj, $chap["id"], $parent_id,
				$target, $chap["insert_time"], $copied_nodes,
				(ilEditClipboard::getAction() == "copy"));
			$target = $cid;
		}
		//ilLMObject::updateInternalLinks($copied_nodes);

		if (ilEditClipboard::getAction() == "cut")
		{
			$ilUser->clipboardDeleteObjectsOfType("page");
			$ilUser->clipboardDeleteObjectsOfType("chap");
			$ilUser->clipboardDeleteObjectsOfType("sco");
			ilEditClipboard::clear();
		}
	}

	/**
	* Paste item (tree) from clipboard to current scorm learning module
	*/
	static function pasteTree($a_target_slm, $a_item_id, $a_parent_id, $a_target, $a_insert_time,
		&$a_copied_nodes, $a_as_copy = false)
	{
		global $ilUser, $ilias, $ilLog;

		$item_slm_id = ilSCORM2004Node::_lookupSLMID($a_item_id);
		$item_type = ilSCORM2004Node::_lookupType($a_item_id);
		$slm_obj = $ilias->obj_factory->getInstanceByObjId($item_slm_id);
		if ($item_type == "chap")
		{
			include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Chapter.php");
			$item = new ilSCORM2004Chapter($slm_obj, $a_item_id);
		}
		else if ($item_type == "page")
		{
			include_once("./Modules/Scorm2004/classes/class.ilSCORM2004PageNode.php");
			$item = new ilSCORM2004PageNode($slm_obj, $a_item_id);
		}
		else if ($item_type == "sco")
		{
			include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Sco.php");
			$item = new ilSCORM2004Sco($slm_obj, $a_item_id);
		}

		$ilLog->write("Getting from clipboard type ".$item_type.", ".
			"Item ID: ".$a_item_id.", of original SLM: ".$item_slm_id);

		if ($item_slm_id != $a_target_slm->getId() && !$a_as_copy)
		{
			// @todo: check whether st is NOT in tree
			
			// "move" metadata to new lm
			include_once("Services/MetaData/classes/class.ilMD.php");
			$md = new ilMD($item_slm_id, $item->getId(), $item->getType());
			$new_md = $md->cloneMD($a_target_slm->getId(), $item->getId(), $item->getType());
			
			// update lm object
			$item->setSLMId($a_target_slm->getId());
			$item->setSLMObject($a_target_slm);
			$item->update();
			
			// delete old meta data set
			$md->deleteAll();
			
			if ($item_type == "page")
			{
				$page = $item->getPageObject();
				$page->buildDom();
				$page->setParentId($a_target_slm->getId());
				$page->update();
			}
		}

		if ($a_as_copy)
		{
			$target_item = $item->copy($a_target_slm);
			$a_copied_nodes[$item->getId()] = $target_item->getId();
		}
		else
		{
			$target_item = $item;
		}
		
		$ilLog->write("Putting into tree type ".$target_item->getType().
			"Item ID: ".$target_item->getId().", Parent: ".$a_parent_id.", ".
			"Target: ".$a_target.", Item LM:".$target_item->getContentObject()->getId());
		
		ilSCORM2004Node::putInTree($target_item, $a_parent_id, $a_target);
		
		$childs = $ilUser->getClipboardChilds($item->getId(), $a_insert_time);

		foreach($childs as $child)
		{
			ilSCORM2004Node::pasteTree($a_target_slm, $child["id"], $target_item->getId(),
				IL_LAST_NODE, $a_insert_time, $a_copied_nodes, $a_as_copy);
		}
		
		return $target_item->getId();
	}
	
	//Methods for Sequencing
	
	//objectives per node
	public function getObjectives()
	{
		include_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Objective.php");
		return ilSCORM2004Objective::fetchAllObjectives($this->slm_object,$this->getId());
	}
	
	public function deleteSeqInfo()
	{
		include_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Item.php");
		$seq_item = new ilSCORM2004Item($this->getId());
		$seq_item -> delete();
	}
	
	//function currently unused - shouldn't be removed if subchapter support may be added in the future
	public function parentHasSeqTemplate(){
		include_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Utilities.php");
		$seq_util = new ilSCORM2004Utilities($this->getId());
		return $seq_util -> parentHasSeqTemplate($this->slm_object);
	}
	
	
	public function exportAsScorm12() {
		//to implement
		return;
	}
	
	public function exportAsScorm13() {
		//to implement
		return;
	}
	
	

}
?>
