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
* ILIAS Data Validator & Recovery Tool
*
* @author	Sascha Hofmann <shofmann@databay.de> 
* @version	$Id$
*
* @package	ilias-tools
*/
class ilValidator extends PEAR
{
	/**
	* all valid rbac object types
	* @var	string
	*/
	var $rbac_object_types = NULL;

	/**
	* list of object types to exclude from recovering
	* @var	array
	*/
	var $object_types_exclude = array("adm","root","mail","usrf","objf","lngf","trac","taxf","auth","rolf","file", "assf");
	
	/**
	* set mode
	* @var	array
	*/
	var $mode = array(
						"analyze"		=> true,		// gather information about corrupted entries
						"check_tree"	=> false,		// check tree consistence
						"clean" 		=> false,		// remove all unusable entries & renumber tree
						"restore"		=> false,		// restore objects with invalid parent to RecoveryFolder
						"purge"			=> false,		// delete all objects with invalid parent from system
						"restore_trash"	=> false,		// restore all objects in trash to RecoveryFolder
						"purge_trash"	=> false		// delete all objects in trash from system
					);

	/**
	* invalid references
	* @var	array
	*/
	var $invalid_references = array();

	/**
	* invalid childs (tree entries)
	* @var	array
	*/
	var $invalid_childs = array();

	/**
	* missing objects
	* @var	array
	*/
	var $missing_objects = array();

	/**
	* unbound objects
	* @var	array
	*/
	var $unbound_objects = array();

	/**
	* objects in trash
	* @var	array
	*/
	var $deleted_objects = array();

	/**
	* contains missing objects that are rolefolders. found by this::
	* findMissingObjects()' these rolefolders must be removed before any
	* restore operations
	* @var	array
	*/
	var $invalid_rolefolders = array();
	
	/**
	* contains correct registrated objects but data are corrupted (experimental)
	* @var	array
	*/
	var $invalid_objects = array();
	
	/**
	* true enables scan log
	* @var	boolean
	*/
	var $logging = false;
	
	/**
	* contains ilLog object
	* @var	object
	*/
	var $scan_log;
	
	var $scan_log_file = "scanlog.log";
	
	var $scan_log_separator = "<!-- scan log start -->";

	/**
	* Constructor
	* 
	* @access	public
	* @param	integer	mode
	*/
	function ilValidator($a_log = false)
	{
		global $objDefinition, $ilDB;
		
		$this->PEAR();
		$this->db =& $ilDB;
		$this->rbac_object_types = "'".implode("','",$objDefinition->getAllRBACObjects())."'";
        $this->setErrorHandling(PEAR_ERROR_CALLBACK,array(&$this, 'handleErr'));
		
		if ($a_log === true)
		{
			$this->logging = true;

			// should be available thru inc.header.php
			// TODO: move log functionality to new class ilScanLog
			include_once "classes/class.ilLog.php";
		
			// create scan log
			$this->scan_log = new ilLog(CLIENT_DATA_DIR,"scanlog.log");
			$this->scan_log->setLogFormat("");
			$this->writeScanLogLine($this->scan_log_separator);
			$this->writeScanLogLine("\n[Systemscan from ".date("y-m-d H:i]"));
		}
	}

	/**
	* set mode of ilValidator
	* Usage: setMode("restore",true)	=> enable object restorey
	* 		 setMode("all",true) 		=> enable all features
	* 		 For all possible modes see variables declaration
	*
	* @access	public
	* @param	string	mode
	* @param	boolean	value (true=enable/false=disable)
	* @return	boolean	false on error
	*/
	function setMode($a_mode,$a_value)
	{
		if ((!in_array($a_mode,array_keys($this->mode)) and $a_mode != "all") or !is_bool($a_value))
		{
			$this->throwError(INVALID_PARAM, FATAL, DEBUG);
			return false;
		}
		
		if ($a_mode == "all")
		{
			foreach ($this->mode as $mode => $value)
			{
				$this->mode[$mode] = $a_value;
			}
		}
		else
		{
			$this->mode[$a_mode] = $a_value;
		}
		
		// consider mode dependencies
		$this->setModeDependencies();

		return true;
	}
	
	/**
	* Is a particular mode enabled?
	*
	* @access	public
	* @param	string	mode to query
	* @return	boolean
	* @see		this::setMode()
	*/
	function isModeEnabled($a_mode)
	{
		if (!in_array($a_mode,array_keys($this->mode)))
		{
			$this->throwError(VALIDATER_UNKNOWN_MODE, WARNING, DEBUG);
			return false;
		}
		
		return $this->mode[$a_mode];
	}
	
	function isLogEnabled()
	{
		return $this->logging;
	}
	
	/**
	* Sets modes by considering mode dependencies;
	* some modes require other modes to be activated.
	* This functions set all modes that are required according to the current setting.
	* 
	* @access	private
	* @see		this::setMode()
	*/
	function setModeDependencies()
	{
		// DO NOT change the order
		
		if ($this->mode["restore"] === true)
		{
			$this->mode["clean"] = true;
			$this->mode["purge"] = false;
		}

		if ($this->mode["purge"] === true)
		{
			$this->mode["clean"] = true;
			$this->mode["restore"] = false;
		}

		if ($this->mode["restore_trash"] === true)
		{
			$this->mode["clean"] = true;
			$this->mode["purge_trash"] = false;
		}

		if ($this->mode["purge_trash"] === true)
		{
			$this->mode["clean"] = true;
			$this->mode["restore_trash"] = false;
		}

		if ($this->mode["clean"] === true)
		{
			$this->mode["analyze"] = true;
		}
	}

