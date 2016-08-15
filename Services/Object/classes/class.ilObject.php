<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilObject
* Basic functions for all objects
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @author Alex Killing <alex.killing@gmx.de>
* @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
* @version $Id$
*/
class ilObject
{
	/**
	 * max length of object title
	 */
	const TITLE_LENGTH = 255; // title column max length in db
	const DESC_LENGTH = 128; // (short) description column max length in db


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
	// BEGIN WebDAV: WebDAV needs to access the untranslated title of an object
	var $untranslatedTitle;
	// END WebDAV: WebDAV needs to access the untranslated title of an object
	var $desc;
	var $long_desc;
	var $owner;
	var $create_date;
	var $last_update;
	var $import_id;
	var $register = false;		// registering required for object? set to true to implement a subscription interface

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
	* @var integer
	*/
	var $max_title;

	/**
	* max description length
	* @var integer
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
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function __construct($a_id = 0, $a_reference = true)
	{
		global $ilias, $lng, $ilBench, $objDefinition;

		$ilBench->start("Core", "ilObject_Constructor");

		if (DEBUG)
		{
			echo "<br/><font color=\"red\">type(".$this->type.") id(".$a_id.") referenced(".$a_reference.")</font>";
		}

		$this->ilias =& $ilias;
		$this->lng =& $lng;
		$this->objDefinition = $objDefinition;

		$this->max_title = self::TITLE_LENGTH;
		$this->max_desc = self::DESC_LENGTH;
		$this->add_dots = true;

		$this->referenced = $a_reference;
		$this->call_by_reference = $a_reference;

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

		$ilBench->stop("Core", "ilObject_Constructor");
	}

	/**
	* determines wehter objects are referenced or not (got ref ids or not)
	*/
	function withReferences()
	{
		// both vars could differ. this method should always return true if one of them is true without changing their status
		return ($this->call_by_reference) ? true : $this->referenced;
	}


