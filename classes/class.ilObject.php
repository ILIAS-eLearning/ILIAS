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


/**
* Class ilObject
* Basic functions for all objects
*
* @author Stefan Meyer <smeyer@databay.de>
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package ilias-core
*/
class ilObject
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
	* object_data record
	*/
	var $obj_data_record;


	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObject($a_id = 0, $a_reference = true)
	{
		global $ilias, $lng;

		if (DEBUG)
		{
			echo "<br/><font color=\"red\">type(".$this->type.") id(".$a_id.") referenced(".$a_reference.")</font>";
		}

		$this->ilias =& $ilias;
		$this->lng =& $lng;

		$this->max_title = MAXLENGTH_OBJ_TITLE;
		$this->max_desc = MAXLENGTH_OBJ_DESC;
		$this->add_dots = true;

		$this->referenced = $a_reference;

		if ($a_id == 0)
		{
			$this->referenced = false;		// newly created objects are never referenced
		}									// they will get referenced if createReference() is called

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
	* @param	boolean
	* @access	public
	*/
	function read($a_force_db = false)
	{
		global $log;

		if (isset($this->obj_data_record) && !$a_force_db)
		{
			$obj = $this->obj_data_record;
		}
		else if ($this->referenced)
		{
			// check reference id
			if (!isset($this->ref_id))
			{
				$message = "ilObject::read(): No ref_id given!";
				$log->writeWarning($message);
				$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
			}

			// read object data
			$q = "SELECT * FROM object_data ".
				 "LEFT JOIN object_reference ON object_data.obj_id=object_reference.obj_id ".
				 "WHERE object_reference.ref_id='".$this->ref_id."'";
			$object_set = $this->ilias->db->query($q);

			// check number of records
			if ($object_set->numRows() == 0)
			{
				$message = "ilObject::read(): Object with ref_id ".$this->ref_id." not found!";
				$log->writeWarning($message);
				$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
			}

			$obj = $object_set->fetchRow(DB_FETCHMODE_ASSOC);
		}
		else
		{
			// check object id
			if (!isset($this->id))
			{
				$message = "ilObject::read(): No obj_id given!";
				$log->writeWarning($message);
				$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
			}

			// read object data
			$q = "SELECT * FROM object_data ".
				 "WHERE obj_id = '".$this->id."'";
			$object_set = $this->ilias->db->query($q);

			// check number of records
			if ($object_set->numRows() == 0)
			{
				$message = "ilObject::read(): Object with obj_id: ".$this->id." not found!";
				$log->writeWarning($message);
				$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
			}

			$obj = $object_set->fetchRow(DB_FETCHMODE_ASSOC);
		}

		$this->id = $obj["obj_id"];
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
	* @return	integer	object id
	*/
	function getId()
	{
		return $this->id;
	}


	/**
	* set object id
	* @access	public
	* @param	integer	$a_id		object id
	*/
	function setId($a_id)
	{
		$this->id = $a_id;
	}


	/**
	* set reference id
	* @access	public
	* @param	integer	$a_id		reference id
	*/
	function setRefId($a_id)
	{
		$this->ref_id = $a_id;
		$this->referenced = true;
	}


	/**
	* get reference id
	* @access	public
	* @return	integer	reference id
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
	* @param	integer	$a_type		object type
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
	*
	* @access	public
	* @param	string		$a_title		object title
	*/
	function setTitle($a_title)
	{
		$this->title = addslashes(ilUtil::shortenText($a_title, $this->max_title, $this->add_dots));
	}


	/**
	* get object description
	*
	* @access	public
	* @return	string		object description
	*/
	function getDescription()
	{
		return $this->desc;
	}


	/**
	* set object description
	*
	* @access	public
	* @param	string		$a_desc		object description
	*/
	function setDescription($a_desc)
	{
		$this->desc = addslashes(ilUtil::shortenText($a_desc, $this->max_desc, $this->add_dots));
	}

	/**
	* get object owner
	*
	* @access	public
	* @return	integer	owner id
	*/
	function getOwner()
	{
		return $this->owner;
	}


	/*
	* get full name of object owner
	*
	* @access	public
	* @return	string	owner name or unknown
	*/
	function getOwnerName()
	{
		if ($this->getOwner() != -1)
		{
			$owner = new ilObjUser($this->getOwner());
		}

		if (is_object($owner))
		{
			$own_name = $owner->getFullname();
		}
		else
		{
			$own_name = $this->lng->txt("unknown");
		}

		return $own_name;
	}


	/**
	* set object owner
	*
	* @access	public
	* @param	integer	$a_owner	owner id
	*/
	function setOwner($a_owner)
	{
		$this->owner = $a_owner;
	}


	/**
	* get create date
	* @access	public
	* @return	string		creation date
	*/
	function getCreateDate()
	{
		return $this->create_date;
	}


	/**
	* get last update date
	* @access	public
	* @return	string		date of last update
	*/
	function getLastUpdateDate()
	{
		return $this->last_update;
	}


	/**
	* set object_data record (note: this method should
	* only be called from the ilObjectFactory class)
	*
	* @param	array	$a_record	assoc. array from table object_data
	* @access	public
	* @return	integer	object id
	*/
	function setObjDataRecord($a_record)
	{
		$this->obj_data_record = $a_record;
	}


	/**
	* create
	*
	* note: title, description and type should be set when this function is called
	*
	* @access	public
	* @return	integer		object id
	*/
	function create()
	{
		global $log;

		if (!isset($this->type))
		{
			$message = get_class($this)."::create(): No object type given!";
			$log->writeWarning($message);
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		// we must use getTitle(), because the title may be stored in a
		// assigned meta object, not in $this->title
		if ($this->getTitle() == "")
		{
			$message = get_class($this)."::create(): No title given! A title is required!";
			$log->writeWarning($message);
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$this->title = addslashes(ilUtil::shortenText($this->getTitle(), $this->max_title, $this->add_dots));
		$this->desc = addslashes(ilUtil::shortenText($this->getDescription(), $this->max_desc, $this->add_dots));

		$q = "INSERT INTO object_data ".
			 "(type,title,description,owner,create_date,last_update) ".
			 "VALUES ".
			 "('".$this->type."','".$this->getTitle()."','".$this->getDescription()."',".
			 "'".$this->ilias->account->getId()."',now(),now())";
		$this->ilias->db->query($q);

		$this->id = getLastInsertId();

		// the line ($this->read();) messes up meta data handling: meta data,
		// that is not saved at this time, gets lost, so we query for the dates alone
		//$this->read();
		$q = "SELECT last_update, create_date FROM object_data".
			 " WHERE obj_id = '".$this->id."'";
		$obj_set = $this->ilias->db->query($q);
		$obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);
		$this->last_update = $obj_rec["last_update"];
		$this->create_date = $obj_rec["create_date"];
		
		// set owner for new objects
		$this->setOwner($this->ilias->account->getId());

		return $this->id;
	}

	/**
	* update object in db
	*
	* @access	public
	* @return	boolean	true on success
	*/
	function update()
	{
		$q = "UPDATE object_data ".
			"SET ".
			"title = '".$this->getTitle()."',".
			"description = '".$this->getDescription()."', ".
			"last_update = now() ".
			"WHERE obj_id = '".$this->getId()."'";
		$this->ilias->db->query($q);

		// the line ($this->read();) messes up meta data handling: meta data,
		// that is not saved at this time, gets lost, so we query for the dates alone
		//$this->read();
		$q = "SELECT last_update FROM object_data".
			 " WHERE obj_id = '".$this->getId()."'";
		$obj_set = $this->ilias->db->query($q);
		$obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);
		$this->last_update = $obj_rec["last_update"];

		return true;
	}


	/**
	* maybe this method should be in tree object!?
	*
	* @todo	role/rbac stuff
	*/
	function putInTree($a_parent_ref)
	{
		global $tree;
		
		$tree->insertNode($this->getRefId(), $a_parent_ref);
	}

	/**
	* set permissions of object
	*
	* @param	integer	reference_id of parent object
	* @access	public
	*/
	function setPermissions($a_parent_ref)
	{
		global $rbacadmin, $rbacreview;
		
		$parentRoles = $rbacreview->getParentRoleIds($a_parent_ref);

		foreach ($parentRoles as $parRol)
		{
			$ops = $rbacreview->getOperationsOfRole($parRol["obj_id"], $this->getType(), $parRol["parent"]);
			$rbacadmin->grantPermission($parRol["obj_id"], $ops, $this->getRefId());
		}
	}
		

	/**
	* creates reference for object
	*
	* @access	public
	* @return	integer	reference_id of object
	*/
	function createReference()
	{
		global $log;

		if (!isset($this->id))
		{
			$message = "ilObject::createNewReference(): No obj_id given!";
			$log->writeWarning($message);
			$this->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$q = "INSERT INTO object_reference ".
			 "(obj_id) VALUES ('".$this->id."')";
		$this->ilias->db->query($q);

		$this->ref_id = getLastInsertId();

		return $this->ref_id;
	}


	/**
	* count references of object
	*
	* @access	public
	* @return	integer		number of references for this object
	*/
	function countReferences()
	{
		global $log;

		if (!isset($this->obj_id))
		{
			$message = "ilObject::countReferences(): No obj_id given!";
			$log->writeWarning($message);
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$q = "SELECT COUNT(ref_id) AS num FROM object_reference ".
		 	"WHERE obj_id = '".$this->obj_id."'";
		$row = $this->ilias->db->getRow($q);

		return $row->num;
	}


	/**
	* clone object into tree
	* basic clone function. Register new object in object_data, creates reference and
	* insert reference ID in tree. All object specific data must be copied in the clone function of the appropriate object class.
	* Look in ilObjForum::clone() for example code
	* 
	* @access	public
	* @param	integer		$a_parent_ref		ref id of parent object
	* @return	integer		new ref id
	*/
	function clone($a_parent_ref)
	{
		global $rbacadmin, $rbacreview;

		$new_obj = new ilObject();
		$new_obj->setTitle($this->getTitle());
		$new_obj->setType($this->getType());
		$new_obj->setDescription($this->getDescription());
		$new_obj->create();
		$new_ref_id = $new_obj->createReference();
		$new_obj->putInTree($a_parent_ref);
		$new_obj->setPermissions($a_parent_ref);
		unset($new_obj);

		return $new_ref_id;
	}


	/**
	* delete object or referenced object
	* (in the case of a referenced object, object data is only deleted
	* if last reference is deleted)
	*
 	* @access	public
	* @return	boolean	true when successfully deleted
	*/
	function delete()
	{
		global $rbacadmin;

		// ALL OBJECT ENTRIES IN TREE HAVE BEEN DELETED FROM CLASS ADMIN.PHP

		// delete object_data entry
		if ((!$this->referenced) || ($this->countReferences() == 1))
		{
			// delete entry in object_data
			$q = "DELETE FROM object_data ".
				"WHERE obj_id = '".$this->getId()."'";
			$this->ilias->db->query($q);
		}
		
		// delete object_reference entry
		if ($this->referenced)
		{
			// delete entry in object_reference
			$q = "DELETE FROM object_reference ".
				"WHERE ref_id = '".$this->getRefId()."'";
			$this->ilias->db->query($q);

			// DELETE PERMISSION ENTRIES IN RBAC_PA
			// DONE: method overwritten in ilObjRole & ilObjUser.
			//this call only applies for objects in rbac (not usr,role,rolt)
			// TODO: Do this for role templates too
			$rbacadmin->revokePermission($this->getRefId());
		}

		return true;
	}

	/**
	* DESC MISSING
	* @access	public
	* @return	array
	*/
	function getSubObjects()
	{
		global $rbacsystem, $rbacadmin, $rbacreview;

		$data = array();

		// show only objects with permission 'create'
		$objects = $rbacreview->getModules($this->type,$this->id);

		foreach ($objects as $key => $object)
		{
			if ($rbacsystem->checkAccess("create", $this->id, $key))
			{
				$data[$key] = $object;
			} //if
		} //foreach

		return $data;
	}
} // END class.ilObject
?>