	/**
	* Search database for all object entries with missing reference and/or tree entry
	* and stores result in $this->missing_objects
	* 
	* @access	public
	* @return	boolean	false if analyze mode disabled or nothing found
	* @see		this::getMissingObjects()
	* @see		this::restoreMissingObjects()
	*/
	function findMissingObjects()
	{
		// check mode: analyze
		if ($this->mode["analyze"] !== true)
		{
			return false;
		}
		
		// init
		$this->missing_objects = array();
	
		$this->writeScanLogLine("\nfindMissingObjects:");
		
		$q = "SELECT object_data.*, ref_id FROM object_data ".
			 "LEFT JOIN object_reference ON object_data.obj_id = object_reference.obj_id ".
			 "LEFT JOIN tree ON object_reference.ref_id = tree.child ".
			 "WHERE (object_reference.obj_id IS NULL OR tree.child IS NULL) ".
			 "AND object_data.type IN (".$this->rbac_object_types.")";
		$r = $this->db->query($q);
		
		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if (!in_array($row->type,$this->object_types_exclude))
			{
				$this->missing_objects[] = array(
													"obj_id"		=> $row->obj_id,
													"type"			=> $row->type,
													"ref_id"		=> $row->ref_id,
													"child"			=> $row->child,
													"title"			=> $row->title,
													"desc"			=> $row->description,
													"owner"			=> $row->owner,
													"create_date"	=> $row->create_date,
													"last_update"	=> $row->last_update
												);
			}
		}
		
		if (count($this->missing_objects) > 0)
		{
			$this->writeScanLogLine("obj_id\ttype\tref_id\tchild\ttitle\tdesc\towner\tcreate_date\tlast_update");
			$this->writeScanLogArray($this->missing_objects);
			return true;
		}

