<?php
/**
* Class Object
* Basic functions for all objects
*
* @author Stefan Meyer <smeyer@databay.de>
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package ilias-core
*/
class Object
{
	/**
	* ilias object
	* @var		object ilias
	* @access	private
	*/
	var $ilias;

	/**
	* lng object
	* @var		object language
	* @access	private
	*/
	var $lng;

	/**
	* object id
	* @var		integer object id of object itself
	* @access	private
	*/
	var $id;	// true object_id!!!!
	var $ref_id;// reference_id
	var $type;
	var $title;
	var $desc;
	var $owner;
	var $create_date;
	var $last_update;

	/**
	* indicates if object is a referenced object
	* @var		boolean
	* @access	private
	*/
	var $referenced;

	/**
	* object list
	* @var		array	contains all child objects of current object
	* @access	private
	*/
	var $objectList;


	/**
	* max title length
	* @var int
	*/
	var $max_title;


	/**
	* max description length
	* @var int
	*/
	var $max_desc;


	/**
	* add dots to shortened titles and descriptions
	* @var boolean
	*/
	var $add_dots;


	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id NOT object_id!!!!
	*/
	function Object($a_id = 0, $reference = true)
	{
		global $ilias, $lng;

		$this->ilias =& $ilias;
		$this->lng =& $lng;

		$this->max_title = MAXLENGTH_OBJ_TITLE;
		$this->max_desc = MAXLENGTH_OBJ_DESC;
		$this->add_dots = true;

		$this->referenced = $reference;
		if ($a_id == 0) $this->referenced = false;		// newly created objects are never referenced
														// they will get referenced if createReference() is called

		if ($this->referenced)
		{
			$this->ref_id = $a_id;
		}
		else
		{
			$this->id = $a_id;
		}

		// read object data
		if ($a_id != 0)
		{
			$this->read();
		}
	}


	/**
	* read object data from db into object
	* @access	public
	*/
	function read()
	{
		global $ilias;

		if ($this->referenced)
		{
			$obj = getObjectByReference($this->ref_id);
			$this->id = $obj["obj_id"];
		}
		else
		{
			$obj = getObject($this->id);
		}

		$this->type = $obj["type"];
		$this->title = $obj["title"];
		$this->desc = $obj["description"];
		$this->owner = $obj["owner"];
		$this->create_date = $obj["create_date"];
		$this->last_update = $obj["last_update"];
	}


	/**
	* get object id
	* @access	public
	* @return	int		object id
	*/
	function getId()
	{
		return $this->id;
	}


	/**
	* set object id
	* @access	public
	* @param	int		$a_id		object id
	*/
	function setId($a_id)
	{
		$this->id = $a_id;
	}


	/**
	* set reference id
	* @access	public
	* @param	int		$a_id		reference id
	*/
	function setRefId($a_id)
	{
		$this->ref_id = $a_id;
	}


	/**
	* get reference id
	* @access	public
	* @return	int		reference id
	*/
	function getRefId()
	{
		return $this->ref_id;
	}


	/**
	* get object type
	* @access	public
	* @return	string		object type
	*/
	function getType()
	{
		return $this->type;
	}


	/**
	* set object type
	* @access	public
	* @param	int		$a_type		object type
	*/
	function setType($a_type)
	{
		$this->type = $a_type;
	}


	/**
	* get object title
	* @access	public
	* @return	string		object title
	*/
	function getTitle()
	{
		return $this->title;
	}


	/**
	* set object title
	* @access	public
	* @param	string		$a_title		object title
	*/
	function setTitle($a_title)
	{
		$this->title = addslashes(shortenText($a_title, $this->max_title, $this->add_dots));
	}


	/**
	* get object description
	* @access	public
	* @return	string		object description
	*/
	function getDescription()
	{
		return $this->desc;
	}


	/**
	* set object description
	* @access	public
	* @param	string		$a_desc		object description
	*/
	function setDescription($a_desc)
	{
		$this->desc = addslashes(shortenText($a_desc, $this->max_desc, $this->add_dots));
	}


	/**
	* get object owner
	* @access	public
	* @return	int			owner id
	*/
	function getOwner()
	{
		return $this->owner;
	}


	/**
	* set object owner
	* @access	public
	* @param	int		$a_owner		owner id
	*/
	function setOwner($a_owner)
	{
		$this->owner = $a_owner;
	}


	/**
	* get create date
	* @access	public
	* @return	string			creation date
	*/
	function getCreateDate()
	{
		return $this->create_date;
	}