	/**
	* read object data from db into object
	* @param	boolean
	* @access	public
	*/
	public function read()
	{
		global $objDefinition, $ilBench, $ilDB, $log;

		$ilBench->start("Core", "ilObject_read");
		if ($this->referenced)
		{
			// check reference id
			if (!isset($this->ref_id))
			{
				$message = "ilObject::read(): No ref_id given! (".$this->type.")";
				$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
			}

			// read object data
			$ilBench->start("Core", "ilObject_read_readData");

			$q = "SELECT * FROM object_data, object_reference WHERE object_data.obj_id=object_reference.obj_id ".
				 "AND object_reference.ref_id= ".$ilDB->quote($this->ref_id, "integer");
			$object_set = $ilDB->query($q);
			$ilBench->stop("Core", "ilObject_read_readData");

			// check number of records
			if ($ilDB->numRows($object_set) == 0)
			{
				$message = "ilObject::read(): Object with ref_id ".$this->ref_id." not found! (".$this->type.")";
				$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
			}

			$obj = $ilDB->fetchAssoc($object_set);
		}
		else
		{
			// check object id
			if (!isset($this->id))
			{
				$message = "ilObject::read(): No obj_id given! (".$this->type.")";
				$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
			}

			// read object data
			$q = "SELECT * FROM object_data ".
				 "WHERE obj_id = ".$ilDB->quote($this->id, "integer");
			$object_set = $ilDB->query($q);

			// check number of records
			if ($ilDB->numRows($object_set) == 0)
			{
				include_once("./Services/Object/exceptions/class.ilObjectNotFoundException.php");
				throw new ilObjectNotFoundException("ilObject::read(): Object with obj_id: ".$this->id.
					" (".$this->type.") not found!");
				return;
			}

			$obj = $ilDB->fetchAssoc($object_set);
		}

		$this->id = $obj["obj_id"];
		
		// check type match (the "xxx" type is used for the unit test)
		if ($this->type != $obj["type"] && $obj["type"] != "xxx")
		{
			$message = "ilObject::read(): Type mismatch. Object with obj_id: ".$this->id." ".
				"was instantiated by type '".$this->type."'. DB type is: ".$obj["type"];

			// write log entry
			$log->write($message);
				
			// raise error
			include_once("./Services/Object/exceptions/class.ilObjectTypeMismatchException.php");
			throw new ilObjectTypeMismatchException($message);
			return;
		}
		
		$this->type = $obj["type"];
		$this->title = $obj["title"];
		// BEGIN WebDAV: WebDAV needs to access the untranslated title of an object
		$this->untranslatedTitle = $obj["title"];
		// END WebDAV: WebDAV needs to access the untranslated title of an object
		$this->desc = $obj["description"];
		$this->owner = $obj["owner"];
		$this->create_date = $obj["create_date"];
		$this->last_update = $obj["last_update"];
		$this->import_id = $obj["import_id"];
		
		if($objDefinition->isRBACObject($this->getType()))
		{
			// Read long description
			$query = "SELECT * FROM object_description WHERE obj_id = ".$ilDB->quote($this->id,'integer');
			$res = $this->ilias->db->query($query);
			while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
			{
				if(strlen($row->description))
				{
					$this->setDescription($row->description);
				}
			}
		}

		// multilingual support systemobjects (sys) & categories (db)
		$ilBench->start("Core", "ilObject_Constructor_getTranslation");
		$translation_type = $objDefinition->getTranslationType($this->type);

		if ($translation_type == "sys")
		{
			$this->title = $this->lng->txt("obj_".$this->type);
			$this->setDescription($this->lng->txt("obj_".$this->type."_desc"));
		}
		elseif ($translation_type == "db")
		{
			$q = "SELECT title,description FROM object_translation ".
				 "WHERE obj_id = ".$ilDB->quote($this->id,'integer')." ".
				 "AND lang_code = ".$ilDB->quote($this->ilias->account->getCurrentLanguage(),'text')." ".
				 "AND NOT lang_default = 1";
			$r = $this->ilias->db->query($q);
			$row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
			if ($row)
			{
				$this->title = $row->title;
				$this->setDescription($row->description);
				#$this->desc = $row->description;
			}
		}

		$ilBench->stop("Core", "ilObject_Constructor_getTranslation");

		$ilBench->stop("Core", "ilObject_read");
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
	 * get presentation title
	 * Normally same as title
	 * Overwritten for sessions
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function getPresentationTitle()
	{
		return $this->getTitle();
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
	// BEGIN WebDAV: WebDAV needs to access the untranslated title of an object
	/**
	* get untranslated object title
	* @access	public
	* @return	string		object title
	*/
	function getUntranslatedTitle()
	{
		return $this->untranslatedTitle;
	}
	// END WebDAV: WebDAV needs to access the untranslated title of an object

	/**
	* set object title
	*
	* @access	public
	* @param	string		$a_title		object title
	*/
	function setTitle($a_title)
	{
		$this->title = ilUtil::shortenText($a_title, $this->max_title, $this->add_dots);
		// BEGIN WebDAV: WebDAV needs to access the untranslated title of an object
		$this->untranslatedTitle = $this->title;
		// END WebDAV: WebDAV needs to access the untranslated title of an object
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
		// Shortened form is storted in object_data. Long form is stored in object_description
		$this->desc = ilUtil::shortenText($a_desc, $this->max_desc, $this->add_dots);

		$this->long_desc = $a_desc;

		return true;
	}

	/**
	* get object long description (stored in object_description)
	*
	* @access	public
	* @return	string		object description
	*/
	function getLongDescription()
	{
		return strlen($this->long_desc) ? $this->long_desc : $this->desc;
	}

	/**
	* get import id
	*
	* @access	public
	* @return	string	import id
	*/
	function getImportId()
	{
		return $this->import_id;
	}

	/**
	* set import id
	*
	* @access	public
	* @param	string		$a_import_id		import id
	*/
	function setImportId($a_import_id)
	{
		$this->import_id = $a_import_id;
	}

	public static function _lookupObjIdByImportId($a_import_id)
	{
		global $ilDB;

		$query = "SELECT * FROM object_data ".
			"WHERE import_id = ".$ilDB->quote($a_import_id, "text")." ".
			"ORDER BY create_date DESC";
		$res = $ilDB->query($query);
		while($row = $ilDB->fetchObject($res))
		{
			return $row->obj_id;
		}
		return 0;
	}

	public static function _lookupImportId($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT import_id FROM object_data ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id, "integer");
		$res = $ilDB->query($query);
		$row = $ilDB->fetchObject($res);
		return $row->import_id;
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
		return ilObject::_lookupOwnerName($this->getOwner());
	}

	/**
	* lookup owner name for owner id
	*/
	static function _lookupOwnerName($a_owner_id)
	{
		global $lng;

		if ($a_owner_id != -1)
		{
			if (ilObject::_exists($a_owner_id))
			{
				$owner = new ilObjUser($a_owner_id);
			}
		}

		if (is_object($owner))
		{
			$own_name = $owner->getFullname();
		}
		else
		{
			$own_name = $lng->txt("unknown");
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
	* Gets the disk usage of the object in bytes.
    * Returns null, if the object does not use disk space at all.
	*
	* The implementation of class ilObject always returns null.
	* Subclasses which use disk space can override this method to return a
	* non-null value.
    *
	* @access	public
	* @return	integer		the disk usage in bytes or null
	*/
	function getDiskUsage()
	{
		return null;
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
		global $ilDB, $log,$ilUser,$objDefinition;

		if (!isset($this->type))
		{
			$message = get_class($this)."::create(): No object type given!";
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		// write log entry
		$log->write("ilObject::create(), start");

		$this->title = ilUtil::shortenText($this->getTitle(), $this->max_title, $this->add_dots);
		$this->desc = ilUtil::shortenText($this->getDescription(), $this->max_desc, $this->add_dots);
		
		// determine owner
		if ($this->getOwner() > 0)
		{
			$owner = $this->getOwner();
		}
		elseif(is_object($ilUser))
		{
			$owner = $ilUser->getId();
		}
		else
		{
			$owner = 0;
		}
		$this->id = $ilDB->nextId("object_data");
		$q = "INSERT INTO object_data ".
			 "(obj_id,type,title,description,owner,create_date,last_update,import_id) ".
			 "VALUES ".
			 "(".
			 $ilDB->quote($this->id, "integer").",".
			 $ilDB->quote($this->type, "text").",".
			 $ilDB->quote($this->getTitle(), "text").",".
			 $ilDB->quote($this->getDescription(), "text").",".
			 $ilDB->quote($owner, "integer").",".
			 $ilDB->now().",".
			 $ilDB->now().",".
			 $ilDB->quote($this->getImportId(), "text").")";

		$ilDB->manipulate($q);

		//$this->id = $ilDB->getLastInsertId();

		
		// Save long form of description if is rbac object
		if($objDefinition->isRBACObject($this->getType()))
		{
			$values = array(
				'obj_id'		=> array('integer',$this->id),
				'description'	=> array('clob', $this->getLongDescription()));
//var_dump($values);
			$ilDB->insert('object_description',$values);
		}
		

		// the line ($this->read();) messes up meta data handling: meta data,
		// that is not saved at this time, gets lost, so we query for the dates alone
		//$this->read();
		$q = "SELECT last_update, create_date FROM object_data".
			 " WHERE obj_id = ".$ilDB->quote($this->id, "integer");
		$obj_set = $ilDB->query($q);
		$obj_rec = $ilDB->fetchAssoc($obj_set);
		$this->last_update = $obj_rec["last_update"];
		$this->create_date = $obj_rec["create_date"];

		// set owner for new objects
		$this->setOwner($owner);

		// write log entry
		$log->write("ilObject::create(), finished, obj_id: ".$this->id.", type: ".
			$this->type.", title: ".$this->getTitle());

		$GLOBALS['ilAppEventHandler']->raise(
			'Services/Object',
			'create',
			array('obj_id' => $this->id,'obj_type' => $this->type));

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
		global $objDefinition, $ilDB;

		$q = "UPDATE object_data ".
			"SET ".
			"title = ".$ilDB->quote($this->getTitle(), "text").",".
			"description = ".$ilDB->quote($this->getDescription(), "text").", ".
			"import_id = ".$ilDB->quote($this->getImportId(), "text").",".
			"last_update = ".$ilDB->now()." ".
			"WHERE obj_id = ".$ilDB->quote($this->getId(), "integer");
		$ilDB->manipulate($q);

		// the line ($this->read();) messes up meta data handling: meta data,
		// that is not saved at this time, gets lost, so we query for the dates alone
		//$this->read();
		$q = "SELECT last_update FROM object_data".
			 " WHERE obj_id = ".$ilDB->quote($this->getId(), "integer");
		$obj_set = $ilDB->query($q);
		$obj_rec = $ilDB->fetchAssoc($obj_set);
		$this->last_update = $obj_rec["last_update"];

		if($objDefinition->isRBACObject($this->getType()))
		{
			// Update long description
			$res = $this->ilias->db->query("SELECT * FROM object_description WHERE obj_id = ".
				$ilDB->quote($this->getId(),'integer'));
			if($res->numRows())
			{
				$values = array(
					'description'	=> array('clob',$this->getLongDescription())
					);
				$ilDB->update('object_description',$values,array('obj_id' => array('integer',$this->getId())));
			}
			else
			{
				$values = array(
					'description'	=> array('clob',$this->getLongDescription()),
					'obj_id'		=> array('integer',$this->getId()));
				$ilDB->insert('object_description',$values);
			}
		}
		$GLOBALS['ilAppEventHandler']->raise(
			'Services/Object',
			'update',
			array('obj_id' => $this->getId(),
				'obj_type' => $this->getType(),
				'ref_id' => $this->getRefId()));

		return true;
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

		$GLOBALS['ilAppEventHandler']->raise(
			'Services/Object',
			'update',
			array('obj_id' => $this->getId(),
				'obj_type' => $this->getType(),
				'ref_id' => $this->getRefId()));

		switch($a_element)
		{
			case 'General':

				// Update Title and description
				$md = new ilMD($this->getId(),0, $this->getType());
				if(!is_object($md_gen = $md->getGeneral()))
				{
					return false;
				}
				$this->setTitle($md_gen->getTitle());

				foreach($md_gen->getDescriptionIds() as $id)
				{
					$md_des = $md_gen->getDescription($id);
					$this->setDescription($md_des->getDescription());
					break;
				}
				$this->update();
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
		include_once 'Services/MetaData/classes/class.ilMDCreator.php';

		global $ilUser;

		$md_creator = new ilMDCreator($this->getId(),0,$this->getType());
		$md_creator->setTitle($this->getTitle());
		$md_creator->setTitleLanguage($ilUser->getPref('language'));
		$md_creator->setDescription($this->getLongDescription());
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

		$md = new ilMD($this->getId(), 0, $this->getType());
		$md_gen =& $md->getGeneral();
		// BEGIN WebDAV: meta data can be missing sometimes.
		if ($md_gen == null)
		{
			$this->createMetaData();
			$md = new ilMD($this->getId(), 0, $this->getType());
			$md_gen =& $md->getGeneral();
		}
		// END WebDAV: meta data can be missing sometimes.
		$md_gen->setTitle($this->getTitle());

		// sets first description (maybe not appropriate)
		$md_des_ids =& $md_gen->getDescriptionIds();
		if (count($md_des_ids) > 0)
		{
			$md_des =& $md_gen->getDescription($md_des_ids[0]);
			$md_des->setDescription($this->getLongDescription());
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
		$md = new ilMD($this->getId(), 0, $this->getType());
		$md->deleteAll();
	}

    /**
     * update owner of object in db
     *
     * @access   public
     * @return   boolean true on success
     */
    function updateOwner()
    {
		global $ilDB;
		
        $q = "UPDATE object_data ".
            "SET ".
            "owner = ".$ilDB->quote($this->getOwner(), "integer").", ".
            "last_update = ".$ilDB->now()." ".
            "WHERE obj_id = ".$ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($q);

        $q = "SELECT last_update FROM object_data".
             " WHERE obj_id = ".$ilDB->quote($this->getId(), "integer");
        $obj_set = $ilDB->query($q);
        $obj_rec = $ilDB->fetchAssoc($obj_set);
        $this->last_update = $obj_rec["last_update"];

        return true;
    }

	/**
	* get current object id for import id (static)
	*
	* @param	int		$a_import_id		import id
	*
	* @return	int		id
	*/
	static function _getIdForImportId($a_import_id)
	{
		global $ilDB;
		
		$ilDB->setLimit(1,0);
		$q = "SELECT * FROM object_data WHERE import_id = ".$ilDB->quote($a_import_id, "text").
			" ORDER BY create_date DESC";
		$obj_set = $ilDB->query($q);

		if ($obj_rec = $ilDB->fetchAssoc($obj_set))
		{
			return $obj_rec["obj_id"];
		}
		else
		{
			return 0;
		}
	}

	/**
	* get all reference ids of object
	*
	* @param	int		$a_id		object id
	*/
	public static function _getAllReferences($a_id)
	{
		global $ilDB;

		$query = "SELECT * FROM object_reference WHERE obj_id = ".
			$ilDB->quote($a_id,'integer');
			
		$res = $ilDB->query($query);
		$ref = array();
		while($obj_rec = $ilDB->fetchAssoc($res))
		{
			$ref[$obj_rec["ref_id"]] = $obj_rec["ref_id"];
		}

		return $ref;
	}

	/**
	* lookup object title
	*
	* @param	int		$a_id		object id
	*/
	public static function _lookupTitle($a_id)
	{
		global $ilObjDataCache;

		$tit = $ilObjDataCache->lookupTitle($a_id);
//echo "<br>LOOKING-$a_id-:$tit";		
		return $tit;
	}
	
	/**
	* lookup object owner
	*
	* @param	int		$a_id		object id
	*/
	static function _lookupOwner($a_id)
	{
		global $ilObjDataCache;

		$owner = $ilObjDataCache->lookupOwner($a_id);
		return $owner;
	}

	public static function _getIdsForTitle($title, $type = '', $partialmatch = false)
	{
		global $ilDB;
		
		$query = (!$partialmatch)
			? "SELECT obj_id FROM object_data WHERE title = ".$ilDB->quote($title, "text")
			: "SELECT obj_id FROM object_data WHERE ".$ilDB->like("title", "text", '%'.$title.'%');
		if($type != '')
		{
			$query .= " AND type = ".$ilDB->quote($type, "text");
		}
		
		$result = $ilDB->query($query);
		
		$object_ids = array();
		while($row = $ilDB->fetchAssoc($result))
		{
			$object_ids[] = $row['obj_id'];
		}
		
		return is_array($object_ids) ? $object_ids : array();
	}

	/**
	* lookup object description
	*
	* @param	int		$a_id		object id
	*/
	public static function _lookupDescription($a_id)
	{
		global $ilObjDataCache;

		return $ilObjDataCache->lookupDescription($a_id);
	}

	/**
	* lookup last update
	*
	* @param	int		$a_id		object id
	*/
	static function _lookupLastUpdate($a_id, $a_as_string = false)
	{
		global $ilObjDataCache;
		
		if ($a_as_string)
		{
			return ilDatePresentation::formatDate(new ilDateTime($ilObjDataCache->lookupLastUpdate($a_id),IL_CAL_DATETIME));
		}
		else
		{
			return $ilObjDataCache->lookupLastUpdate($a_id);
		}
	}

	/**
	* Get last update for a set of media objects.
	*
	* @param	array
	*/
	static function _getLastUpdateOfObjects($a_objs)
	{
		global $ilDB;
		
		if (!is_array($a_objs))
		{
			$a_objs = array($a_objs);
		}
		$types = array();
		$set = $ilDB->query("SELECT max(last_update) as last_update FROM object_data ".
			"WHERE ".$ilDB->in("obj_id", $a_objs, false, "integer")." ");
		$rec = $ilDB->fetchAssoc($set);
		
		return ($rec["last_update"]);
	}

	public static function _lookupObjId($a_id)
	{
		global $ilObjDataCache;

		return (int) $ilObjDataCache->lookupObjId($a_id);
	}
	
	/**
	* only called in ilTree::saveSubTree
	*/
	static function _setDeletedDate($a_ref_id)
	{
		global $ilDB;
		
		$query = "UPDATE object_reference SET deleted= ".$ilDB->now().' '.
			"WHERE ref_id = ".$ilDB->quote($a_ref_id,'integer');
		$res = $ilDB->manipulate($query);
	}
	
	/**
	 * Set deleted date
	 * @global type $ilDB
	 * @param type $a_ref_ids
	 * @return type
	 */
	public static function setDeletedDates($a_ref_ids)
	{
		global $ilDB;
		
		$query = 'UPDATE object_reference SET deleted = '.$ilDB->now().' '.
				'WHERE '.$ilDB->in('ref_id',(array) $a_ref_ids,false,'integer');
		
		$GLOBALS['ilLog']->write(__METHOD__.': Query is '. $query);
		$ilDB->manipulate($query);
		return;
	}

	/**
	* only called in ilObjectGUI::insertSavedNodes
	*/
	public static function _resetDeletedDate($a_ref_id)
	{
		global $ilDB;
		
		$query = "UPDATE object_reference SET deleted = ".$ilDB->quote(null,'timestamp').
			" WHERE ref_id = ".$ilDB->quote($a_ref_id,'integer');
		$ilDB->manipulate($query);
	}
	
	/**
	* only called in ilObjectGUI::insertSavedNodes
	*/
	static function _lookupDeletedDate($a_ref_id)
	{
		global $ilDB;
		
		$query = "SELECT deleted FROM object_reference".
			" WHERE ref_id = ".$ilDB->quote($a_ref_id, "integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);

		return $rec["deleted"];
	}


	/**
	* write title to db (static)
	*
	* @param	int		$a_obj_id		object id
	* @param	string	$a_title		title
	* @access	public
	*/
	static function _writeTitle($a_obj_id, $a_title)
	{
		global $ilDB;

		$q = "UPDATE object_data ".
			"SET ".
			"title = ".$ilDB->quote($a_title, "text").",".
			"last_update = ".$ilDB->now()." ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id, "integer");

		$ilDB->manipulate($q);
	}

	/**
	* write description to db (static)
	*
	* @param	int		$a_obj_id		object id
	* @param	string	$a_desc			description
	* @access	public
	*/
	static function _writeDescription($a_obj_id, $a_desc)
	{
		global $ilDB,$objDefinition;


		$desc = ilUtil::shortenText($a_desc,self::DESC_LENGTH,true);

		$q = "UPDATE object_data ".
			"SET ".
			"description = ".$ilDB->quote($desc, "text").",".
			"last_update = ".$ilDB->now()." ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id, "integer");

		$ilDB->manipulate($q);

		if($objDefinition->isRBACObject(ilObject::_lookupType($a_obj_id)))
		{
			// Update long description
			$res = $ilDB->query("SELECT * FROM object_description WHERE obj_id = ".
				$ilDB->quote($a_obj_id,'integer'));

			if($res->numRows())
			{
				$values = array(
					'description'	=> array('clob',$a_desc)
					);
				$ilDB->update('object_description',$values,array('obj_id' => array('integer',$a_obj_id)));
			}
			else
			{
				$values = array(
					'description'	=> array('clob',$a_desc),
					'obj_id'		=> array('integer',$a_obj_id));
				$ilDB->insert('object_description',$values);
			}
		}
	}

	/**
	* write import id to db (static)
	*
	* @param	int		$a_obj_id			object id
	* @param	string	$a_import_id		import id
	* @access	public
	*/
	static function _writeImportId($a_obj_id, $a_import_id)
	{
		global $ilDB;

		$q = "UPDATE object_data ".
			"SET ".
			"import_id = ".$ilDB->quote($a_import_id, "text").",".
			"last_update = ".$ilDB->now()." ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id, "integer");

		$ilDB->manipulate($q);
	}

	/**
	* lookup object type
	*
	* @param	int		$a_id		object id
	*/
	public static function _lookupType($a_id,$a_reference = false)
	{
		global $ilObjDataCache;

		if($a_reference)
		{
			return $ilObjDataCache->lookupType($ilObjDataCache->lookupObjId($a_id));
		}
		return $ilObjDataCache->lookupType($a_id);
	}

	/**
	* checks wether object is in trash
	*/
	public static function _isInTrash($a_ref_id)
	{
		global $tree;

		return $tree->isSaved($a_ref_id);
	}

	/**
	* checks wether an object has at least one reference that is not in trash
	*/
	static function _hasUntrashedReference($a_obj_id)
	{
		$ref_ids  = ilObject::_getAllReferences($a_obj_id);
		foreach($ref_ids as $ref_id)
		{
			if(!ilObject::_isInTrash($ref_id))
			{
				return true;
			}
		}

		return false;
	}

	/**
	* lookup object id
	* @static
	* @param	int		$a_id		object id
	*/
	public static function _lookupObjectId($a_ref_id)
	{
		global $ilObjDataCache;

		return (int) $ilObjDataCache->lookupObjId($a_ref_id);
	}

	/**
	* get all objects of a certain type
	*
	* @param	string		$a_type			desired object type
	* @param	boolean		$a_omit_trash	omit objects, that are in trash only
	*										(default: false)
	*
	* @return	array		array of object data arrays ("id", "title", "type",
	*						"description")
	*/
	static function _getObjectsDataForType($a_type, $a_omit_trash = false)
	{
		global $ilDB;

		$q = "SELECT * FROM object_data WHERE type = ".$ilDB->quote($a_type, "text");
		$obj_set = $ilDB->query($q);

		$objects = array();
		while ($obj_rec = $ilDB->fetchAssoc($obj_set))
		{
			if ((!$a_omit_trash) || ilObject::_hasUntrashedReference($obj_rec["obj_id"]))
			{
				$objects[$obj_rec["title"].".".$obj_rec["obj_id"]] = array("id" => $obj_rec["obj_id"],
					"type" => $obj_rec["type"], "title" => $obj_rec["title"],
					"description" => $obj_rec["description"]);
			}
		}
		ksort($objects);
		return $objects;
	}

	/**
	* maybe this method should be in tree object!?
	*
	* @todo	role/rbac stuff
	*/
	function putInTree($a_parent_ref)
	{
		global $tree, $log;

		$tree->insertNode($this->getRefId(), $a_parent_ref);
		
		// write log entry
		$log->write("ilObject::putInTree(), parent_ref: $a_parent_ref, ref_id: ".
			$this->getRefId().", obj_id: ".$this->getId().", type: ".
			$this->getType().", title: ".$this->getTitle());

	}

	/**
	* set permissions of object
	*
	* @param	integer	reference_id of parent object
	* @access	public
	*/
	function setPermissions($a_parent_ref)
	{
		$this->setParentRolePermissions($a_parent_ref);
		$this->initDefaultRoles();
	}
	
	/**
	 * Initialize the permissions of parent roles (local roles of categories, global roles...)
	 * This method is overwritten in e.g courses, groups for building permission intersections with non_member  templates.
	 */
	public function setParentRolePermissions($a_parent_ref)
	{
		global $rbacadmin, $rbacreview;
		
		$parent_roles = $rbacreview->getParentRoleIds($a_parent_ref);
		foreach((array) $parent_roles as $parent_role)
		{
			$operations = $rbacreview->getOperationsOfRole(
				$parent_role['obj_id'],
				$this->getType(),
				$parent_role['parent']
			);
			$rbacadmin->grantPermission(
				$parent_role['obj_id'],
				$operations,
				$this->getRefId()
			);
		}
		return true;
	}

	/**
	* creates reference for object
	*
	* @access	public
	* @return	integer	reference_id of object
	*/
	function createReference()
	{
		global $ilDB;

		if (!isset($this->id))
		{
			$message = "ilObject::createNewReference(): No obj_id given!";
			$this->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$next_id = $ilDB->nextId('object_reference');
		$query = "INSERT INTO object_reference ".
			 "(ref_id, obj_id) VALUES (".$ilDB->quote($next_id,'integer').','.$ilDB->quote($this->id ,'integer').")";
		$this->ilias->db->query($query);

		$this->ref_id = $next_id;
		$this->referenced = true;

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
		global $ilDB;
		
		if (!isset($this->id))
		{
			$message = "ilObject::countReferences(): No obj_id given!";
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$query = "SELECT COUNT(ref_id) num FROM object_reference ".
		 	"WHERE obj_id = ".$ilDB->quote($this->id,'integer')." ";
		$res = $ilDB->query($query);
		$row = $ilDB->fetchObject($res);

		return $row->num;
	}




	/**
	* delete object or referenced object
	* (in the case of a referenced object, object data is only deleted
	* if last reference is deleted)
	* This function removes an object entirely from system!!
	*
 	* @access	public
	* @return	boolean	true if object was removed completely; false if only a references was removed
	*/
	function delete()
	{
		global $rbacadmin, $log, $ilDB;

		$remove = false;

		// delete object_data entry
		if ((!$this->referenced) || ($this->countReferences() == 1))
		{
			// check type match
			$db_type = ilObject::_lookupType($this->getId());
			if ($this->type != $db_type)
			{
				$message = "ilObject::delete(): Type mismatch. Object with obj_id: ".$this->id." ".
					"was instantiated by type '".$this->type."'. DB type is: ".$db_type;
					
				// write log entry
				$log->write($message);
					
				// raise error
				$this->ilias->raiseError("ilObject::delete(): Type mismatch. (".$this->type."/".$this->id.")",$this->ilias->error_obj->WARNING);
			}
			
			// delete entry in object_data
			$q = "DELETE FROM object_data ".
				"WHERE obj_id = ".$ilDB->quote($this->getId(), "integer");
			$ilDB->manipulate($q);

			// delete long description
			$query = "DELETE FROM object_description WHERE obj_id = ".
				$ilDB->quote($this->getId(), "integer");
			$ilDB->manipulate($query);

			// write log entry
			$log->write("ilObject::delete(), deleted object, obj_id: ".$this->getId().", type: ".
				$this->getType().", title: ".$this->getTitle());
			
			// keep log of core object data 
			include_once "Services/Object/classes/class.ilObjectDataDeletionLog.php";
			ilObjectDataDeletionLog::add($this);
			
			// remove news
			include_once("./Services/News/classes/class.ilNewsItem.php");
			$news_item = new ilNewsItem();
			$news_item->deleteNewsOfContext($this->getId(), $this->getType());
			include_once("./Services/Block/classes/class.ilBlockSetting.php");
			ilBlockSetting::_deleteSettingsOfBlock($this->getId(), "news");

			include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateObjSettings.php';
			ilDidacticTemplateObjSettings::deleteByObjId($this->getId());

			/* remove notes (see infoscreen gui)
			   as they can be seen as personal data we are keeping them for now
			include_once("Services/Notes/classes/class.ilNote.php");
			foreach(array(IL_NOTE_PRIVATE, IL_NOTE_PUBLIC) as $note_type)
			{
				foreach(ilNote::_getNotesOfObject($this->id, 0, $this->type, $note_type) as $note)
				{
					$note->delete();
				}
			}
		    */
						
			// BEGIN WebDAV: Delete WebDAV properties
			$query = "DELETE FROM dav_property ".
				"WHERE obj_id = ".$ilDB->quote($this->getId(),'integer');
			$res = $ilDB->manipulate($query);
			// END WebDAV: Delete WebDAV properties

			include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
			ilECSImport::_deleteByObjId($this->getId());
			
			include_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php");
			ilAdvancedMDValues::_deleteByObjId($this->getId());
			
			include_once("Services/Tracking/classes/class.ilLPObjSettings.php");
			ilLPObjSettings::_deleteByObjId($this->getId());

			$remove = true;
		}
		else
		{
			// write log entry
			$log->write("ilObject::delete(), object not deleted, number of references: ".
				$this->countReferences().", obj_id: ".$this->getId().", type: ".
				$this->getType().", title: ".$this->getTitle());
		}

		// delete object_reference entry
		if ($this->referenced)
		{			
			include_once "Services/Object/classes/class.ilObjectActivation.php";
			ilObjectActivation::deleteAllEntries($this->getRefId());
			
			// delete entry in object_reference
			$query = "DELETE FROM object_reference ".
				"WHERE ref_id = ".$ilDB->quote($this->getRefId(),'integer');
			$res = $ilDB->manipulate($query);
			
			// write log entry
			$log->write("ilObject::delete(), reference deleted, ref_id: ".$this->getRefId().
				", obj_id: ".$this->getId().", type: ".
				$this->getType().", title: ".$this->getTitle());

			// DELETE PERMISSION ENTRIES IN RBAC_PA
			// DONE: method overwritten in ilObjRole & ilObjUser.
			// this call only applies for objects in rbac (not usr,role,rolt)
			// TODO: Do this for role templates too
			$rbacadmin->revokePermission($this->getRefId(),0,false);

			include_once "Services/AccessControl/classes/class.ilRbacLog.php";
			ilRbacLog::delete($this->getRefId());

			// Remove applied didactic template setting
			include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateObjSettings.php';
			ilDidacticTemplateObjSettings::deleteByRefId($this->getRefId());

			// Remove desktop items
			ilUtil::removeItemFromDesktops($this->getRefId());
		}

		// remove conditions
		if ($this->referenced)
		{
			$ch = new ilConditionHandler();
			$ch->delete($this->getRefId());
			unset($ch);
		}

		return $remove;
	}

	/**
	* init default roles settings
	* Purpose of this function is to create a local role folder and local roles, that are needed depending on the object type
	* If you want to setup default local roles you MUST overwrite this method in derived object classes (see ilObjForum for an example)
	* @access	public
	* @return	array	empty array
	*/
	function initDefaultRoles()
	{
		return array();
	}
	

	/**
	 * Apply template
	 * @param int $a_tpl_id
	 */
	public function applyDidacticTemplate($a_tpl_id)
	{
		if(!$a_tpl_id)
		{
			return true;
		}

		include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateObjSettings.php';
		ilDidacticTemplateObjSettings::assignTemplate($this->getRefId(), $this->getId(), (int) $a_tpl_id);

		include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateActionFactory.php';
		foreach(ilDidacticTemplateActionFactory::getActionsByTemplateId($a_tpl_id) as $action)
		{
			$action->setRefId($this->getRefId());
			$action->apply();
		}
	}

	/**
	* checks if an object exists in object_data
	* @static
	* @access	public
	* @param	integer	object id or reference id
	* @param	boolean	true if id is a reference, else false (default)
	* @param	string	restrict on a certain type.
	* @return	boolean	true if object exists
	*/
	public static function _exists($a_id, $a_reference = false, $a_type = null)
	{
		global $ilDB;
		
		if ($a_reference)
		{
			$q = "SELECT * FROM object_data ".
				 "LEFT JOIN object_reference ON object_reference.obj_id=object_data.obj_id ".
				 "WHERE object_reference.ref_id= ".$ilDB->quote($a_id, "integer");
		}
		else
		{
			$q = "SELECT * FROM object_data WHERE obj_id=".$ilDB->quote($a_id, "integer");
		}

		if($a_type)
			$q .= " AND object_data.type = ".$ilDB->quote($a_type, "text");
		
		$r = $ilDB->query($q);

		return $ilDB->numRows($r) ? true : false;
	}
		
// toggle subscription interface
	function setRegisterMode($a_bool)
	{
		$this->register = (bool) $a_bool;
	}
	
	// check register status of current user
	// abstract method; overwrite in object type class
	function isUserRegistered($a_user_id = 0)
	{
		return false;
	}

	function requireRegistration()
	{
		return $this->register;
	}


	function getXMLZip()
	{
		return false;
	}
	function getHTMLDirectory()
	{
		return false;
	}

	/**
	* Get objects by type
	*/
	static function _getObjectsByType($a_obj_type = "", $a_owner = "")
	{
		global $ilDB;
				
		$order = " ORDER BY title";

		// where clause
		if ($a_obj_type)
		{
			$where_clause = "WHERE type = ".
				$ilDB->quote($a_obj_type, "text");
				
			if ($a_owner != "")
			{
				$where_clause.= " AND owner = ".$ilDB->quote($a_owner, "integer");
			}
		}
	
		$q = "SELECT * FROM object_data ".$where_clause.$order;
		$r = $ilDB->query($q);

		$arr = array();
		if ($ilDB->numRows($r) > 0)
		{
			while ($row = $ilDB->fetchAssoc($r))
			{
				$row["desc"] = $row["description"];
				$arr[$row["obj_id"]] = $row;
			}
		}
	
		return $arr;
	}
	
	/**
	 * Prepare copy wizard object selection 
	 * 
	 * This method should renamed. Currently used in ilObjsurvey and ilObjTest
	 * @deprecated since version 5.2
	 * @static
	 * 
	 * @global type $ilDB
	 * @global type $lng
	 * @global type $objDefinition
	 * @param array $a_ref_ids
	 * @param string $new_type
	 * @param bool $show_path
	 * @return array
	 */
	public static function _prepareCloneSelection($a_ref_ids,$new_type,$show_path = true)
	{
		global $ilDB,$lng,$objDefinition;
		
		$query = "SELECT obj_data.title obj_title,path_data.title path_title,child FROM tree ".
			"JOIN object_reference obj_ref ON child = obj_ref.ref_id ".
			"JOIN object_data obj_data ON obj_ref.obj_id = obj_data.obj_id ".
			"JOIN object_reference path_ref ON parent = path_ref.ref_id ".
			"JOIN object_data path_data ON path_ref.obj_id = path_data.obj_id ".
			"WHERE ".$ilDB->in("child", $a_ref_ids, false, "integer")." ".
			"ORDER BY obj_data.title ";
		$res = $ilDB->query($query);
		
		if (!$objDefinition->isPlugin($new_type))
		{
			$options[0] = $lng->txt('obj_'.$new_type.'_select');
		}
		else
		{
			include_once("./Services/Component/classes/class.ilPlugin.php");
			$options[0] = ilPlugin::lookupTxt("rep_robj", $new_type, "obj_".$new_type."_select");
		}

		while($row = $ilDB->fetchObject($res))
		{
			if(strlen($title = $row->obj_title) > 40)
			{
				$title = substr($title,0,40).'...';
			}
			
			if($show_path)
			{
				if(strlen($path = $row->path_title) > 40)
				{
					$path = substr($path,0,40).'...';
				}
				
				$title .= ' ('.$lng->txt('path').': '.$path.')';
			}
			
			$options[$row->child] = $title;
		}
		return $options ? $options : array();
	}

	/**
	 * Clone object permissions, put in tree ...
	 *
	 * @access public
	 * @param int target id
	 * @param int copy id for class.ilCopyWizardOptions()
	 * @return object new object
	 *  
	 */
	public function cloneObject($a_target_id,$a_copy_id = 0)
	{
		global $objDefinition,$ilUser,$rbacadmin, $ilDB;
		
		$location = $objDefinition->getLocation($this->getType());
		$class_name = ('ilObj'.$objDefinition->getClassName($this->getType()));
		
		include_once './Services/CopyWizard/classes/class.ilCopyWizardOptions.php';
		$options = ilCopyWizardOptions::_getInstance($a_copy_id);
		
		if(!$options->isTreeCopyDisabled())
		{
			$title = $this->appendCopyInfo($a_target_id,$a_copy_id);
		}
		else
		{
			$title = $this->getTitle();
		}

		// create instance
		include_once($location."/class.".$class_name.".php");
		$new_obj = new $class_name(0, false);
		$new_obj->setOwner($ilUser->getId());
		$new_obj->setTitle($title);
		$new_obj->setDescription($this->getLongDescription());
		$new_obj->setType($this->getType());
		// Choose upload mode to avoid creation of additional settings, db entries ...
		$new_obj->create(true);

		if(!$options->isTreeCopyDisabled())
		{
			ilLoggerFactory::getLogger('obj')->debug('Tree copy is enabled');
			$new_obj->createReference();
			$new_obj->putInTree($a_target_id);
			$new_obj->setPermissions($a_target_id);

			// when copying from personal workspace we have no current ref id
			if($this->getRefId())
			{
				// copy local roles
				$rbacadmin->copyLocalRoles($this->getRefId(),$new_obj->getRefId());
			}
		}
		else
		{
			ilLoggerFactory::getLogger('obj')->debug('Tree copy is disabled');
		}
		
		include_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php');
		ilAdvancedMDValues::_cloneValues($this->getId(),$new_obj->getId());

		// BEGIN WebDAV: Clone WebDAV properties
		$query = "INSERT INTO dav_property (obj_id,node_id,ns,name,value) ".
			"SELECT ".$ilDB->quote($new_obj->getId(),'integer').",node_id,ns,name,value ".
			"FROM dav_property ".
			"WHERE obj_id = ".$ilDB->quote($this->getId(),'integer');
		$res = $ilDB->manipulate($query);
		// END WebDAV: Clone WebDAV properties
		
		return $new_obj;
	}
	
	/**
	 * Prepend Copy info if object with same name exists in that container
	 *
	 * @access public
	 * @param int copy_id
	 * 
	 */
	public function appendCopyInfo($a_target_id,$a_copy_id)
	{
		global $tree;
		
		include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
		$cp_options = ilCopyWizardOptions::_getInstance($a_copy_id);
		if(!$cp_options->isRootNode($this->getRefId()))
		{
			return $this->getTitle();
		}
		$nodes = $tree->getChilds($a_target_id);
		
		$title_unique = false;
		require_once 'Modules/File/classes/class.ilObjFileAccess.php';
		$numberOfCopy = 1;
		$handleExtension = ($this->getType() == "file"); // #14883
		$title = ilObjFileAccess::_appendNumberOfCopyToFilename($this->getTitle(), $numberOfCopy, $handleExtension);
		while(!$title_unique)
		{
			$found = 0;
			foreach($nodes as $node)
			{
				if(($title == $node['title']) and ($this->getType() == $node['type']))
				{
					$found++;
				}
			}
			if($found > 0)
			{
				$title = ilObjFileAccess::_appendNumberOfCopyToFilename($this->getTitle(), ++$numberOfCopy, $handleExtension);
			}
			else
			{
				break;
			}
		}
		return $title;
	}	
	
	/**
	 * Clone object dependencies
	 *
	 * This method allows to refresh any ref id references to other objects
	 * that are affected in the same copy process. Ask ilCopyWizardOptions for
	 * the mappings.
	 *
	 * @access public
	 * @param int ref_id of target object
	 * @param int copy_id
	 * 
	 */
	public function cloneDependencies($a_target_id,$a_copy_id)
	{
		include_once './Services/AccessControl/classes/class.ilConditionHandler.php' ;
		ilConditionHandler::cloneDependencies($this->getRefId(),$a_target_id,$a_copy_id);
		
		include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateObjSettings.php';
		$tpl_id = ilDidacticTemplateObjSettings::lookupTemplateId($this->getRefId());
		if($tpl_id)
		{
			include_once './Services/Object/classes/class.ilObjectFactory.php';
			$factory = new ilObjectFactory();
			$obj = $factory->getInstanceByRefId($a_target_id, FALSE);
			if($obj instanceof ilObject)
			{
				$obj->applyDidacticTemplate($tpl_id);
			}
		}
		return true;
	}
	
	/**
	 * Copy meta data
	 *
	 * @access public
	 * @param object target object
	 * 
	 */
	public function cloneMetaData($target_obj)
	{
	 	include_once "./Services/MetaData/classes/class.ilMD.php";
		$md = new ilMD($this->getId(),0,$this->getType());
		$md->cloneMD($target_obj->getId(),0,$target_obj->getType());
		return true;	 	
	}
	
	/**
	* Get icon for repository item.
	*
	* @param	int			object id
	* @param	string		size (big, small, tiny)
	* @param	string		object type
	* @param	boolean		true: offline, false: online
	*/
	public static function _getIcon($a_obj_id = "", $a_size = "big", $a_type = "",
		$a_offline = false)
	{
		global $ilSetting, $objDefinition;

		if ($a_obj_id == "" && $a_type == "")
		{
			return "";
		}
		
		if ($a_type == "")
		{
			$a_type = ilObject::_lookupType($a_obj_id);
		}
		
		if ($a_size == "")
		{
			$a_size = "big";
		}

		if ($ilSetting->get("custom_icons") &&
			in_array($a_type, array("cat","grp","crs", "root", "fold", "prg")))
		{
			require_once("./Services/Container/classes/class.ilContainer.php");
			if (ilContainer::_lookupContainerSetting($a_obj_id, "icon_custom"))
			{
				$cont_dir = ilContainer::_getContainerDirectory($a_obj_id);

				$file_name = $cont_dir."/icon_custom.svg";
				if (is_file($file_name))
				{
					return $file_name;
				}
			}
		}
		
		if (!$a_offline)
		{			
			if ($objDefinition->isPluginTypeName($a_type))
			{
				if ($objDefinition->getClassName($a_type) != "")
				{
					$class_name = "il".$objDefinition->getClassName($a_type).'Plugin';
					$location = $objDefinition->getLocation($a_type);
					if (is_file($location."/class.".$class_name.".php"))
					{
						include_once($location."/class.".$class_name.".php");
						return call_user_func(array($class_name, "_getIcon"), $a_type, $a_size, $a_obj_id);
					}
				}
				return ilUtil::getImagePath("icon_cmps.svg");
			}
			
			return ilUtil::getImagePath("icon_".$a_type.".svg");
		}
		else
		{
			return "./images/icon_".$a_type.".svg";
		}
	}

	/**
	 * Collect deletion dependencies. E.g. 
	 *
	 * @param
	 * @return
	 */
	static function collectDeletionDependencies(&$deps, $a_ref_id, $a_obj_id, $a_type, $a_depth = 0)
	{
		global $objDefinition, $tree;

		if ($a_depth == 0)
		{
			$deps["dep"] = array();
		}
		
		$deps["del_ids"][$a_obj_id] = $a_obj_id;
		
		if (!$objDefinition->isPluginTypeName($a_type))
		{
			$class_name = "ilObj".$objDefinition->getClassName($a_type);		
			$location = $objDefinition->getLocation($a_type);
			include_once($location."/class.".$class_name.".php");
			$odeps = call_user_func(array($class_name, "getDeletionDependencies"), $a_obj_id);
			if (is_array($odeps))
			{
				foreach ($odeps as $id => $message)
				{
					$deps["dep"][$id][$a_obj_id][] = $message;
				}
			}
			
			// get deletion dependency of childs
			foreach ($tree->getChilds($a_ref_id) as $c)
			{
				ilObject::collectDeletionDependencies($deps, $c["child"], $c["obj_id"], $c["type"], $a_depth + 1);
			}
		}
		
		// delete all dependencies to objects that will be deleted, too
		if ($a_depth == 0)
		{
			foreach ($deps["del_ids"] as $obj_id)
			{
				unset($deps["dep"][$obj_id]);
			}
			$deps = $deps["dep"];
		}		
	}
	
	/**
	 * Get deletion dependencies
	 *
	 */
	static function getDeletionDependencies($a_obj_id)
	{
		return false;	
	}
	
	/**
	 * Get long description data
	 *
	 * @param array $a_obj_ids
	 * @return array 
	 */
	static function getLongDescriptions(array $a_obj_ids)
	{
		global $ilDB;
		
		$res = $ilDB->query("SELECT * FROM object_description".
			" WHERE ".$ilDB->in("obj_id", $a_obj_ids, "", "integer"));
		$all = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$all[$row["obj_id"]] = $row["description"];
		}
		return $all;
	}
	
	/**
	 * Get all ids of objects user owns
	 * 
	 * @param int $a_user_id
	 * @return array 
	 */
	static function getAllOwnedRepositoryObjects($a_user_id)
	{
		global $ilDB, $objDefinition;
				
		$all = array();
		
		// restrict to repository
		$types = array_keys($objDefinition->getSubObjectsRecursively("root"));	
			
		$sql = "SELECT od.obj_id,od.type,od.title FROM object_data od".
			" JOIN object_reference oref ON(oref.obj_id = od.obj_id)".
			" JOIN tree ON (tree.child = oref.ref_id)";
		
		if($a_user_id)
		{
			$sql .= " WHERE od.owner = ".$ilDB->quote($a_user_id, "integer");				
		}
		else
		{
			$sql .= " LEFT JOIN usr_data ud ON (ud.usr_id = od.owner)".
				" WHERE (od.owner < ".$ilDB->quote(1, "integer").
				" OR od.owner IS NULL OR ud.login IS NULL)".
				" AND od.owner <> ".$ilDB->quote(-1, "integer");
		}
		
		$sql .= " AND ".$ilDB->in("od.type", $types, "", "text").
			" AND tree.tree > ".$ilDB->quote(0, "integer"); // #12485
			
		$res = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($res))
		{
			$all[$row["type"]][$row["obj_id"]] = $row["title"];
		}
				
		return $all;
	}
	
	/**
	 * Try to fix missing object titles
	 * 
	 * @param type $a_type
	 * @param array &$a_obj_title_map
	 */
	static function fixMissingTitles($a_type, array &$a_obj_title_map)
	{
		global $ilDB;
		
		if(!in_array($a_type, array("catr", "crsr", "sess", "grpr")))
		{
			return;
		}
		
		// any missing titles?
		$missing_obj_ids = array();
		foreach($a_obj_title_map as $obj_id => $title)
		{
			if(!trim($title))
			{
				$missing_obj_ids[] = $obj_id;
			}
		}
		
		if(!sizeof($missing_obj_ids))
		{
			return;
		}
		
		switch($a_type)
		{
			case "grpr":
			case "catr":
			case "crsr":				
				$set = $ilDB->query("SELECT oref.obj_id, od.type, od.title FROM object_data od".
					" JOIN container_reference oref ON (od.obj_id = oref.target_obj_id)".
					" AND ".$ilDB->in("oref.obj_id", $missing_obj_ids, "", "integer"));
				while($row = $ilDB->fetchAssoc($set))
				{					
					$a_obj_title_map[$row["obj_id"]] = $row["title"];									
				}
				break;
				
			case "sess":				
				include_once "Modules/Session/classes/class.ilObjSession.php";
				foreach($missing_obj_ids as $obj_id)
				{
					$sess = new ilObjSession($obj_id, false);
					$a_obj_title_map[$obj_id] = $sess->getFirstAppointment()->appointmentToString();
				}
				break;				
		}				
	}
	
	/**
	 * Lookup creation date
	 *
	 * @param
	 * @return
	 */
	static function _lookupCreationDate($a_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT create_date FROM object_data ".
			" WHERE obj_id = ".$ilDB->quote($a_id, "integer"));
		$rec  = $ilDB->fetchAssoc($set);
		return $rec["create_date"];
	}
		
	/**
	 * Check if auto rating is active for parent group/course
	 * 
	 * @param string $a_type
	 * @param int $a_ref_id
	 * @return bool
	 */
	public static function hasAutoRating($a_type, $a_ref_id)
	{
		global $tree;
		
		if(!$a_ref_id ||
			!in_array($a_type, array("file", "lm", "wiki")))
		{
			return false;
		}

		// find parent container
		$parent_ref_id = $tree->checkForParentType($a_ref_id, "grp");
		if(!$parent_ref_id)
		{					
			$parent_ref_id = $tree->checkForParentType($a_ref_id, "crs");
		}				
		if($parent_ref_id)
		{
			include_once './Services/Object/classes/class.ilObjectServiceSettingsGUI.php';
			
			// get auto rate setting
			$parent_obj_id = ilObject::_lookupObjId($parent_ref_id);		
			return ilContainer::_lookupContainerSetting(
				$parent_obj_id,
				ilObjectServiceSettingsGUI::AUTO_RATING_NEW_OBJECTS,
				false
			);
		}
		return false;
	}

	/**
	* get all possible subobjects of this type
	* the object can decide which types of subobjects are possible jut in time
	* overwrite if the decision distinguish from standard model
	*
	* @param boolean filter disabled objects? ($a_filter = true)
	* @access public
	* @return array list of allowed object types
	*/
	function getPossibleSubObjects($a_filter = true) {
		return $this->objDefinition->getSubObjects($this->type, $a_filter);
	}
} // END class.ilObject
?>