		$this->writeScanLogLine("none");
		return false;	
	}

	/**
	* Search database for all rolefolder object entries with missing reference
	* entry. Furthermore gets all rolefolders that are placed accidently in
	* RECOVERY_FOLDER from earlier versions of System check.
	* Result is stored in $this->invalid_rolefolders
	* 
	* @access	public
	* @return	boolean	false if analyze mode disabled or nothing found
	* @see		this::getInvalidRolefolders()
	* @see		this::removeInvalidRolefolders()
	*/
	function findInvalidRolefolders()
	{
		// check mode: analyze
		if ($this->mode["analyze"] !== true)
		{
			return false;
		}
		
		// init
		$this->invalid_rolefolders = array();
		
		$this->writeScanLogLine("\nfindInvalidRolefolders:");

		// find rolfs without reference/tree entry
		$q = "SELECT object_data.*, ref_id FROM object_data ".
			 "LEFT JOIN object_reference ON object_data.obj_id = object_reference.obj_id ".
			 "LEFT JOIN tree ON object_reference.ref_id = tree.child ".
			 "WHERE (object_reference.obj_id IS NULL OR tree.child IS NULL) ".
			 "AND object_data.type='rolf'";
		$r = $this->db->query($q);
		
		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->invalid_rolefolders[] = array(
												"obj_id"		=> $row->obj_id,
												"type"			=> $row->type,
												"ref_id"		=> $row->ref_id,
												"child"			=> $row->child,
												"title"			=> $row->title,
												"desc"			=> $row->description,
												"owner"			=> $row->owner,
												"create_date"	=> $row->create_date,
												"last_update"	=> $row->last_update
											);
		}
		
		// find rolfs within RECOVERY FOLDER
		$q = "SELECT object_data.*, ref_id FROM object_data ".
			 "LEFT JOIN object_reference ON object_data.obj_id = object_reference.obj_id ".
			 "LEFT JOIN tree ON object_reference.ref_id = tree.child ".
			 "WHERE object_reference.ref_id ='".RECOVERY_FOLDER_ID."' ".
			 "AND object_data.type='rolf'";
		$r = $this->db->query($q);
		
		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->invalid_rolefolders[] = array(
												"obj_id"		=> $row->obj_id,
												"type"			=> $row->type,
												"ref_id"		=> $row->ref_id,
												"child"			=> $row->child,
												"title"			=> $row->title,
												"desc"			=> $row->description,
												"owner"			=> $row->owner,
												"create_date"	=> $row->create_date,
												"last_update"	=> $row->last_update
											);
		}
			
		if (count($this->invalid_rolefolders) > 0)
		{
			$this->writeScanLogLine("obj_id\ttype\tref_id\tchild\ttitle\tdesc\towner\tcreate_date\tlast_update");
			$this->writeScanLogArray($this->invalid_rolefolders);
			return true;
		}

		$this->writeScanLogLine("none");
		return false;	
	}

	/**
	* Search database for all role entries that are linked to invalid
	* ref_ids
	* 
	* @access	public
	* @return	boolean	false if analyze mode disabled or nothing found
	* @see		this::getInvalidRBACEntries()
	* @see		this::removeInvalidRBACEntries()
	*/
	function findInvalidRBACEntries()
	{
		// check mode: analyze
		if ($this->mode["analyze"] !== true)
		{
			return false;
		}
		
		// init
		$this->invalid_rbac_entries = array();
		
		$this->writeScanLogLine("\nfindInvalidRBACEntries:");

		$q = "SELECT object_data.*, ref_id FROM object_data ".
			 "LEFT JOIN object_reference ON object_data.obj_id = object_reference.obj_id ".
			 "LEFT JOIN tree ON object_reference.ref_id = tree.child ".
			 "WHERE (object_reference.obj_id IS NULL OR tree.child IS NULL) ".
			 "AND object_data.type='rolf'";
		$r = $this->db->query($q);
		
		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->invalid_rolefolders[] = array(
												"obj_id"		=> $row->obj_id,
												"type"			=> $row->type,
												"ref_id"		=> $row->ref_id,
												"child"			=> $row->child,
												"title"			=> $row->title,
												"desc"			=> $row->description,
												"owner"			=> $row->owner,
												"create_date"	=> $row->create_date,
												"last_update"	=> $row->last_update
											);
		}
		
		// find rolfs within RECOVERY FOLDER
		$q = "SELECT object_data.*, ref_id FROM object_data ".
			 "LEFT JOIN object_reference ON object_data.obj_id = object_reference.obj_id ".
			 "LEFT JOIN tree ON object_reference.ref_id = tree.child ".
			 "WHERE object_reference.ref_id ='".RECOVERY_FOLDER_ID."' ".
			 "AND object_data.type='rolf'";
		$r = $this->db->query($q);
		
		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->invalid_rolefolders[] = array(
												"obj_id"		=> $row->obj_id,
												"type"			=> $row->type,
												"ref_id"		=> $row->ref_id,
												"child"			=> $row->child,
												"title"			=> $row->title,
												"desc"			=> $row->description,
												"owner"			=> $row->owner,
												"create_date"	=> $row->create_date,
												"last_update"	=> $row->last_update
											);
		}
			
		if (count($this->invalid_rolefolders) > 0)
		{
			$this->writeScanLogLine("obj_id\ttype\tref_id\tchild\ttitle\tdesc\towner\tcreate_date\tlast_update");
			$this->writeScanLogArray($this->invalid_rolefolders);
			return true;
		}

		$this->writeScanLogLine("none");
		return false;	
	}

	/**
	* Gets all object entries with missing reference and/or tree entry.
	* Returns array with
	*		obj_id		=> actual object entry with missing reference or tree
	*		type		=> symbolic name of object type
	*		ref_id		=> reference entry of object (or NULL if missing)
	* 		child		=> always NULL (only for debugging and verification)
	* 
	* @access	public
	* @return	array
	* @see		this::findMissingObjects()
	* @see		this::restoreMissingObjects()
	*/
	function getMissingObjects()
	{
		return $this->missing_objects;
	}

	/**
	* Search database for all reference entries that are not linked with a valid object id
	* and stores result in $this->invalid_references
	* 
	* @access	public
	* @return	boolean	false if analyze mode disabled or nothing found
	* @see		this::getInvalidReferences()
	* @see		this::removeInvalidReferences()
 	*/	
	function findInvalidReferences()
	{
		// check mode: analyze
		if ($this->mode["analyze"] !== true)
		{
			return false;
		}

		// init
		$this->invalid_references = array();
		
		$this->writeScanLogLine("\nfindInvalidReferences:");

		$q = "SELECT object_reference.* FROM object_reference ".
			 "LEFT JOIN object_data ON object_data.obj_id = object_reference.obj_id ".
			 "WHERE object_data.obj_id IS NULL ".
			 "OR object_data.type NOT IN (".$this->rbac_object_types.")";
		$r = $this->db->query($q);
		
		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->invalid_references[] = array(
											"ref_id"	=> $row->ref_id,
											"obj_id"	=> $row->obj_id,
											"msg"		=> "Object does not exist."
											);
		}

		if (count($this->invalid_references) > 0)
		{
			$this->writeScanLogLine("ref_id\t\tobj_id");
			$this->writeScanLogArray($this->invalid_references);
			return true;
		}

		$this->writeScanLogLine("none");
		return false;	
	}

	/**
	* Gets all reference entries that are not linked with a valid object id.
	* 
	* @access	public
	* @return	array
	* @see		this::findInvalidReferences()
	* @see		this::removeInvalidReferences()
	*/	
	function getInvalidReferences()
	{
		return $this->invalid_references;
	}

	/**
	* Search database for all tree entries without any link to a valid object
	* and stores result in $this->invalid_childs
	* 
	* @access	public
	* @return	boolean	false if analyze mode disabled or nothing found
	* @see		this::getInvalidChilds()
	* @see		this::removeInvalidChilds()
	*/
	function findInvalidChilds()
	{
		// check mode: analyze
		if ($this->mode["analyze"] !== true)
		{
			return false;
		}

		// init
		$this->invalid_childs = array();

		$this->writeScanLogLine("\nfindInvalidChilds:");

		$q = "SELECT tree.*,object_reference.ref_id FROM tree ".
			 "LEFT JOIN object_reference ON tree.child = object_reference.ref_id ".
			 "LEFT JOIN object_data ON object_reference.obj_id = object_data.obj_id ".
			 "WHERE object_reference.ref_id IS NULL or object_data.obj_id IS NULL";
		$r = $this->db->query($q);
		
		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->invalid_childs[] = array(
											"child"		=> $row->child,
											"ref_id"	=> $row->ref_id,
											"msg"		=> "No object found"
											);
		}

		if (count($this->invalid_childs) > 0)
		{
			$this->writeScanLogLine("child\t\tref_id");
			$this->writeScanLogArray($this->invalid_childs);
			return true;
		}

		$this->writeScanLogLine("none");
		return false;
	}

	/**
	* Gets all tree entries without any link to a valid object
	* 
	* @access	public
	* @return	array
	* @see		this::findInvalidChilds()
	* @see		this::removeInvalidChilds()
	*/
	function getInvalidChilds()
	{
		return $this->invalid_childs;
	}

	/**
	* Search database for all tree entries having no valid parent (=> no valid path to root node)
	* and stores result in $this->unbound_objects
	* Result does not contain childs that are marked as deleted! Deleted childs
	* have a negative number.
	*
	* @access	public
	* @return	boolean	false if analyze mode disabled or nothing found
	* @see		this::getUnboundObjects()
	* @see		this::restoreUnboundObjects()
	*/
	function findUnboundObjects()
	{
		// check mode: analyze
		if ($this->mode["analyze"] !== true)
		{
			return false;
		}

		// init
		$this->unbound_objects = array();

		$this->writeScanLogLine("\nfindUnboundObjects:");

		$q = "SELECT T2.tree AS deleted,T1.child,T1.parent,T2.parent AS grandparent FROM tree AS T1 ".
			 "LEFT JOIN tree AS T2 ON T2.child=T1.parent ".
			 "WHERE (T2.tree!=1 OR T2.tree IS NULL) AND T1.parent!=0";
		$r = $this->db->query($q);
		
		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			// exclude deleted nodes
			if ($row->deleted === NULL)
			{
				$this->unbound_objects[] = array(
												"child"			=> $row->child,
												"parent"		=> $row->parent,
												"tree"			=> 1,
												"msg"			=> "No valid parent node found"
												);
			}
		}

		if (count($this->unbound_objects) > 0)
		{
			$this->writeScanLogLine("child\t\tparent\ttree");
			$this->writeScanLogArray($this->unbound_objects);
			return true;
		}

		$this->writeScanLogLine("none");
		return false;
	}

	/**
	* Search database for all tree entries having no valid parent (=> no valid path to root node)
	* and stores result in $this->unbound_objects
	* Result also contains childs that are marked as deleted! Deleted childs has
	* a negative number in ["deleted"] otherwise NULL.
	*
	* @access	public
	* @return	boolean	false if analyze mode disabled or nothing found
	* @see		this::getUnboundObjects()
	* @see		this::restoreUnboundObjects()
	*/
	function findDeletedObjects()
	{
		// check mode: analyze
		if ($this->mode["analyze"] !== true)
		{
			return false;
		}

		// init
		$this->deleted_objects = array();

		$this->writeScanLogLine("\nfindDeletedObjects:");

		$q = "SELECT object_data.*,tree.tree,tree.child,tree.parent FROM object_data ".
			 "LEFT JOIN object_reference ON object_data.obj_id=object_reference.obj_id ".
			 "LEFT JOIN tree ON tree.child=object_reference.ref_id ".
			 " WHERE tree !=1";
		$r = $this->db->query($q);
		
		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->deleted_objects[] = array(
											"child"			=> $row->child,
											"parent"		=> $row->parent,
											"tree"			=> $row->tree,
											"type"			=> $row->type,
											"title"			=> $row->title,
											"desc"			=> $row->description,
											"owner"			=> $row->owner,
											"create_date"	=> $row->create_date,
											"last_update"	=> $row->last_update
											);
		}

		if (count($this->deleted_objects) > 0)
		{
			$this->writeScanLogLine("obj_id\tref_id\ttree\ttype\ttitle\tdesc\towner\tcreate_date\tlast_update");
			$this->writeScanLogArray($this->deleted_objects);
			return true;
		}

		$this->writeScanLogLine("none");
		return false;
	}
	

	/**
	* Gets all tree entries having no valid parent (=> no valid path to root node)
	* Returns an array with
	*		child		=> actual entry with broken uplink to its parent
	*		parent		=> parent of child that does not exist
	*		grandparent	=> grandparent of child (where path to root node continues)
	* 		deleted		=> containing a negative number (= parent in trash) or NULL (parent does not exist at all)
	* 
	* @access	public
	* @return	array
	* @see		this::findUnboundObjects()
	* @see		this::restoreUnboundObjects()
	*/
	function getUnboundObjects()
	{
		return $this->unbound_objects;
	}

	/**
	* Gets all object in trash
	* 
	* @access	public
	* @return	array	objects in trash
	*/
	function getDeletedObjects()
	{
		return $this->deleted_objects;
	}
	
	/**
	* Gets invalid rolefolders (same as missing objects)
	* 
	* @access	public
	* @return	array
	* @see		this::findMissingObjects()
	* @see		this::removeInvalidRolefolders()
	*/
	function getInvalidRolefolders()
	{
		return $this->invalid_rolefolders;
	}

	/**
	* Removes all reference entries that are linked with invalid object IDs
	* 
	* @access	public
	* @param	array	invalid IDs in object_reference (optional)
	* @return	boolean	true if any ID were removed / false on error or clean mode disabled
	* @see		this::getInvalidReferences()
	* @see		this::findInvalidReferences()
	*/
	function removeInvalidReferences($a_invalid_refs = NULL)
	{
		global $ilLog;

		// check mode: clean
		if ($this->mode["clean"] !== true)
		{
			return false;
		}

		$this->writeScanLogLine("\nremoveInvalidReferences:");

		if ($a_invalid_refs === NULL and isset($this->invalid_references))
		{
			$a_invalid_refs =& $this->invalid_references; 
		}

		// handle wrong input
		if (!is_array($a_invalid_refs))
		{
			$this->throwError(INVALID_PARAM, WARNING, DEBUG);
			return false;
		}
		// no unbound references found. do nothing
		if (count($a_invalid_refs) == 0)
		{
			$this->writeScanLogLine("none");
			return false;
		}

/*******************
removal starts here
********************/

		$message = sprintf('%s::removeInvalidReferences(): Started...',
						   get_class($this));
		$ilLog->write($message,$ilLog->WARNING);

		foreach ($a_invalid_refs as $entry)
		{
			$q = "DELETE FROM object_reference WHERE ref_id='".$entry["ref_id"]."' AND obj_id='".$entry["obj_id"]."'";
			$this->db->query($q);

			$message = sprintf('%s::removeInvalidReferences(): Reference %s removed',
							   get_class($this),
							   $entry["ref_id"]);
			$ilLog->write($message,$ilLog->WARNING);
			
			$this->writeScanLogLine("Entry ".$entry["ref_id"]." removed");
		}
		
		return true;	
	}

	/**
	* Removes all tree entries without any link to a valid object
	* 
	* @access	public
	* @param	array	invalid IDs in tree (optional)
	* @return	boolean	true if any ID were removed / false on error or clean mode disabled
	* @see		this::getInvalidChilds()
	* @see		this::findInvalidChilds()
	*/
	function removeInvalidChilds($a_invalid_childs = NULL)
	{
		global $ilLog;

		// check mode: clean
		if ($this->mode["clean"] !== true)
		{
			return false;
		}

		$this->writeScanLogLine("\nremoveInvalidChilds:");

		if ($a_invalid_childs === NULL and isset($this->invalid_childs))
		{
			$a_invalid_childs =& $this->invalid_childs; 
		}

		// handle wrong input
		if (!is_array($a_invalid_childs))
		{
			$this->throwError(INVALID_PARAM, WARNING, DEBUG);
			return false;
		}

		// no unbound childs found. do nothing
		if (count($a_invalid_childs) == 0)
		{
			$this->writeScanLogLine("none");
			return false;
		}

/*******************
removal starts here
********************/

		$message = sprintf('%s::removeInvalidChilds(): Started...',
						   get_class($this));
		$ilLog->write($message,$ilLog->WARNING);

		foreach ($a_invalid_childs as $entry)
		{
			$q = "DELETE FROM tree WHERE child='".$entry["child"]."'";
			$this->db->query($q);

			$message = sprintf('%s::removeInvalidChilds(): Entry child=%s removed',
							   get_class($this),
							   $entry["child"]);
			$ilLog->write($message,$ilLog->WARNING);
				
			$this->writeScanLogLine("Entry ".$entry["child"]." removed");
		}
		
		return true;	
	}

	/**
	* Removes invalid rolefolders
	* 
	* @access	public
	* @param	array	obj_ids of rolefolder objects (optional)
	* @return	boolean	true if any object were removed / false on error or
	* remove mode disabled
	* @see		this::getInvalidRolefolders()
	* @see		this::findMissingObjects()
	*/
	function removeInvalidRolefolders($a_invalid_rolefolders = NULL)
	{
		global $ilias,$ilLog;
		
		// check mode: clean
		if ($this->mode["clean"] !== true)
		{
			return false;
		}

		$this->writeScanLogLine("\nremoveInvalidRolefolders:");

		if ($a_invalid_rolefolders === NULL and isset($this->invalid_rolefolders))
		{
			$a_invalid_rolefolders = $this->invalid_rolefolders;
		}

		// handle wrong input
		if (!is_array($a_invalid_rolefolders)) 
		{
			$this->throwError(INVALID_PARAM, WARNING, DEBUG);
			return false;
		}

		// no invalid rolefolders found. do nothing
		if (count($a_invalid_rolefolders) == 0)
		{
			$this->writeScanLogLine("none");
			return false;
		}
		
/*******************
removal starts here
********************/

		$removed = false;
		
		$message = sprintf('%s::removeInvalidRolefolders(): Started...',
						   get_class($this));
		$ilLog->write($message,$ilLog->WARNING);
		
		foreach ($a_invalid_rolefolders as $rolf)
		{
			// restore ref_id in case of missing
			if ($rolf["ref_id"] === NULL)
			{
				$rolf["ref_id"] = $this->restoreReference($rolf["obj_id"]);

				$this->writeScanLogLine("Created missing reference '".$rolf["ref_id"]."' for rolefolder object '".$rolf["obj_id"]."'");
			}

			// now delete rolefolder
			$obj_data =& $ilias->obj_factory->getInstanceByRefId($rolf["ref_id"]);
			$obj_data->delete();
			unset($obj_data);
			$removed = true;
			$this->writeScanLogLine("Removed invalid rolefolder '".$rolf["title"]."' (id=".$rolf["obj_id"].",ref=".$rolf["ref_id"].") from system");
		}
		
		return $removed;
	}

	/**
	* Restores missing reference and/or tree entry for all objects found by this::getMissingObjects()
	* Restored object are placed in RecoveryFolder
	* 
	* @access	public
	* @param	array	obj_ids of missing objects (optional)
	* @return	boolean	true if any object were restored / false on error or restore mode disabled
	* @see		this::getMissingObjects()
	* @see		this::findMissingObjects()
	*/
	function restoreMissingObjects($a_missing_objects = NULL)
	{
		global $ilias,$rbacadmin,$ilLog;
		
		// check mode: restore
		if ($this->mode["restore"] !== true)
		{
			return false;
		}

		$this->writeScanLogLine("\nrestoreMissingObjects:");

		if ($a_missing_objects === NULL and isset($this->missing_objects))
		{
			$a_missing_objects = $this->missing_objects;
		}

		// handle wrong input
		if (!is_array($a_missing_objects)) 
		{
			$this->throwError(INVALID_PARAM, WARNING, DEBUG);
			return false;
		}

		// no missing objects found. do nothing
		if (count($a_missing_objects) == 0)
		{
			$this->writeScanLogLine("none");
			return false;
		}
		
/*******************
restore starts here
********************/

		$restored = false;
		
		$message = sprintf('%s::restoreMissingObjects(): Started...',
						   get_class($this));
		$ilLog->write($message,$ilLog->WARNING);
		
		foreach ($a_missing_objects as $missing_obj)
		{
			// restore ref_id in case of missing
			if ($missing_obj["ref_id"] === NULL)
			{
				$missing_obj["ref_id"] = $this->restoreReference($missing_obj["obj_id"]);

				$this->writeScanLogLine("Created missing reference '".$missing_obj["ref_id"]."' for object '".$missing_obj["obj_id"]."'");
			}

			// put in tree under RecoveryFolder if not on exclude list
			if (!in_array($missing_obj["type"],$this->object_types_exclude))
			{
				$rbacadmin->revokePermission($missing_obj["ref_id"]);
				$obj_data =& $ilias->obj_factory->getInstanceByRefId($missing_obj["ref_id"]);
				$obj_data->putInTree(RECOVERY_FOLDER_ID);
				$obj_data->setPermissions(RECOVERY_FOLDER_ID);
				$obj_data->initDefaultRoles();
				unset($obj_data);
				//$tree->insertNode($missing_obj["ref_id"],RECOVERY_FOLDER_ID);
				$restored = true;
				$this->writeScanLogLine("Restored object '".$missing_obj["title"]."' (id=".$missing_obj["obj_id"].",ref=".$missing_obj["ref_id"].") in 'Restored objects folder'");
			}
			
			// TODO: process rolefolders
		}
		
		return $restored;
	}

	/**
	* restore a reference for an object
	* Creates a new reference entry in DB table object_reference for $a_obj_id
	* 
	* @param	integer	obj_id
	* @access	private
	* @return	integer/boolean	generated ref_id or false on error
	* @see		this::restoreMissingObjects()	
	*/
	function restoreReference($a_obj_id)
	{
		global $ilLog;

		if (empty($a_obj_id))
		{
			$this->throwError(INVALID_PARAM, WARNING, DEBUG);
			return false;
		}
		
		$q = "INSERT INTO object_reference (ref_id,obj_id) VALUES ('0','".$a_obj_id."')";
		$this->db->query($q);

		$message = sprintf('%s::restoreReference(): new reference %s for obj_id %s created',
						   get_class($this),
						   $this->db->getLastInsertId(),
						   $_obj_id);
		$ilLog->write($message,$ilLog->WARNING);

		return $this->db->getLastInsertId();
	}

	/**
	* Restore objects (and their subobjects) to RecoveryFolder that are valid but not linked correctly
	* in the hierarchy because they point to an invalid parent_id
	*
	* @access	public
	* @param	array	list of childs with invalid parents (optional)
	* @return	boolean false on error or restore mode disabled
	* @see		this::findUnboundObjects()
	* @see		this::restoreSubTrees()
	*/
	function restoreUnboundObjects($a_unbound_objects = NULL)
	{
		global $ilLog;

		// check mode: restore
		if ($this->mode["restore"] !== true)
		{
			return false;
		}

		$this->writeScanLogLine("\nrestoreUnboundObjects:");

		if ($a_unbound_objects === NULL and isset($this->unbound_objects))
		{
			$a_unbound_objects = $this->unbound_objects;
		}

		// handle wrong input
		if (!is_array($a_unbound_objects)) 
		{
			$this->throwError(INVALID_PARAM, WARNING, DEBUG);
			return false;
		}

		$message = sprintf('%s::restoreUnboundObjects(): Started...',
						   get_class($this));
		$ilLog->write($message,$ilLog->WARNING);
		
		// start restore process
		return $this->restoreSubTrees($a_unbound_objects);
	}
	
	/**
	* Restore all objects in trash to RecoveryFolder
	* NOTE: All objects will be restored to top of RecoveryFolder regardless of existing hierarchical structure!
	*
	* @access	public
	* @param	array	list of deleted childs  (optional)
	* @return	boolean false on error or restore mode disabled
	* @see		this::findDeletedObjects()
	*/
	function restoreTrash($a_deleted_objects = NULL)
	{
		global $ilLog;

		// check mode: restore
		if ($this->mode["restore_trash"] !== true)
		{
			return false;
		}

		$this->writeScanLogLine("\nrestoreTrash:");
	
		if ($a_deleted_objects === NULL and isset($this->deleted_objects))
		{
			$a_deleted_objects = $this->deleted_objects;
		}

		// handle wrong input
		if (!is_array($a_deleted_objects)) 
		{
			$this->throwError(INVALID_PARAM, WARNING, DEBUG);
			return false;
		}

		$message = sprintf('%s::restoreTrash(): Started...',
						   get_class($this));
		$ilLog->write($message,$ilLog->WARNING);
	
		// start restore process
		$restored = $this->restoreDeletedObjects($a_deleted_objects);
		
		if ($restored)
		{
			$q = "DELETE FROM tree WHERE tree!=1";
			$this->db->query($q);

			$message = sprintf('%s::restoreTrash(): Removed all trees with tree id <> 1',
							   get_class($this));
			$ilLog->write($message,$ilLog->WARNING);
		
			$this->writeScanLogLine("Old tree entries removed");
		}
		
		return $restored;
	}
	
	/**
	* Restore deleted objects (and their subobjects) to RecoveryFolder
	*
	* @access	private
	* @param	array	list of nodes
	* @return	boolean false on error or restore mode disabled
	* @see		this::restoreTrash()
	*/
	function restoreDeletedObjects($a_nodes)
	{
		global $tree,$rbacadmin,$ilias,$ilLog;
//vd($a_nodes);exit;
		// handle wrong input
		if (!is_array($a_nodes)) 
		{
			$this->throwError(INVALID_PARAM, WARNING, DEBUG);
			return false;
		}

		// no invalid parents found. do nothing
		if (count($a_nodes) == 0)
		{
			$this->writeScanLogLine("none");
			return false;
		}

		$message = sprintf('%s::restoreDeletedObjects()): Started...',
						   get_class($this));
		$ilLog->write($message,$ilLog->WARNING);

		// first delete all rolefolders
		// don't save rolefolders, remove them
		// TODO process ROLE_FOLDER_ID
		foreach ($a_nodes as $key => $node)
		{
			if ($node["type"] == "rolf")
			{
				// delete old tree entries
				$tree->deleteTree($node);

				$obj_data =& $ilias->obj_factory->getInstanceByRefId($node["child"]);
				$obj_data->delete();
				unset($a_nodes[$key]);
			}	
		}
		
		// process move
		foreach ($a_nodes as $node)
		{
			// delete old tree entries
			$tree->deleteTree($node);
			
			$rbacadmin->revokePermission($node["child"]);
			$obj_data =& $ilias->obj_factory->getInstanceByRefId($node["child"]);
			$obj_data->putInTree(RECOVERY_FOLDER_ID);
			$obj_data->setPermissions(RECOVERY_FOLDER_ID);
			$obj_data->initDefaultRoles();
		}
		
		return true;
	}

	/**
	* Restore objects (and their subobjects) to RecoveryFolder
	*
	* @access	private
	* @param	array	list of nodes
	* @return	boolean false on error or restore mode disabled
	* @see		this::restoreUnboundObjects()
	*/
	function restoreSubTrees ($a_nodes)
	{
		global $tree,$rbacadmin,$ilias,$ilLog;
		
		// handle wrong input
		if (!is_array($a_nodes)) 
		{
			$this->throwError(INVALID_PARAM, WARNING, DEBUG);
			return false;
		}

		// no invalid parents found. do nothing
		if (count($a_nodes) == 0)
		{
			$this->writeScanLogLine("none");
			return false;
		}
		
/*******************
restore starts here
********************/

		$subnodes = array();
		$topnode = array();

		$message = sprintf('%s::restoreSubTrees(): Started...',
						   get_class($this));
		$ilLog->write($message,$ilLog->WARNING);
		
		// process move subtree
		foreach ($a_nodes as $node)
		{
			// get node data
			$topnode = $tree->getNodeData($node["child"]);
			
			// don't save rolefolders, remove them
			// TODO process ROLE_FOLDER_ID
			if ($topnode["type"] == "rolf")
			{
				$rolfObj = $ilias->obj_factory->getInstanceByRefId($topnode["child"]);
				$rolfObj->delete();
				unset($top_node);
				unset($rolfObj);
				continue;
			}

			// get subnodes of top nodes
			$subnodes[$node["child"]] = $tree->getSubtree($topnode);
		
			// delete old tree entries
			$tree->deleteTree($topnode);
		}

		// now move all subtrees to new location
		// TODO: this whole put in place again stuff needs revision. Permission settings get lost.
		foreach ($subnodes as $key => $subnode)
		{

			// first paste top_node ...
			$rbacadmin->revokePermission($key);
			$obj_data =& $ilias->obj_factory->getInstanceByRefId($key);
			$obj_data->putInTree(RECOVERY_FOLDER_ID);
			$obj_data->setPermissions(RECOVERY_FOLDER_ID);
			$obj_data->initDefaultRoles();
			
			$this->writeScanLogLine("Object '".$obj_data->getId()."' restored.");

			// ... remove top_node from list ...
			array_shift($subnode);
			
			// ... insert subtree of top_node if any subnodes exist
			if (count($subnode) > 0)
			{
				foreach ($subnode as $node)
				{
					$rbacadmin->revokePermission($node["child"]);
					$obj_data =& $ilias->obj_factory->getInstanceByRefId($node["child"]);
					$obj_data->putInTree($node["parent"]);
					$obj_data->setPermissions($node["parent"]);
					$obj_data->initDefaultRoles();
					
					$this->writeScanLogLine("Object '".$obj_data->getId()."' restored.");
				}
			}
		}

		// final clean up
		$this->findInvalidChilds();
		$this->removeInvalidChilds();

		return true;
	}
	
	/**
	* Removes all objects in trash from system
	* 
	* @access	public
	* @param	array	list of nodes to delete
	* @return	boolean	true on success
	* @see		this::purgeObjects()
	* @see		this::findDeletedObjects()
	*/
	function purgeTrash($a_nodes = NULL)
	{
		global $ilLog;
		
		// check mode: purge_trash
		if ($this->mode["purge_trash"] !== true)
		{
			return false;
		}

		$this->writeScanLogLine("\npurgeTrash:");
	
		if ($a_nodes === NULL and isset($this->deleted_objects))
		{
			$a_nodes = $this->deleted_objects;
		}

		$message = sprintf('%s::purgeTrash(): Started...',
						   get_class($this));
		$ilLog->write($message,$ilLog->WARNING);
		
		// start purge process
		return $this->purgeObjects($a_nodes);
	}
	
	/**
	* Removes all invalid objects from system
	* 
	* @access	public
	* @param	array	list of nodes to delete
	* @return	boolean	true on success
	* @see		this::purgeObjects()
	* @see		this::findUnboundObjects()
	*/
	function purgeUnboundObjects($a_nodes = NULL)
	{
		// check mode: purge
		if ($this->mode["purge"] !== true)
		{
			return false;
		}

		$this->writeScanLogLine("\npurgeUnboundObjects:");

		if ($a_nodes === NULL and isset($this->unbound_objects))
		{
			$a_nodes = $this->unbound_objects;
		}

		$message = sprintf('%s::purgeUnboundObjects(): Started...',
						   get_class($this));
		$ilLog->write($message,$ilLog->WARNING);
		
		// start purge process
		return $this->purgeObjects($a_nodes);
	}

	/**
	* Removes all missing objects from system
	* 
	* @access	public
	* @param	array	list of nodes to delete
	* @return	boolean	true on success
	* @see		this::purgeObjects()
	* @see		this::findMissingObjects()
	*/
	function purgeMissingObjects($a_nodes = NULL)
	{
		global $ilLog;
		
		// check mode: purge
		if ($this->mode["purge"] !== true)
		{
			return false;
		}

		$this->writeScanLogLine("\npurgeMissingObjects:");

		if ($a_nodes === NULL and isset($this->missing_objects))
		{
			$a_nodes = $this->missing_objects;
		}

		$message = sprintf('%s::purgeMissingObjects(): Started...',
						   get_class($this));
		$ilLog->write($message,$ilLog->WARNING);
		
		// start purge process
		return $this->purgeObjects($a_nodes);
	}
	
	/**
	* removes objects from system
	* 
	* @access	private
	* @param	array	list of objects
	* @return	boolean
	*/
	function purgeObjects($a_nodes)
	{
		global $ilias,$ilLog;

		// handle wrong input
		if (!is_array($a_nodes)) 
		{
			$this->throwError(INVALID_PARAM, WARNING, DEBUG);
			return false;
		}
		
		// start delete process
		foreach ($a_nodes as $node)
		{
			$ref_id = ($node["child"]) ? $node["child"] : $node["ref_id"];
			$node_obj =& $ilias->obj_factory->getInstanceByRefId($ref_id,false);
			
			if ($node_obj === false)
			{
				$this->invalid_objects[] = $node;
				continue;
			}

			$message = sprintf('%s::purgeObjects(): Removing object (id:%s ref:%s)',
							   get_class($this),
							   $ref_id,
							   $node_obj->getId);
			$ilLog->write($message,$ilLog->WARNING);
		
			$node_obj->delete();
			ilTree::_removeEntry($node["tree"],$ref_id);
			
			$this->writeScanLogLine("Object '".$node_obj->getId()."' deleted");
		}
		
		$this->findInvalidChilds();
		$this->removeInvalidChilds();

		return true;
	}

	/**
	* close gaps in lft/rgt values of a tree
	* Wrapper for ilTree::renumber()
	* 
	* @access	public
	* @return	boolean false if clean mode disabled
	* @see		ilTree::renumber()
	*/
	function closeGapsInTree()
	{
		global $tree,$ilLog;
		
		$message = sprintf('%s::closeGapsInTree(): Started...',
						   get_class($this));
		$ilLog->write($message,$ilLog->WARNING);

		// check mode: clean
		if ($this->mode["clean"] !== true)
		{
			return false;
		}
		$this->writeScanLogLine("\nrenumberTree:");

		$tree->renumber(ROOT_FOLDER_ID);

		$this->writeScanLogLine("done");

		return true;
	}

	/**
	* Callback function
	* handles PEAR_error and outputs detailed infos about error
	* TODO: implement that in global errorhandler of ILIAS (via templates)
	* 
	* @access	private
	* @param	object	PEAR_error
	* @see		PEAR::PEAR_error()
	*/
	function handleErr($error)
	{
		$call_loc = $error->backtrace[count($error->backtrace)-1];
		$num_args = count($call_loc["args"]);

		if ($num_args > 0)
		{
			foreach ($call_loc["args"] as $arg)
			{
				$type = gettype($arg);
				
				switch ($type)
				{
					case "string":
						$value = strlen($arg);
						break;

					case "array":
						$value = count($arg);
						break;

					case "object":
						$value = get_class($arg);
						break;

					case "boolean":
						$value = ($arg) ? "true" : "false";
						break;
						
					default:
						$value = $arg;
						break;
				}
				
				$arg_list[] = array(
									"type"	=> $type,
									"value"	=> "(".$value.")"
									);
			}
			
			foreach ($arg_list as $arg)
			{
				$arg_str .= implode("",$arg)." ";
			}
		}

		$err_msg = "<br/><b>".$error->getCode().":</b> ".$error->getMessage()." in ".$call_loc["class"].$call_loc["type"].$call_loc["function"]."()".
				   "<br/>Called from: ".basename($call_loc["file"])." , line ".$call_loc["line"].
				   "<br/>Passed parameters: [".$num_args."] ".$arg_str."<br/>";
		printf($err_msg);
		
		if ($error->getUserInfo())
		{
			printf("<br/>Parameter details:");
			echo "<pre>";
			var_dump($call_loc["args"]);
			echo "</pre>";
		}
		
		if ($error->getCode() == FATAL)
		{
			exit();
		}
	}
	
	function writeScanLogArray($a_arr)
	{
		if (!$this->isLogEnabled())
		{
			return false;
		}
		
		foreach ($a_arr as $entry)
		{
			$this->scan_log->write(implode("\t",$entry));		
		}
	}
	
	function writeScanLogLine($a_msg)
	{
		if (!$this->isLogEnabled())
		{
			return false;
		}
		
		$this->scan_log->write($a_msg);
	}

	function readScanLog()
	{
		// file check
		if (!is_file(CLIENT_DATA_DIR."/".$this->scan_log_file))
		{
			return false;
		}

		// header check
		if (!$scan_log = $this->get_last_scan(file(CLIENT_DATA_DIR."/".$this->scan_log_file)))
		{
			return false;
		}
		
		return $scan_log;
	}
	
	function get_last_scan($a_scan_log)
	{
		$logs = array_keys($a_scan_log,$this->scan_log_separator."\n");
		
		if (count($logs) > 0)
		{
			return array_slice($a_scan_log,array_pop($logs)+2);
		}
		
		return false;
	}
	
	function checkTreeStructure($a_startnode = null)
	{
		global $tree;

		$this->writeScanLogLine("\nchecking tree structure is disabled");
		
		return false;
	}
} // END class.ilValidator
?>