	/**
	* get last update date
	* @access	public
	* @return	string			date of last update
	*/
	function getLastUpdateDate()
	{
		return $this->last_update;
	}


	/**
	* create
	*
	* note: title, description and type should be set when this function is called
	*
	* @return	int		object id
	*/
	function create()
	{
		global $ilias;

		if (!isset($this->type))
		{
			$message = "Object->create(): No object type given!";
			$ilias->raiseError($message,$ilias->error_obj->WARNING);
		}

		if (empty($this->title))
		{
			$message = "Object->create(): No title given! A title is required!";
			$ilias->raiseError($message,$ilias->error_obj->WARNING);
		}

		$this->title = addslashes(shortenText($this->title, $this->max_title, $this->add_dots));
		$this->desc = addslashes(shortenText($this->desc, $this->max_desc, $this->add_dots));

		$q = "INSERT INTO object_data ".
			 "(type,title,description,owner,create_date,last_update) ".
			 "VALUES ".
			 "('".$this->type."','".$this->title."','".$this->desc."',".
			 "'".$ilias->account->getId()."',now(),now())";
		$ilias->db->query($q);

		$this->id = getLastInsertId();

		$this->read();

		return $this->id;
	}


	/*
	* update object in db
	*/
	function update()
	{
		global $ilias;

		// cut length of text
		$this->title = addslashes(shortenText($this->title, $this->max_title, $this->add_dots));
		$this->desc = addslashes(shortenText($this->desc, $this->max_desc, $this->add_dots));

		$q = "UPDATE object_data ".
			"SET ".
			"title = '".$this->title."',".
			"description = '".$this->desc."', ".
			"last_update = now() ".
			"WHERE obj_id = '".$this->id."'";
		$ilias->db->query($q);

		$this->read();						// to get all data (incl. dates!)

		return true;
	}


	/**
	* maybe this method should be in tree object!?
	* @todo	role/rbac stuff
	*/
	function putInTree($a_parent)
	{
		global $tree, $rbacadmin;

		$tree->insertNode($this->getRefId(), $a_parent);

		// TODO: MAKE THIS WORK!
		/*
		$parentRoles = $rbacadmin->getParentRoleIds();
		foreach ($parentRoles as $parRol)
		{
    		// Es werden die im Baum am 'nächsten liegenden' Templates ausgelesen
			$ops = $rbacreview->getOperations($parRol["obj_id"], $this->getType(), $parRol["parent"]);
			$rbacadmin->grantPermission($parRol["obj_id"], $ops, $this->getRefId(), $a_parent);
		}*/
	}


	/**
	* creates reference for object
	*/
	function createReference()
	{
		if (!isset($this->id))
		{
			$message = "perm::createNewReference(): No obj_id given!";
			$ilias->raiseError($message,$ilias->error_obj->WARNING);
		}

		$q = "INSERT INTO object_reference ".
			 "(obj_id) VALUES ('".$this->id."')";
		$this->ilias->db->query($q);

		$this->ref_id = getLastInsertId();

		return $this->ref_id;
	}


	/**
	* copy all entries of an object !!! IT MUST RETURN THE NEW OBJECT ID !!
	* @access	public
	* @return	new object id
	*/
	function cloneObject($a_obj_id,$a_parent,$a_dest_id,$a_dest_parent)
	{
		global $tree,$rbacadmin,$rbacreview;

		$object = getObject($a_obj_id);
		$new_id = copyObject($a_obj_id);
		$tree->insertNode($new_id,$a_dest_id,$a_dest_parent);

		$parentRoles = $rbacadmin->getParentRoleIds($a_dest_id,$a_dest_parent);

		foreach ($parentRoles as $parRol)
		{
			// Es werden die im Baum am 'nächsten liegenden' Templates ausgelesen
			$ops = $rbacreview->getOperations($parRol["obj_id"], $object["type"], $parRol["parent"]);
			$rbacadmin->grantPermission($parRol["obj_id"],$ops, $new_id);
		}
		return $new_id;
	}

