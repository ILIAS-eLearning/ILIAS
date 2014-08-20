<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject.php";
require_once "./Services/MetaData/classes/class.ilMDLanguageItem.php";
require_once("./Modules/Folder/classes/class.ilObjFolder.php");
require_once("./Modules/MediaPool/classes/class.ilMediaPoolItem.php");

/** @defgroup ModulesMediaPool Modules/MediaPool
 */

/**
* Media pool object
*
* @author Alex Killing <alex.killing@gmx.de>
*
* $Id$
*
* @ingroup ModulesMediaPool
*/
class ilObjMediaPool extends ilObject
{
	var $tree;

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjMediaPool($a_id = 0,$a_call_by_reference = true)
	{
		// this also calls read() method! (if $a_id is set)
		$this->type = "mep";
		$this->ilObject($a_id,$a_call_by_reference);
	}

	/**
	* Set default width
	*
	* @param	int		default width
	*/
	function setDefaultWidth($a_val)
	{
		$this->default_width = $a_val;
	}
	
	/**
	* Get default width
	*
	* @return	int		default width
	*/
	function getDefaultWidth()
	{
		return $this->default_width;
	}

	/**
	* Set default height
	*
	* @param	int		default height
	*/
	function setDefaultHeight($a_val)
	{
		$this->default_height = $a_val;
	}
	
	/**
	* Get default height
	*
	* @return	int		default height
	*/
	function getDefaultHeight()
	{
		return $this->default_height;
	}
	
	/**
	* Read pool data
	*/
	function read()
	{
		global $ilDB;
		
		parent::read();

		$set = $ilDB->query("SELECT * FROM mep_data ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
			);
		if ($rec = $ilDB->fetchAssoc($set))
		{
			$this->setDefaultWidth($rec["default_width"]);
			$this->setDefaultHeight($rec["default_height"]);
		}
		$this->tree = ilObjMediaPool::getPoolTree($this->getId());
	}


	/**
	* Get Pool Tree
	*
	* @param	int		Media pool ID
	*
	* @return	object	Tree object of media pool
	*/
	static function getPoolTree($a_obj_id)
	{
		$tree = new ilTree($a_obj_id);
		$tree->setTreeTablePK("mep_id");
		$tree->setTableNames("mep_tree", "mep_item");
		
		return $tree;
	}
	
	/**
	* create new media pool
	*/
	function create()
	{
		global $ilDB;
		
		parent::create();

		$ilDB->manipulate("INSERT INTO mep_data ".
			"(id, default_width, default_height) VALUES (".
			$ilDB->quote($this->getId(), "integer").", ".
			$ilDB->quote($this->getDefaultWidth(), "integer").", ".
			$ilDB->quote($this->getDefaultHeight(), "integer").
			")");

		$this->createMepTree();
	}

	/**
	 * Create media pool tree
	 *
	 * @param
	 * @return
	 */
	function createMepTree()
	{
		// create media pool tree
		$this->tree = new ilTree($this->getId());
		$this->tree->setTreeTablePK("mep_id");
		$this->tree->setTableNames('mep_tree','mep_item');
		$this->tree->addTree($this->getId(), 1);
	}
	
	
	/**
	* get media pool folder tree
	*/
	function &getTree()
	{
		return $this->tree;
	}

	/**
	* update object data
	*
	* @access	public
	* @return	boolean
	*/
	function update()
	{
		global $ilDB;
		
		if (!parent::update())
		{
			return false;
		}

		// put here object specific stuff
		$ilDB->manipulate("UPDATE mep_data SET ".
			" default_width = ".$ilDB->quote($this->getDefaultWidth(), "integer").",".
			" default_height = ".$ilDB->quote($this->getDefaultHeight(), "integer").
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
			);

		return true;
	}


