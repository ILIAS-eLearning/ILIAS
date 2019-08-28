<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */


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
	/**
	 * @var ilDB
	 */
	protected $db;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	var $slm_id;
	var $type;
	var $id;
	var $slm_object;

	/**
	* @param	object		$a_slm_object		ilObjScorm2004LearningModule object
	*/
	function __construct($a_slm_object, $a_id = 0)
	{
		global $DIC;

		$this->db = $DIC->database();
		$this->user = $DIC->user();
		$this->id = $a_id;
		$this->setSLMObject($a_slm_object);
		$this->setSLMId($a_slm_object->getId());

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Tree.php");
		$this->tree = new ilSCORM2004Tree($a_slm_object->getId());
		/*$this->tree = new ilTree($a_slm_object->getId());
		$this->tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
		$this->tree->setTreeTablePK("slm_id");*/

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
	 * Get scorm learning module object
	 *
	 * @return	int		Scorm LM Object
	 */
	function getSLMObject()
	{
		return $this->slm_object;
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
		$ilDB = $this->db;

		if(!isset($this->data_record))
		{
			$query = "SELECT * FROM sahs_sc13_tree_node WHERE obj_id = ".
				$ilDB->quote($this->id, "integer");
			$obj_set = $ilDB->query($query);
			$this->data_record = $ilDB->fetchAssoc($obj_set);
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
		$ilUser = $this->user;

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
			$md_des = $md_gen->getDescription($md_des_ids[0]);
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
		global $DIC;

		$ilDB = $DIC->database();

		$query = "SELECT * FROM sahs_sc13_tree_node WHERE obj_id = ".
			$ilDB->quote($a_obj_id, "integer");
		$obj_set = $ilDB->query($query);
		$obj_rec = $ilDB->fetchAssoc($obj_set);

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
		global $DIC;

		$ilDB = $DIC->database();

		$query = "SELECT * FROM sahs_sc13_tree_node WHERE obj_id = ".
			$ilDB->quote($a_obj_id, "integer");
		$obj_set = $ilDB->query($query);
		$obj_rec = $ilDB->fetchAssoc($obj_set);

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
		global $DIC;

		$ilDB = $DIC->database();

		$query = "UPDATE sahs_sc13_tree_node SET ".
			" title = ".$ilDB->quote($a_title, "text").
			" WHERE obj_id = ".$ilDB->quote($a_obj_id, "integer");
		$ilDB->manipulate($query);
	}

	/**
	* Write import ID.
	*
	* @param	int		$a_id				Node ID
	* @param	string	$a_import_id		Import ID
	*/
	static function _writeImportId($a_id, $a_import_id)
	{
		global $DIC;

		$ilDB = $DIC->database();

		$q = "UPDATE sahs_sc13_tree_node ".
			"SET ".
			"import_id = ".$ilDB->quote($a_import_id, "text").",".
			"last_update = ".$ilDB->now().
			"WHERE obj_id = ".$ilDB->quote($a_id, "integer");

		$ilDB->manipulate($q);
	}

	/**
	* Create Node
	*
	* @param	boolean		Upload Mode
	*/
	function create($a_upload = false)
	{
		$ilDB = $this->db;

		// insert object data
		$id = $ilDB->nextId("sahs_sc13_tree_node");
		$query = "INSERT INTO sahs_sc13_tree_node (obj_id, title, type, slm_id, import_id, create_date) ".
			"VALUES (".
			$ilDB->quote($id, "integer").",".
			$ilDB->quote($this->getTitle(), "text").",".
			$ilDB->quote($this->getType(), "text").", ".
			$ilDB->quote($this->getSLMId(), "integer").",".
			$ilDB->quote($this->getImportId(), "text").
			", ".$ilDB->now().")";
		$ilDB->manipulate($query);
		$this->setId($id);

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
		$ilDB = $this->db;

		$this->updateMetaData();

		$query = "UPDATE sahs_sc13_tree_node SET ".
			" slm_id = ".$ilDB->quote($this->getSLMId(), "integer").
			" ,title = ".$ilDB->quote($this->getTitle(), "text").
			" WHERE obj_id = ".$ilDB->quote($this->getId(), "integer");

		$ilDB->manipulate($query);
	}

	/**
	* Delete Node
	*/
	function delete()
	{
		$ilDB = $this->db;
		
		$query = "DELETE FROM sahs_sc13_tree_node WHERE obj_id= ".
			$ilDB->quote($this->getId(), "integer");
		$ilDB->manipulate($query);

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
	static function _getIdForImportId($a_import_id)
	{
		global $DIC;

		$ilDB = $DIC->database();
		
		$ilDB->setLimit(1);
		$q = "SELECT * FROM sahs_sc13_tree_node WHERE import_id = ".
			$ilDB->quote($a_import_id, "text")." ".
			" ORDER BY create_date DESC";
		$obj_set = $ilDB->query($q);
		while ($obj_rec = $ilDB->fetchAssoc($obj_set))
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
	static function _exists($a_id)
	{
		global $DIC;

		$ilDB = $DIC->database();
		
		include_once("./Services/Link/classes/class.ilInternalLink.php");
		if (is_int(strpos($a_id, "_")))
		{
			$a_id = ilInternalLink::_extractObjIdOfTarget($a_id);
		}
		
		$q = "SELECT * FROM sahs_sc13_tree_node WHERE obj_id = ".
			$ilDB->quote($a_id, "integer");
		$obj_set = $ilDB->query($q);
		if ($obj_rec = $ilDB->fetchAssoc($obj_set))
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
	static function _deleteAllSLMNodes($a_slm_object)
	{
		global $DIC;

		$ilDB = $DIC->database();
		
		$query = "SELECT * FROM sahs_sc13_tree_node ".
			"WHERE slm_id = ".$ilDB->quote($a_slm_object->getId(), "integer")." ";
		$obj_set = $ilDB->query($query);

		require_once("./Modules/Scorm2004/classes/class.ilSCORM2004NodeFactory.php");
		while($obj_rec = $ilDB->fetchAssoc($obj_set))
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
	static function _lookupSLMID($a_id)
	{
		global $DIC;

		$ilDB = $DIC->database();

		$query = "SELECT * FROM sahs_sc13_tree_node WHERE obj_id = ".
			$ilDB->quote($a_id, "integer")."";
		$obj_set = $ilDB->query($query);
		$obj_rec = $ilDB->fetchAssoc($obj_set);

		return $obj_rec["slm_id"];
	}

	/**
	* put this object into content object tree
	*/
	static function putInTree($a_obj, $a_parent_id = "", $a_target_node_id = "")
	{
		$tree = new ilTree($a_obj->getSLMId());
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
			$childs = $tree->getChilds($parent_id);

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
		global $DIC;

		$ilUser = $DIC->user();
		
		$tree = ilSCORM2004Node::getTree($a_slm_obj_id);
		
		$ilUser->clipboardDeleteObjectsOfType("page");
		$ilUser->clipboardDeleteObjectsOfType("chap");
		$ilUser->clipboardDeleteObjectsOfType("sco");
		$ilUser->clipboardDeleteObjectsOfType("ass");
		
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
	static function clipboardCut($a_slm_obj_id, $a_ids)
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
		global $DIC;

		$ilUser = $DIC->user();
		
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
		$source_parent_type = "";
		if ($ilUser->getClipboardObjects("page"))
		{
			$pages = $ilUser->getClipboardObjects("page");
		}
		else if ($ilUser->getClipboardObjects("pg"))
		{
			$source_parent_type = "lm";
			$pages = $ilUser->getClipboardObjects("pg");
		}
		$copied_nodes = array();

		foreach ($pages as $pg)
		{
			$cid = ilSCORM2004Node::pasteTree($a_slm_obj, $pg["id"], $parent_id, $target,
				$pg["insert_time"], $copied_nodes,
				(ilEditClipboard::getAction() == "copy"), true, $source_parent_type);
			$target = $cid;
		}
		//ilLMObject::updateInternalLinks($copied_nodes);

		if (ilEditClipboard::getAction() == "cut")
		{
			$ilUser->clipboardDeleteObjectsOfType("page");
			$ilUser->clipboardDeleteObjectsOfType("chap");
			$ilUser->clipboardDeleteObjectsOfType("sco");
			$ilUser->clipboardDeleteObjectsOfType("ass");
			$ilUser->clipboardDeleteObjectsOfType("pg");
			ilEditClipboard::clear();
		}
	}

	/**
	 * Insert assets from clipboard
	 */
	static function insertAssetClip($a_slm_obj, $a_type = "ass")
	{
		global $DIC;

		$ilCtrl = $DIC->ctrl();
		$ilUser = $DIC->user();
		
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
		$scos = $ilUser->getClipboardObjects($a_type);
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
			$ilUser->clipboardDeleteObjectsOfType("ass");
			ilEditClipboard::clear();
		}
	}

	/**
	 * Insert scos from clipboard
	 */
	static function insertScoClip($a_slm_obj)
	{
		self::insertAssetClip($a_slm_obj, "sco");
	}


	/**
	* Insert Chapter from clipboard
	*/
	static function insertChapterClip($a_slm_obj, $a_as_sub = false)
	{
		global $DIC;

		$ilUser = $DIC->user();
		$ilCtrl = $DIC->ctrl();
		$ilLog = $DIC["ilLog"];
		
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
			$ilUser->clipboardDeleteObjectsOfType("ass");
			ilEditClipboard::clear();
		}
	}

	/**
	 * Paste item (tree) from clipboard or other learning module to target scorm learning module
	 *
	 * @param object $a_target_slm target scorm 2004 learning module object
	 * @param int $a_item_id id of item that should be pasted
	 * @param int $a_parent_id parent id in target tree,
	 * @param int $a_target predecessor target node, no ID means: last child
	 * @param string $a_insert_time cliboard insert time (not needed, if $a_from_cliboard is false)
	 * @param array $a_copied_nodes array of IDs od copied nodes, key is ID of source node, value is ID of copied node
	 * @param bool $a_as_copy if true, items are copied otherwise they are moved
	 * @param bool $a_from_clipboard if true, child node information is read from clipboard, otherwise from source tree
	 */
	static function pasteTree($a_target_slm, $a_item_id, $a_parent_id, $a_target, $a_insert_time,
		&$a_copied_nodes, $a_as_copy = false, $a_from_clipboard = true, $a_source_parent_type = "")
	{
		global $DIC;

		$ilUser = $DIC->user();
		$ilLog = $DIC["ilLog"];

		$item_type = "";

		if (in_array($a_source_parent_type, array("", "sahs")))
		{
			// source lm id, item type and lm object
			$item_slm_id = ilSCORM2004Node::_lookupSLMID($a_item_id);
			$item_type = ilSCORM2004Node::_lookupType($a_item_id);

			include_once("./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php");
			$slm_obj = new ilObjSCORM2004LearningModule($item_slm_id, false);

			$ilLog->write("Getting from clipboard type ".$item_type.", ".
				"Item ID: ".$a_item_id.", of original SLM: ".$item_slm_id);
		}
		else if (in_array($a_source_parent_type, array("lm")))
		{
			include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
			$item_lm_id = ilLMObject::_lookupContObjId($a_item_id);
			$item_type = ilLMObject::_lookupType($a_item_id, $item_lm_id);

			include_once("./Modules/LearningModule/classes/class.ilObjLearningModule.php");
			$lm_obj = new ilObjLearningModule($item_lm_id, false);

			$ilLog->write("Getting from clipboard type ".$item_type.", ".
				"Item ID: ".$a_item_id.", of original SLM: ".$item_lm_id);
		}



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
		else if ($item_type == "ass")
		{
			include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Asset.php");
			$item = new ilSCORM2004Asset($slm_obj, $a_item_id);
		}
		else if ($item_type == "pg")
		{
			include_once("./Modules/LearningModule/classes/class.ilLMPageObject.php");
			$item = new ilLMPageObject($lm_obj, $a_item_id);
		}


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
				$page->buildDom($a_from_clipboard);
				$page->setParentId($a_target_slm->getId());
				$page->update();
			}
		}

		if ($a_as_copy)
		{
			if ($a_source_parent_type == "lm")
			{
				if ($item_type = "pg")
				{
					include_once("./Modules/Scorm2004/classes/class.ilSCORM2004PageNode.php");
					$target_item = ilSCORM2004PageNode::copyPageFromLM($a_target_slm, $item);
				}
			}
			else
			{
				$target_item = $item->copy($a_target_slm);
			}
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
		
		if ($a_from_clipboard)
		{
			$childs = $ilUser->getClipboardChilds($item->getId(), $a_insert_time);
		}
		else
		{
			// get childs of source tree
			$source_tree = $slm_obj->getTree();
			$childs = $source_tree->getChilds($a_item_id);
		}

		foreach($childs as $child)
		{
			$child_id = ($a_from_clipboard)
				? $child["id"]
				: $child["child"];
			ilSCORM2004Node::pasteTree($a_target_slm, $child_id, $target_item->getId(),
				IL_LAST_NODE, $a_insert_time, $a_copied_nodes, $a_as_copy, $a_from_clipboard, $a_source_parent_type);
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
//	public function parentHasSeqTemplate(){
//		include_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Utilities.php");
//		$seq_util = new ilSCORM2004Utilities($this->getId());
//		return $seq_util -> parentHasSeqTemplate($this->slm_object);
//	}
	
	
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