	/**
	* copy object to clipboard
	*/
	function copyObject($a_post_data,$a_post_cmd,$a_obj_id)
	{
		global $tree, $rbacsystem, $rbacadmin, $objDefinition;

		// AT LEAST ONE OBJECT HAS TO BE CHOSEN.
		if (!isset($a_post_data))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		// FOR ALL SELECTED OBJECTS
		foreach($a_post_data as $id)
		{
			// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES
			$node_data = $tree->getNodeData($id);
			$subtree_nodes = $tree->getSubTree($node_data);

			$all_node_data[] = $node_data;
			$all_subtree_nodes[] = $subtree_nodes;

			// CHECK DELETE PERMISSION OF ALL OBJECTS
			foreach($subtree_nodes as $node)
			{
				if(!$rbacsystem->checkAccess('read',$node["obj_id"]))
				{
					$no_copy[] = $node["obj_id"];
					$perform_copy = false;
				}
			}
		}

		// IF THERE IS ANY OBJECT WITH NO PERMISSION TO 'read'
		if (count($no_copy))
		{
			$no_copy = implode(',',$no_copy);
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_copy")." ".
									 $no_copy,$this->ilias->error_obj->MESSAGE);
		}

		// COPY TRHEM
		// SAVE SUBTREE
		foreach($a_post_data as $id)
		{
			$tree->saveSubTree($id);
			$clipboard[$id]["parent"] = $a_obj_id;
			$clipboard[$id]["cmd"] = $a_post_cmd;
		}
		$_SESSION["clipboard"] = $clipboard;
	}

	/**
	*
	*/
	function pasteObject($a_obj_id,$a_parent_id)
	{
		global $rbacsystem,$tree,$objDefinition,$lng;

		// CHECK SOME THINGS
		foreach($_SESSION["clipboard"] as $id => $object)
		{
			// IF CMD WAS 'copy' CALL PRIVATE CLONE METHOD
			if($object["cmd"] == $lng->txt('copy'))
			{
				$this->cloneObject($a_obj_id,$a_parent_id);
				return true;
			}

			$obj_data = getObject($id);
			$data = $tree->getNodeData($id);
			// CHECK ACCESS
			if(!$rbacsystem->checkAccess('create',$a_obj_id,$obj_data["type"]))
			{
				$no_paste[] = $id;
			}
			// CHECK IF REFERENCE ALREADY EXISTS
			if($data["obj_id"])
			{
				$exists[] = $id;
			}
			// CHECK IF PASTE OBJECT SHALL BE CHILD OF ITSELF
			if ($tree->isGrandChild($id,$a_obj_id))
			{
				$is_child[] = $id;
			}
			// CHECK IF OBJECT IS ALLOWED TO CONTAIN PASTED OBJECT AS SUBOBJECT
			$object = getObject($a_obj_id);
			if(!in_array($obj_data["type"],array_keys($objDefinition->getSubObjects($object["type"]))))
			{
				$not_allowed_subobject[] = $obj_data["type"];
			}
		}
		if(count($exists))
		{
			$this->ilias->raiseError($this->lng->txt("msg_obj_exists"),$this->ilias->error_obj->MESSAGE);
		}
		if(count($is_child))
		{
			$this->ilias->raiseError($this->lng->txt("msg_not_in_itself")." ".implode(',',$is_child),
									 $this->ilias->error_obj->MESSAGE);
		}
		if(count($not_allowed_subobject))
		{
			$this->ilias->raiseError($this->lng->txt("msg_may_not_contain")." ".implode(',',$not_allowed_subobject),
									 $this->ilias->error_obj->MESSAGE);
		}
		if(count($no_paste))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_paste")." ".
									 implode(',',$no_paste),$this->ilias->error_obj->MESSAGE);
		}
		foreach($_SESSION["clipboard"] as $id => $object)
		{
			$this->insertSavedNodes($id,$object["parent"],$a_obj_id,$a_parent_id,-(int) $id);
		}
		$this->clearObject();
	}


	/**
	* add a new local role
	* @access public
	**/
	function addRoleObject()
	{
		global $tree,$rbacadmin,$rbacreview,$rbacsystem;

		$object = getObject($_GET["ref_id"]);
		$rolf_data = $rbacadmin->getRoleFolderOfObject($_GET["ref_id"]);

		if (!($rolf_id = $rolf_data["child"]))
		{
			$mods = $rbacadmin->getModules($object["type"],$_GET["ref_id"]);
			//if (!in_array('rolf',$rbacadmin->getModules($object["type"],$_GET["ref_id"])))
			if (!isset($mods["rolf"]))
			{
				$this->ilias->raiseError("'".$object["title"]."' are not allowed to contain Role Folder",$this->ilias->error_obj->WARNING);
			}

			// CHECK ACCESS 'create' rolefolder
			if ($rbacsystem->checkAccess('create',$_GET["ref_id"],'rolf'))
			{
				require_once ("classes/class.RoleFolderObject.php");
				$rolfObj = new RoleFolderObject();
				$rolfObj->setTitle("Role Folder");
				$rolfObj->setDescription("Automaticly generated Role Folder ".$this->ref_id);
				$rolfObj->create();
				$rolfObj->createReference();
				$rolfObj->putInTree($this->ref_id);

				$rolf_id = $rolfObj->getRefId();

				// Suche aller Parent Rollen im Baum
				$parentRoles = $rbacadmin->getParentRoleIds($this->ref_id);
				foreach ($parentRoles as $parRol)
				{
					// Es werden die im Baum am 'nächsten liegenden' Templates ausgelesen
					$ops = $rbacreview->getOperations($parRol["obj_id"],'rolf',$parRol["parent"]);
					// TODO: make this work:
					//$rbacadmin->grantPermission($parRol["obj_id"],$ops,$rolf_id);
				}
			}
			else
			{
				$this->ilias->raiseError("No permission to create role folder",$this->ilias->error_obj->WARNING);
			}
		}

		// CHECK ACCESS 'write' of role folder
		if ($rbacsystem->checkAccess('write',$rolf_id))
		{
			require_once ("classes/class.RoleObject.php");
			$roleObj = new RoleObject();
			$roleObj->setTitle($_POST["Flocal_role"]);
			$roleObj->setDescription("No description");
			$roleObj->create();
			$new_obj_id = $roleObj->getId();
			$rbacadmin->assignRoleToFolder($new_obj_id,$rolf_id,$_GET["ref_id"],'y');
		}
		else
		{
			$this->ilias->raiseError("No permission to write to role folder",$this->ilias->error_obj->WARNING);
		}

		return true;
	}

	/**
	* show owner of object
	* @access	public
	* @return	string	owners fullname or unknown
	**/
	function ownerObject()
	{
		global $lng;

		$owner = TUtil::getOwner($_GET["ref_id"]);

		if (is_object($owner))
		{
			return $owner->getFullname();
		}
		else
		{
			return $lng->txt("unknown");
		}
	}


	/**
	* This method is called automatically from class.Admin.php
	* It removes all object entries for a specific object
	* This method should be overwritten by all object types
	* @access public
	**/
	function deleteObject($a_obj_id, $a_parent_id, $a_tree_id = 1)
	{
		global $rbacadmin, $tree;
		
		// ALL OBJECT ENTRIES IN TREE HAVE BEEN DELETED FROM CLASS ADMIN.PHP

		// IF THERE IS NO OTHER REFERENCE, DELETE ENTRY IN OBJECT_DATA
		if (countReferencesOfObject($a_obj_id) == 1)
		{
			deleteObject($a_obj_id);
		}

		// DELETE PERMISSION ENTRIES IN RBAC_PA
		$rbacadmin->revokePermission($a_obj_id);

		return true;
	}


	function trashObject()
	{
		global $lng,$tree;


		$objects = $tree->getSavedNodeData($_GET["ref_id"]);
		if(count($objects))
		{
			$data["empty"] = false;
			$data["cols"] = array("","type", "title", "description", "last_change");
			
			foreach($objects as $obj_data)
			{
				$data["data"]["$obj_data[child]"] = array(
					"checkbox"    => "",
					"type"        => $obj_data["type"],
					"title"       => $obj_data["title"],
					"desc"        => $obj_data["desc"],
					"last_update" => $obj_data["last_update"]);
			}
			$data["buttons"] = array( "btn_undelete"  => $lng->txt("btn_undelete"),
									  "btn_remove_system"  => $lng->txt("btn_remove_system"));
			return $data;
		}
		else
		{
			$this->ilias->error_obj->sendInfo($lng->txt("msg_trash_empty"));
			$data["empty"] = true;
			return $data;
		}
	}

	/**
	* returns the parent object id of $_GET["parent"]
	* @access	private
	* @param	integer		node_id where to start
	* @return	integer
	*/
	function getParentObjectId($a_start = 0)
	{
		global $tree;

		$a_start = $a_start ? $a_start : $_GET["parent"];

		$path_ids = $tree->getPathId($a_start,ROOT_FOLDER_ID);
		array_pop($path_ids);

		return array_pop($path_ids);
	} //function


	function getSubObjects()
	{
		global $rbacsystem,$rbacadmin;

		$data = array();

		// show only objects with permission 'create'
		$objects = $rbacadmin->getModules($this->type,$this->id);

		foreach ($objects as $key => $object)
		{
			if ($rbacsystem->checkAccess("create", $this->id, $key))
			{
				$data[$key] = $object;
			} //if
		} //foreach
		return $data;
	}
} // class
?>