	/**
	* delete object and all related data
	*
	* this method has been tested on may 9th 2004
	* media pool tree, media objects and folders
	* have been deleted correctly as desired
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{
		// always call parent delete function first!!
		if (!parent::delete())
		{
			return false;
		}

		// get childs
		$childs = $this->tree->getSubTree($this->tree->getNodeData($this->tree->readRootId()));

		// delete tree
		$this->tree->removeTree($this->tree->getTreeId());

		// delete childs
		foreach ($childs as $child)
		{
			$fid = ilMediaPoolItem::lookupForeignId($child["obj_id"]);
			switch ($child["type"])
			{
				case "mob":
					if  (ilObject::_lookupType($fid) == "mob")
					{
						include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
						$mob = new ilObjMediaObject($fid);
						$mob->delete();
					}
					break;

/*				case "fold":
					if  (ilObject::_lookupType($fid) == "fold")
					{
						include_once("./Modules/Folder/classes/class.ilObjFolder.php");
						$fold = new ilObjFolder($fid, false);
						$fold->delete();
					}
					break;*/
			}
		}
		
		return true;
	}


	/**
	* notifys an object about an event occured
	* Based on the event happend, each object may decide how it reacts.
	*
	* If you are not required to handle any events related to your module, just delete this method.
	* (For an example how this method is used, look at ilObjGroup)
	*
	* @access	public
	* @param	string	event
	* @param	integer	reference id of object where the event occured
	* @param	array	passes optional parameters if required
	* @return	boolean
	*/
	function notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params = 0)
	{
		global $tree;

		switch ($a_event)
		{
			case "link":

				//var_dump("<pre>",$a_params,"</pre>");
				//echo "Module name ".$this->getRefId()." triggered by link event. Objects linked into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "cut":

				//echo "Module name ".$this->getRefId()." triggered by cut event. Objects are removed from target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "copy":

				//var_dump("<pre>",$a_params,"</pre>");
				//echo "Module name ".$this->getRefId()." triggered by copy event. Objects are copied into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "paste":

				//echo "Module name ".$this->getRefId()." triggered by paste (cut) event. Objects are pasted into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "new":

				//echo "Module name ".$this->getRefId()." triggered by paste (new) event. Objects are applied to target object ref_id: ".$a_ref_id;
				//exit;
				break;
		}

		// At the beginning of the recursive process it avoids second call of the notify function with the same parameter
		if ($a_node_id==$_GET["ref_id"])
		{
			$parent_obj =& $this->ilias->obj_factory->getInstanceByRefId($a_node_id);
			$parent_type = $parent_obj->getType();
			if($parent_type == $this->getType())
			{
				$a_node_id = (int) $tree->getParentId($a_node_id);
			}
		}

		parent::notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params);
	}


	/**
	* get childs of node
	*/
	function getChilds($obj_id = "", $a_type = "")
	{
		$objs = array();
		$mobs = array();
		$pgs = array();
		if ($obj_id == "")
		{
			$obj_id = $this->tree->getRootId();
		}

		if ($a_type == "fold" || $a_type == "")
		{
			$objs = $this->tree->getChildsByType($obj_id, "fold");
		}
		if ($a_type == "mob" || $a_type == "")
		{		
			$mobs = $this->tree->getChildsByType($obj_id, "mob");
		}
		foreach($mobs as $key => $mob)
		{
			$objs[] = $mob;
		}
		if ($a_type == "pg" || $a_type == "")
		{		
			$pgs = $this->tree->getChildsByType($obj_id, "pg");
		}
		foreach($pgs as $key => $pg)
		{
			$objs[] = $pg;
		}

		return $objs;
	}

	/**
	* get childs of node
	*/
	function getChildsExceptFolders($obj_id = "")
	{
		$objs = array();
		$mobs = array();
		if ($obj_id == "")
		{
			$obj_id = $this->tree->getRootId();
		}

		$objs = $this->tree->getFilteredChilds(array("fold", "dummy"), $obj_id);
		return $objs;
	}

	/**
	* Get media objects
	*/
	function getMediaObjects($a_title_filter = "", $a_format_filter = "", $a_keyword_filter = '', $a_caption_filter)
	{
		global $ilDB;

		$query = "SELECT DISTINCT mep_tree.*, object_data.* ".
			"FROM mep_tree JOIN mep_item ON (mep_tree.child = mep_item.obj_id) ".
			" JOIN object_data ON (mep_item.foreign_id = object_data.obj_id) ";
			
		if ($a_format_filter != "" or $a_caption_filter != '')
		{
			$query.= " JOIN media_item ON (media_item.mob_id = object_data.obj_id) ";
		}
			
		$query .=
			" WHERE mep_tree.mep_id = ".$ilDB->quote($this->getId(), "integer").
			" AND object_data.type = ".$ilDB->quote("mob", "text");
			
		// filter
		if (trim($a_title_filter) != "")	// title
		{
			$query.= " AND ".$ilDB->like("object_data.title", "text", "%".trim($a_title_filter)."%");
		}
		if ($a_format_filter != "")			// format
		{
			$filter = ($a_format_filter == "unknown")
				? ""
				: $a_format_filter;
			$query.= " AND ".$ilDB->equals("media_item.format", $filter, "text", true);
		}
		if(trim($a_caption_filter))
		{
			$query .= 'AND '.$ilDB->like('media_item.caption', 'text', '%'.trim($a_caption_filter).'%');
		}
			
		$query.=
			" ORDER BY object_data.title";
		
		$objs = array();
		$set = $ilDB->query($query);
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$rec["foreign_id"] = $rec["obj_id"];
			$rec["obj_id"] = "";
			$objs[] = $rec;
		}
		
		// Keyword filter
		if($a_keyword_filter)
		{
			include_once './Services/MetaData/classes/class.ilMDKeyword.php';
			$res = ilMDKeyword::_searchKeywords($a_keyword_filter,'mob',0);
			
			foreach($objs as $obj)
			{
				if(in_array($obj['foreign_id'],$res))
				{
					$filtered[] = $obj;
				}
			}
			return (array) $filtered;
		}
		return $objs;
	}

	/**
	 * Get all media object ids
	 */
	function getAllMobIds($a_id)
	{
		global $ilDB;

		$query = "SELECT foreign_id as id FROM ".
			" mep_tree JOIN mep_item ON (mep_tree.child = mep_item.obj_id) ".
			" JOIN object_data ON (mep_item.foreign_id = object_data.obj_id) ".
			" WHERE mep_tree.mep_id = ".$ilDB->quote($a_id, "integer").
			" AND mep_item.type = ".$ilDB->quote("mob", "text").
			" AND object_data.type = ".$ilDB->quote("mob", "text");
		$set = $ilDB->query($query);
		$ids = array();
		while ($rec  = $ilDB->fetchAssoc($set))
		{
			$ids[] = $rec["id"];
		}
		return $ids;
	}
	
	/**
	* Get used formats
	*/
	function getUsedFormats()
	{
		global $ilDB, $lng;

		$query = "SELECT DISTINCT media_item.format f FROM mep_tree ".
			" JOIN mep_item ON (mep_item.obj_id = mep_tree.child) ".
			" JOIN object_data ON (mep_item.foreign_id = object_data.obj_id) ".
			" JOIN media_item ON (media_item.mob_id = object_data.obj_id) ".
			" WHERE mep_tree.mep_id = ".$ilDB->quote($this->getId(), "integer").
			" AND object_data.type = ".$ilDB->quote("mob", "text").
			" ORDER BY f";
		$formats = array();
		$set = $ilDB->query($query);
		while ($rec = $ilDB->fetchAssoc($set))
		{
			if ($rec["f"] != "")
			{
				$formats[$rec["f"]] = $rec["f"];
			}
			else
			{
				$formats["unknown"] = $lng->txt("mep_unknown");
			}
		}
		
		return $formats;
	}
	
	function getParentId($obj_id = "")
	{
		if ($obj_id == "")
		{
			return false;
		}
		if ($obj_id == $this->tree->getRootId())
		{
			return false;
		}

		return $this->tree->getParentId($obj_id);
	}
	
	/**
	 * Insert into tree 
	 * @param int 	$a_obj_id (mep_item obj_id)
	 * @param int $a_parent
	 */
	function insertInTree($a_obj_id, $a_parent = "")
	{
		if (!$this->tree->isInTree($a_obj_id))
		{
			$parent = ($a_parent == "")
				? $this->tree->getRootId()
				: $a_parent;
			$this->tree->insertNode($a_obj_id, $parent);
			return true;
		}
		else
		{
			return false;
		}
	}


	/**
	 * Delete a child of media tree 
	 * @param	int		mep_item id
	 */
	function deleteChild($obj_id)
	{
		$fid = ilMediaPoolItem::lookupForeignId($obj_id);
		$type = ilMediaPoolItem::lookupType($obj_id);
		$title = ilMediaPoolItem::lookupTitle($obj_id);
		
		$node_data = $this->tree->getNodeData($obj_id);
		$subtree = $this->tree->getSubtree($node_data);

		// delete tree
		if($this->tree->isInTree($obj_id))
		{
			$this->tree->deleteTree($node_data);
		}

		// delete objects
		foreach ($subtree as $node)
		{
			$fid = ilMediaPoolItem::lookupForeignId($node["child"]);
			if ($node["type"] == "mob")
			{
				if (ilObject::_lookupType($fid) == "mob")
				{
					$obj =& new ilObjMediaObject($fid);
					$obj->delete();
				}
			}

			if ($node["type"] == "fold")
			{
				if ($fid > 0 && ilObject::_lookupType($fid) == "fold")
				{
					$obj = new ilObjFolder($fid, false);
					$obj->delete();
				}
			}

			if ($node["type"] == "pg")
			{
				include_once("./Modules/MediaPool/classes/class.ilMediaPoolPage.php");
				if (ilMediaPoolPage::_exists($node["child"]))
				{
					$pg = new ilMediaPoolPage($node["child"]);
					$pg->delete();
				}
			}
			
			include_once("./Modules/MediaPool/classes/class.ilMediaPoolItem.php");
			$item = new ilMediaPoolItem($node["child"]);
			$item->delete();
		}
	}
	
	/**
	 * Check whether foreign id is in tree
	 *
	 * @param
	 * @return
	 */
	static function isForeignIdInTree($a_pool_id, $a_foreign_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM mep_tree JOIN mep_item ON (child = obj_id) WHERE ".
			" foreign_id = ".$ilDB->quote($a_foreign_id, "integer").
			" AND mep_id = ".$ilDB->quote($a_pool_id, "integer")
			);
		if ($rec  = $ilDB->fetchAssoc($set))
		{
			return true;
		}
		return false;
	}
	
	/**
	* Check wheter a mep item id is in the media pool
	*/
	static function isItemIdInTree($a_pool_id, $a_item_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM mep_tree WHERE child = ".
			$ilDB->quote($a_item_id, "integer").
			" AND mep_id = ".$ilDB->quote($a_pool_id, "integer"));
		if ($rec  = $ilDB->fetchAssoc($set))
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Create a new folder
	 *
	 * @param
	 * @return
	 */
	function createFolder($a_title, $a_parent = 0)
	{
		// perform save
		$mep_item = new ilMediaPoolItem();
		$mep_item->setTitle($a_title);
		$mep_item->setType("fold");
		$mep_item->create();
		if ($mep_item->getId() > 0)
		{
			$tree = $this->getTree();
			$parent = $a_parent > 0
				? $a_parent
				: $tree->getRootId();
			$this->insertInTree($mep_item->getId(), $parent);
			return $mep_item->getId();
		}
		return false;
	}
	
	/**
	 * Clone media pool
	 *
	 * @param int target ref_id
	 * @param int copy id
	 */
	public function cloneObject($a_target_id,$a_copy_id = 0)
	{
		global $ilDB, $ilUser, $ilias;

		$new_obj = parent::cloneObject($a_target_id,$a_copy_id);
	 	
		$new_obj->setTitle($this->getTitle());
		$new_obj->setDescription($this->getDescription());
		$new_obj->setDefaultWidth($this->getDefaultWidth());
		$new_obj->setDefaultHeight($this->getDefaultHeight());
		$new_obj->update();

		// copy content
		$this->copyTreeContent($new_obj, $new_obj->getTree()->readRootId(),
			$this->getTree()->readRootId());

		return $new_obj;
	}

	/**
	 * Copy tree content
	 *
	 * @param
	 * @return
	 */
	function copyTreeContent($a_new_obj, $a_target_parent, $a_source_parent)
	{
		include_once("./Modules/MediaPool/classes/class.ilMediaPoolItem.php");
		include_once("./Modules/MediaPool/classes/class.ilMediaPoolPage.php");
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		
		// get all chapters of root lm
		$nodes = $this->getTree()->getChilds($a_source_parent);
		foreach ($nodes as $node)
		{
			$item = new ilMediaPoolItem();
			$item->setType($node["type"]);
			switch ($node["type"])
			{
				case "mob":
					$mob_id = ilMediaPoolItem::lookupForeignId($node["child"]);
					$mob = new ilObjMediaObject($mob_id);
					$new_mob = $mob->duplicate();
					$item->setForeignId($new_mob->getId());
					$item->setTitle($new_mob->getTitle());
					$item->create();
					break;
				
				case "pg":
					$item->setTitle($node["title"]);
					$item->create();
					$page = new ilMediaPoolPage($node["child"]);
					$new_page = new ilMediaPoolPage();
					$new_page->setId($item->getId());
					$new_page->create();
					
					// copy page
					$page->copy($new_page->getId(), $new_page->getParentType(), $new_page->getParentId(), true);
					//$new_page->setXMLContent($page->copyXMLContent(true));
					//$new_page->buildDom();
					//$new_page->update();
					break;
					
				case "fold":
					$item->setTitle($node["title"]);
					$item->create();
					break;
			}

			// insert item into tree
			$a_new_obj->insertInTree($item->getId(), $a_target_parent);
			
			// handle childs
			$this->copyTreeContent($a_new_obj, $item->getId(), $node["child"]);
		}
	}
	
}
?>