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

	function read()
	{
		parent::read();

		$this->tree =& new ilTree($this->getId());
		$this->tree->setTreeTablePK("mep_id");
		$this->tree->setTableNames('mep_tree','object_data');
	}


	/**
	* create new media pool
	*/
	function create()
	{
		parent::create();

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
		if (!parent::update())
		{
			return false;
		}

		// put here object specific stuff

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
