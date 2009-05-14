<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
require_once "./Services/MetaData/classes/class.ilMDLanguageItem.php";
require_once("./Modules/Folder/classes/class.ilObjFolder.php");

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
		$tree->setTableNames("mep_tree", "object_data");
		
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
			"(id) VALUES (".
			$ilDB->quote($this->getId(), "integer").
			")");
			
		// create media pool tree
		$this->tree =& new ilTree($this->getId());
		$this->tree->setTreeTablePK("mep_id");
		$this->tree->setTableNames('mep_tree','object_data');
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
			switch ($child["type"])
			{
				case "mob":
					include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
					$mob = new ilObjMediaObject($child["obj_id"]);
					$mob->delete();
					break;

				case "fold":
					include_once("./Modules/Folder/classes/class.ilObjFolder.php");
					$fold = new ilObjFolder($child["obj_id"], false);
					$fold->delete();
					break;
			}
		}



		return true;
	}

	/**
	* init default roles settings
	*
	* If your module does not require any default roles, delete this method
	* (For an example how this method is used, look at ilObjForum)
	*
	* @access	public
	* @return	array	object IDs of created local roles.
	*/
	function initDefaultRoles()
	{
		global $rbacadmin;

		// create a local role folder
		//$rfoldObj = $this->createRoleFolder("Local roles","Role Folder of forum obj_no.".$this->getId());

		// create moderator role and assign role to rolefolder...
		//$roleObj = $rfoldObj->createRole("Moderator","Moderator of forum obj_no.".$this->getId());
		//$roles[] = $roleObj->getId();

		//unset($rfoldObj);
		//unset($roleObj);

		return $roles ? $roles : array();
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
		if ($obj_id == "")
		{
			$obj_id = $this->tree->getRootId();
		}

		if ($a_type != "mob")
		{
			$objs = $this->tree->getChildsByType($obj_id, "fold");
		}
		if ($a_type != "fold")
		{		
			$mobs = $this->tree->getChildsByType($obj_id, "mob");
		}
		foreach($mobs as $key => $mob)
		{
			$objs[] = $mob;
		}

		return $objs;
	}

	/**
	* Get media objects
	*/
	function getMediaObjects($a_title_filter = "", $a_format_filter = "")
	{
		global $ilDB;

		$query = "SELECT DISTINCT mep_tree.*, object_data.* FROM mep_tree JOIN object_data ".
			"ON (mep_tree.child = object_data.obj_id) ";
			
		if ($a_format_filter != "")
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
			
		$query.=
			" ORDER BY object_data.title";
		
		$objs = array();
		$set = $ilDB->query($query);
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$objs[] = $rec;
		}
		return $objs;
	}
	
	/**
	* Get used formats
	*/
	function getUsedFormats()
	{
		global $ilDB, $lng;

		$query = "SELECT DISTINCT media_item.format f FROM mep_tree ".
			" JOIN object_data ON (mep_tree.child = object_data.obj_id) ".
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


	function deleteChild($obj_id)
	{
		$type = ilObject::_lookupType($obj_id);
		$title = ilObject::_lookupTitle($obj_id);


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
			if ($node["type"] == "mob")
			{
				$obj =& new ilObjMediaObject($node["child"]);
				$obj->delete();
			}

			if ($node["type"] == "fold")
			{
				$obj =& new ilObjFolder($node["child"], false);
				$obj->delete();
			}
		}
	}
}
?>
