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
     * @var ilObjectDefinition
     */
    protected $objDefinition;

    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var Logger
     */
    protected $log;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilAppEventHandler
     */
    protected $app_event_handler;

    /**
     * @var ilRbacAdmin
     */
    protected $rbacadmin;

    /**
     * @var ilRbacReview
     */
    protected $rbacreview;

    /**
     * max length of object title
     */
    const TITLE_LENGTH = 255; // title column max length in db
    const DESC_LENGTH = 128; // (short) description column max length in db


    /**
    * lng object
    * @var		object language
    * @access	private
    */
    public $lng;

    /**
    * object id
    * @var		integer object id of object itself
    * @access	private
    */
    public $id;	// true object_id!!!!
    public $ref_id;// reference_id
    public $type;
    public $title;

    /**
     * Check if object is offline
     * null means undefined
     *
     * @var null | int
     */
    private $offline = null;


    // BEGIN WebDAV: WebDAV needs to access the untranslated title of an object
    public $untranslatedTitle;
    // END WebDAV: WebDAV needs to access the untranslated title of an object
    public $desc;
    public $long_desc;
    public $owner;
    public $create_date;
    public $last_update;
    public $import_id;
    public $register = false;		// registering required for object? set to true to implement a subscription interface

    /**
    * indicates if object is a referenced object
    * @var		boolean
    * @access	private
    */
    public $referenced;

    /**
    * object list
    * @var		array	contains all child objects of current object
    * @access	private
    */
    public $objectList;

    /**
    * max title length
    * @var integer
    */
    public $max_title;

    /**
    * max description length
    * @var integer
    */
    public $max_desc;

    /**
    * add dots to shortened titles and descriptions
    * @var boolean
    */
    public $add_dots;


    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_reference = true)
    {
        global $DIC;


        $this->ilias = $DIC["ilias"];

        $this->db = $DIC->database();
        $this->log = $DIC["ilLog"];
        $this->error = $DIC["ilErr"];
        $this->tree = $DIC->repositoryTree();
        $this->app_event_handler = $DIC["ilAppEventHandler"];
        $objDefinition = $DIC["objDefinition"];

        if (DEBUG) {
            echo "<br/><font color=\"red\">type(" . $this->type . ") id(" . $a_id . ") referenced(" . $a_reference . ")</font>";
        }

        if (isset($DIC["lng"])) {
            $this->lng = $DIC["lng"];
        }
        $this->objDefinition = $objDefinition;

        $this->max_title = self::TITLE_LENGTH;
        $this->max_desc = self::DESC_LENGTH;
        $this->add_dots = true;

        $this->referenced = $a_reference;
        $this->call_by_reference = $a_reference;

        if ($a_id == 0) {
            $this->referenced = false;		// newly created objects are never referenced
        }									// they will get referenced if createReference() is called

        if ($this->referenced) {
            $this->ref_id = $a_id;
        } else {
            $this->id = $a_id;
        }
        // read object data
        if ($a_id != 0) {
            $this->read();
        }
    }

    /**
    * determines wehter objects are referenced or not (got ref ids or not)
    */
    public function withReferences()
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
        global $DIC;

        $objDefinition = $this->objDefinition;
        $ilDB = $this->db;
        $ilLog = $this->log;
        $ilErr = $this->error;
        try {
            $ilUser = $DIC["ilUser"];
        } catch (\InvalidArgumentException $e) {
        }

        if ($this->referenced) {
            // check reference id
            if (!isset($this->ref_id)) {
                $message = "ilObject::read(): No ref_id given! (" . $this->type . ")";
                $ilErr->raiseError($message, $ilErr->WARNING);
            }

            // read object data

            $q = "SELECT * FROM object_data, object_reference WHERE object_data.obj_id=object_reference.obj_id " .
                 "AND object_reference.ref_id= " . $ilDB->quote($this->ref_id, "integer");
            $object_set = $ilDB->query($q);

            // check number of records
            if ($ilDB->numRows($object_set) == 0) {
                $message = "ilObject::read(): Object with ref_id " . $this->ref_id . " not found! (" . $this->type . ")";
                $ilErr->raiseError($message, $ilErr->WARNING);
            }

            $obj = $ilDB->fetchAssoc($object_set);
        } else {
            // check object id
            if (!isset($this->id)) {
                $message = "ilObject::read(): No obj_id given! (" . $this->type . ")";
                $ilErr->raiseError($message, $ilErr->WARNING);
            }

            // read object data
            $q = "SELECT * FROM object_data " .
                 "WHERE obj_id = " . $ilDB->quote($this->id, "integer");
            $object_set = $ilDB->query($q);

            // check number of records
            if ($ilDB->numRows($object_set) == 0) {
                include_once("./Services/Object/exceptions/class.ilObjectNotFoundException.php");
                throw new ilObjectNotFoundException("ilObject::read(): Object with obj_id: " . $this->id .
                    " (" . $this->type . ") not found!");
                return;
            }

            $obj = $ilDB->fetchAssoc($object_set);
        }

        $this->id = $obj["obj_id"];

        // check type match (the "xxx" type is used for the unit test)
        if ($this->type != $obj["type"] && $obj["type"] != "xxx") {
            $message = "ilObject::read(): Type mismatch. Object with obj_id: " . $this->id . " " .
                "was instantiated by type '" . $this->type . "'. DB type is: " . $obj["type"];

            // write log entry
            $ilLog->write($message);

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

        $this->setOfflineStatus($obj['offline']);

        if ($objDefinition->isRBACObject($this->getType())) {
            // Read long description
            $query = "SELECT * FROM object_description WHERE obj_id = " . $ilDB->quote($this->id, 'integer');
            $res = $ilDB->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                if (strlen($row->description)) {
                    $this->setDescription($row->description);
                }
            }
        }

        // multilingual support systemobjects (sys) & categories (db)
        $translation_type = $objDefinition->getTranslationType($this->type);

        if ($translation_type == "sys") {
            $this->title = $this->lng->txt("obj_" . $this->type);
            $this->setDescription($this->lng->txt("obj_" . $this->type . "_desc"));
        } elseif ($translation_type == "db") {
            $q = "SELECT title,description FROM object_translation " .
                 "WHERE obj_id = " . $ilDB->quote($this->id, 'integer') . " " .
                 "AND lang_code = " . $ilDB->quote($ilUser->getCurrentLanguage(), 'text');
            $r = $ilDB->query($q);
            $row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
            if ($row) {
                $this->title = $row->title;
                $this->setDescription($row->description);
                #$this->desc = $row->description;
            }
        }
    }

    /**
    * get object id
    * @access	public
    * @return	integer	object id
    */
    public function getId() : int
    {
        return (int) $this->id;
    }

    /**
    * set object id
    * @access	public
    * @param	integer	$a_id		object id
    */
    public function setId($a_id)
    {
        $this->id = (int) $a_id;
    }

    /**
    * set reference id
    * @access	public
    * @param	integer	$a_id		reference id
    */
    public function setRefId($a_id)
    {
        $this->ref_id = $a_id;
        $this->referenced = true;
    }

    /**
    * get reference id
    * @access	public
    * @return	integer	reference id
    */
    public function getRefId()
    {
        return $this->ref_id;
    }

    /**
    * get object type
    * @access	public
    * @return	string		object type
    */
    public function getType()
    {
        return $this->type;
    }

    /**
    * set object type
    * @access	public
    * @param	integer	$a_type		object type
    */
    public function setType($a_type)
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
    public function getTitle()
    {
        return $this->title;
    }
    // BEGIN WebDAV: WebDAV needs to access the untranslated title of an object
    /**
    * get untranslated object title
    * @access	public
    * @return	string		object title
    */
    public function getUntranslatedTitle()
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
    public function setTitle($a_title)
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
    public function getDescription()
    {
        return $this->desc ?? '';
    }

    /**
    * set object description
    *
    * @access	public
    * @param	string		$a_desc		object description
    */
    public function setDescription($a_desc)
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
    public function getLongDescription()
    {
        if (strlen($this->long_desc)) {
            return $this->long_desc;
        }

        return $this->getDescription();
    }

    /**
    * get import id
    *
    * @access	public
    * @return	string	import id
    */
    public function getImportId()
    {
        return $this->import_id;
    }

    /**
    * set import id
    *
    * @access	public
    * @param	string		$a_import_id		import id
    */
    public function setImportId($a_import_id)
    {
        $this->import_id = $a_import_id;
    }

    public static function _lookupObjIdByImportId($a_import_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM object_data " .
            "WHERE import_id = " . $ilDB->quote($a_import_id, "text") . " " .
            "ORDER BY create_date DESC";
        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchObject($res)) {
            return $row->obj_id;
        }
        return 0;
    }

    /**
     * Set offline status
     * @param bool $a_status
     */
    public function setOfflineStatus($a_status)
    {
        $this->offline = $a_status;
    }

    /**
     * Get offline status
     * @return int|null
     */
    public function getOfflineStatus()
    {
        return $this->offline;
    }

    /**
     * Check whether object supports offline handling
     * @return bool
     */
    public function supportsOfflineHandling()
    {
        global $DIC;

        return (bool) $DIC['objDefinition']->supportsOfflineHandling($this->getType());
    }




    public static function _lookupImportId($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT import_id FROM object_data " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer");
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
    public function getOwner()
    {
        return $this->owner;
    }

    /*
    * get full name of object owner
    *
    * @access	public
    * @return	string	owner name or unknown
    */
    public function getOwnerName()
    {
        return ilObject::_lookupOwnerName($this->getOwner());
    }

    /**
    * lookup owner name for owner id
    */
    public static function _lookupOwnerName($a_owner_id)
    {
        global $DIC;

        $lng = $DIC->language();

        if ($a_owner_id != -1) {
            if (ilObject::_exists($a_owner_id)) {
                $owner = new ilObjUser($a_owner_id);
            }
        }

        if (is_object($owner)) {
            $own_name = $owner->getFullname();
        } else {
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
    public function setOwner($a_owner)
    {
        $this->owner = $a_owner;
    }



    /**
    * get create date
    * @access	public
    * @return	string		creation date
    */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
    * get last update date
    * @access	public
    * @return	string		date of last update
    */
    public function getLastUpdateDate()
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
    public function getDiskUsage()
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
    public function create()
    {
        global $DIC;

        $app_event = $DIC->event();
        $ilDB = $this->db;
        $ilLog = $this->log;
        $ilUser = $DIC["ilUser"];
        $objDefinition = $this->objDefinition;
        $ilErr = $this->error;

        if (!isset($this->type)) {
            $message = get_class($this) . "::create(): No object type given!";
            $ilErr->raiseError($message, $ilErr->WARNING);
        }

        // write log entry
        $ilLog->write("ilObject::create(), start");

        $this->title = ilUtil::shortenText($this->getTitle(), $this->max_title, $this->add_dots);
        $this->desc = ilUtil::shortenText($this->getDescription(), $this->max_desc, $this->add_dots);

        // determine owner
        if ($this->getOwner() > 0) {
            $owner = $this->getOwner();
        } elseif (is_object($ilUser)) {
            $owner = $ilUser->getId();
        } else {
            $owner = 0;
        }
        $this->id = $ilDB->nextId("object_data");
        $q = "INSERT INTO object_data " .
            "(obj_id,type,title,description,offline,owner,create_date,last_update,import_id) " .
            "VALUES " .
            "(" .
            $ilDB->quote($this->id, "integer") . "," .
            $ilDB->quote($this->type, "text") . "," .
            $ilDB->quote($this->getTitle(), "text") . "," .
            $ilDB->quote($this->getDescription(), "text") . "," .
            $ilDB->quote($this->supportsOfflineHandling() ? $this->getOfflineStatus() : null, 'integer') . ', ' .
            $ilDB->quote($owner, "integer") . "," .
            $ilDB->now() . "," .
            $ilDB->now() . "," .
            $ilDB->quote($this->getImportId(), "text") . ")";

        $ilDB->manipulate($q);


        // Save long form of description if is rbac object
        if ($objDefinition->isRBACObject($this->getType())) {
            $values = array(
                'obj_id' => array('integer',$this->id),
                'description' => array('clob', $this->getLongDescription()));
            $ilDB->insert('object_description', $values);
        }

        if ($objDefinition->isOrgUnitPermissionType($this->type)) {
            ilOrgUnitGlobalSettings::getInstance()->saveDefaultPositionActivationStatus($this->id);
        }

        // the line ($this->read();) messes up meta data handling: meta data,
        // that is not saved at this time, gets lost, so we query for the dates alone
        //$this->read();
        $q = "SELECT last_update, create_date FROM object_data" .
             " WHERE obj_id = " . $ilDB->quote($this->id, "integer");
        $obj_set = $ilDB->query($q);
        $obj_rec = $ilDB->fetchAssoc($obj_set);
        $this->last_update = $obj_rec["last_update"];
        $this->create_date = $obj_rec["create_date"];

        // set owner for new objects
        $this->setOwner($owner);

        // write log entry
        $ilLog->write("ilObject::create(), finished, obj_id: " . $this->id . ", type: " .
            $this->type . ", title: " . $this->getTitle());

        $app_event->raise(
            'Services/Object',
            'create',
            array('obj_id' => $this->id,'obj_type' => $this->type)
        );

        return $this->id;
    }

    /**
    * update object in db
    *
    * @access	public
    * @return	boolean	true on success
    */
    public function update()
    {
        global $DIC;

        $app_event = $DIC->event();

        $objDefinition = $this->objDefinition;
        $ilDB = $this->db;

        $q = "UPDATE object_data " .
            "SET " .
            "title = " . $ilDB->quote($this->getTitle(), "text") . "," .
            "description = " . $ilDB->quote($this->getDescription(), "text") . ", " .
            'offline = ' . $ilDB->quote($this->supportsOfflineHandling() ? $this->getOfflineStatus() : null, 'integer') . ', ' .
            "import_id = " . $ilDB->quote($this->getImportId(), "text") . "," .
            "last_update = " . $ilDB->now() . " " .
            "WHERE obj_id = " . $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($q);

        // the line ($this->read();) messes up meta data handling: meta data,
        // that is not saved at this time, gets lost, so we query for the dates alone
        //$this->read();
        $q = "SELECT last_update FROM object_data" .
             " WHERE obj_id = " . $ilDB->quote($this->getId(), "integer");
        $obj_set = $ilDB->query($q);
        $obj_rec = $ilDB->fetchAssoc($obj_set);
        $this->last_update = $obj_rec["last_update"];

        if ($objDefinition->isRBACObject($this->getType())) {
            // Update long description
            $res = $ilDB->query("SELECT * FROM object_description WHERE obj_id = " .
                $ilDB->quote($this->getId(), 'integer'));
            if ($res->numRows()) {
                $values = array(
                    'description' => array('clob',$this->getLongDescription())
                    );
                $ilDB->update('object_description', $values, array('obj_id' => array('integer',$this->getId())));
            } else {
                $values = array(
                    'description' => array('clob',$this->getLongDescription()),
                    'obj_id' => array('integer',$this->getId()));
                $ilDB->insert('object_description', $values);
            }
        }
        $app_event->raise(
            'Services/Object',
            'update',
            array('obj_id' => $this->getId(),
                'obj_type' => $this->getType(),
                'ref_id' => $this->getRefId())
        );

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
    public function MDUpdateListener($a_element)
    {
        global $DIC;

        $app_event = $DIC->event();

        include_once 'Services/MetaData/classes/class.ilMD.php';

        $app_event->raise(
            'Services/Object',
            'update',
            array('obj_id' => $this->getId(),
                'obj_type' => $this->getType(),
                'ref_id' => $this->getRefId())
        );

        switch ($a_element) {
            case 'General':

                // Update Title and description
                $md = new ilMD($this->getId(), 0, $this->getType());
                if (!is_object($md_gen = $md->getGeneral())) {
                    return false;
                }
                $this->setTitle($md_gen->getTitle());

                foreach ($md_gen->getDescriptionIds() as $id) {
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
    public function createMetaData()
    {
        global $DIC;

        include_once 'Services/MetaData/classes/class.ilMDCreator.php';

        $ilUser = $DIC["ilUser"];

        $md_creator = new ilMDCreator($this->getId(), 0, $this->getType());
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
    public function updateMetaData()
    {
        $md = new ilMD($this->getId(), 0, $this->getType());
        $md_gen = $md->getGeneral();
        // BEGIN WebDAV: meta data can be missing sometimes.
        if (!$md_gen instanceof ilMDGeneral) {
            $this->createMetaData();
            $md = new ilMD($this->getId(), 0, $this->getType());
            $md_gen = $md->getGeneral();
        }
        // END WebDAV: meta data can be missing sometimes.
        $md_gen->setTitle($this->getTitle());

        // sets first description (maybe not appropriate)
        $md_des_ids = $md_gen->getDescriptionIds();
        if (count($md_des_ids) > 0) {
            $md_des = $md_gen->getDescription($md_des_ids[0]);
            $md_des->setDescription($this->getLongDescription());
            $md_des->update();
        }
        $md_gen->update();
    }

    /**
    * delete meta data entry
    */
    public function deleteMetaData()
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
    public function updateOwner()
    {
        $ilDB = $this->db;

        $q = "UPDATE object_data " .
            "SET " .
            "owner = " . $ilDB->quote($this->getOwner(), "integer") . ", " .
            "last_update = " . $ilDB->now() . " " .
            "WHERE obj_id = " . $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($q);

        $q = "SELECT last_update FROM object_data" .
             " WHERE obj_id = " . $ilDB->quote($this->getId(), "integer");
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
    public static function _getIdForImportId($a_import_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->setLimit(1, 0);
        $q = "SELECT * FROM object_data WHERE import_id = " . $ilDB->quote($a_import_id, "text") .
            " ORDER BY create_date DESC";
        $obj_set = $ilDB->query($q);

        if ($obj_rec = $ilDB->fetchAssoc($obj_set)) {
            return $obj_rec["obj_id"];
        } else {
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
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM object_reference WHERE obj_id = " .
            $ilDB->quote($a_id, 'integer');

        $res = $ilDB->query($query);
        $ref = array();
        while ($obj_rec = $ilDB->fetchAssoc($res)) {
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
        global $DIC;

        $ilObjDataCache = $DIC["ilObjDataCache"];

        $tit = $ilObjDataCache->lookupTitle($a_id);
        //echo "<br>LOOKING-$a_id-:$tit";
        return $tit;
    }

    /**
     * Lookup offline status using objectDataCache
     *
     * @static
     * @param $a_obj_id
     * @return null | bool
     */
    public static function lookupOfflineStatus($a_obj_id)
    {
        global $DIC;

        return $DIC['ilObjDataCache']->lookupOfflineStatus($a_obj_id);
    }



    /**
    * lookup object owner
    *
    * @param	int		$a_id		object id
    */
    public static function _lookupOwner($a_id)
    {
        global $DIC;

        $ilObjDataCache = $DIC["ilObjDataCache"];

        $owner = $ilObjDataCache->lookupOwner($a_id);
        return $owner;
    }

    public static function _getIdsForTitle($title, $type = '', $partialmatch = false)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = (!$partialmatch)
            ? "SELECT obj_id FROM object_data WHERE title = " . $ilDB->quote($title, "text")
            : "SELECT obj_id FROM object_data WHERE " . $ilDB->like("title", "text", '%' . $title . '%');
        if ($type != '') {
            $query .= " AND type = " . $ilDB->quote($type, "text");
        }

        $result = $ilDB->query($query);

        $object_ids = array();
        while ($row = $ilDB->fetchAssoc($result)) {
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
        global $DIC;

        $ilObjDataCache = $DIC["ilObjDataCache"];

        return $ilObjDataCache->lookupDescription($a_id);
    }

    /**
    * lookup last update
    *
    * @param	int		$a_id		object id
    */
    public static function _lookupLastUpdate($a_id, $a_as_string = false)
    {
        global $DIC;

        $ilObjDataCache = $DIC["ilObjDataCache"];

        if ($a_as_string) {
            return ilDatePresentation::formatDate(new ilDateTime($ilObjDataCache->lookupLastUpdate($a_id), IL_CAL_DATETIME));
        } else {
            return $ilObjDataCache->lookupLastUpdate($a_id);
        }
    }

    /**
    * Get last update for a set of media objects.
    *
    * @param	array
    */
    public static function _getLastUpdateOfObjects($a_objs)
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (!is_array($a_objs)) {
            $a_objs = array($a_objs);
        }
        $types = array();
        $set = $ilDB->query("SELECT max(last_update) as last_update FROM object_data " .
            "WHERE " . $ilDB->in("obj_id", $a_objs, false, "integer") . " ");
        $rec = $ilDB->fetchAssoc($set);

        return ($rec["last_update"]);
    }

    public static function _lookupObjId($a_id)
    {
        global $DIC;

        $ilObjDataCache = $DIC["ilObjDataCache"];

        return (int) $ilObjDataCache->lookupObjId($a_id);
    }

    /**
     * @param $a_ref_id
     * @param int $a_deleted_by
     */
    public static function _setDeletedDate($a_ref_id, $a_deleted_by)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "UPDATE object_reference SET " .
            'deleted = ' . $ilDB->now() . ', ' .
            'deleted_by = ' . $ilDB->quote($a_deleted_by, \ilDBConstants::T_INTEGER) . ' ' .
            "WHERE ref_id = " . $ilDB->quote($a_ref_id, 'integer');
        $res = $ilDB->manipulate($query);
    }

    /**
     * Set deleted date
     * @param int[] $a_ref_ids
     * @param int $a_user_id
     * @return void
     */
    public static function setDeletedDates($a_ref_ids, $a_user_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'UPDATE object_reference SET ' .
            'deleted = ' . $ilDB->now() . ', ' .
            'deleted_by = ' . $ilDB->quote($a_user_id, ilDBConstants::T_INTEGER) . ' ' .
            'WHERE ' . $ilDB->in('ref_id', (array) $a_ref_ids, false, ilDBConstants::T_INTEGER);
        $ilDB->manipulate($query);
        return;
    }

    /**
    * only called in ilObjectGUI::insertSavedNodes
    */
    public static function _resetDeletedDate($a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "UPDATE object_reference SET deleted = " . $ilDB->quote(null, 'timestamp') . ', ' .
            'deleted_by = ' . $ilDB->quote(0, \ilDBConstants::T_INTEGER) . ' ' .
            " WHERE ref_id = " . $ilDB->quote($a_ref_id, 'integer');
        $ilDB->manipulate($query);
    }

    /**
    * only called in ilObjectGUI::insertSavedNodes
    */
    public static function _lookupDeletedDate($a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT deleted FROM object_reference" .
            " WHERE ref_id = " . $ilDB->quote($a_ref_id, "integer");
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
    public static function _writeTitle($a_obj_id, $a_title)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "UPDATE object_data " .
            "SET " .
            "title = " . $ilDB->quote($a_title, "text") . "," .
            "last_update = " . $ilDB->now() . " " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer");

        $ilDB->manipulate($q);
    }

    /**
    * write description to db (static)
    *
    * @param	int		$a_obj_id		object id
    * @param	string	$a_desc			description
    * @access	public
    */
    public static function _writeDescription($a_obj_id, $a_desc)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $objDefinition = $DIC["objDefinition"];


        $desc = ilUtil::shortenText($a_desc, self::DESC_LENGTH, true);

        $q = "UPDATE object_data " .
            "SET " .
            "description = " . $ilDB->quote($desc, "text") . "," .
            "last_update = " . $ilDB->now() . " " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer");

        $ilDB->manipulate($q);

        if ($objDefinition->isRBACObject(ilObject::_lookupType($a_obj_id))) {
            // Update long description
            $res = $ilDB->query("SELECT * FROM object_description WHERE obj_id = " .
                $ilDB->quote($a_obj_id, 'integer'));

            if ($res->numRows()) {
                $values = array(
                    'description' => array('clob',$a_desc)
                    );
                $ilDB->update('object_description', $values, array('obj_id' => array('integer',$a_obj_id)));
            } else {
                $values = array(
                    'description' => array('clob',$a_desc),
                    'obj_id' => array('integer',$a_obj_id));
                $ilDB->insert('object_description', $values);
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
    public static function _writeImportId($a_obj_id, $a_import_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "UPDATE object_data " .
            "SET " .
            "import_id = " . $ilDB->quote($a_import_id, "text") . "," .
            "last_update = " . $ilDB->now() . " " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer");

        $ilDB->manipulate($q);
    }

    /**
    * lookup object type
    *
    * @param	int		$a_id		object id
    */
    public static function _lookupType($a_id, $a_reference = false)
    {
        global $DIC;

        $ilObjDataCache = $DIC["ilObjDataCache"];

        if ($a_reference) {
            return $ilObjDataCache->lookupType($ilObjDataCache->lookupObjId($a_id));
        }
        return $ilObjDataCache->lookupType($a_id);
    }

    /**
    * checks wether object is in trash
    */
    public static function _isInTrash($a_ref_id)
    {
        global $DIC;

        $tree = $DIC->repositoryTree();

        return $tree->isSaved($a_ref_id);
    }

    /**
    * checks wether an object has at least one reference that is not in trash
    */
    public static function _hasUntrashedReference($a_obj_id)
    {
        $ref_ids = ilObject::_getAllReferences($a_obj_id);
        foreach ($ref_ids as $ref_id) {
            if (!ilObject::_isInTrash($ref_id)) {
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
        global $DIC;

        $ilObjDataCache = $DIC["ilObjDataCache"];

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
    public static function _getObjectsDataForType($a_type, $a_omit_trash = false)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT * FROM object_data WHERE type = " . $ilDB->quote($a_type, "text");
        $obj_set = $ilDB->query($q);

        $objects = array();
        while ($obj_rec = $ilDB->fetchAssoc($obj_set)) {
            if ((!$a_omit_trash) || ilObject::_hasUntrashedReference($obj_rec["obj_id"])) {
                $objects[$obj_rec["title"] . "." . $obj_rec["obj_id"]] = array("id" => $obj_rec["obj_id"],
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
     * @todo    role/rbac stuff
     *
     * @param int $a_parent_ref Ref-ID of the parent object
     */
    public function putInTree($a_parent_ref)
    {
        $tree = $this->tree;
        $ilLog = $this->log;
        $ilAppEventHandler = $this->app_event_handler;

        $tree->insertNode($this->getRefId(), $a_parent_ref);

        // write log entry
        $ilLog->write("ilObject::putInTree(), parent_ref: $a_parent_ref, ref_id: " .
            $this->getRefId() . ", obj_id: " . $this->getId() . ", type: " .
            $this->getType() . ", title: " . $this->getTitle());

        $ilAppEventHandler->raise(
            'Services/Object',
            'putObjectInTree',
            array(
                'object' => $this,
                'obj_type' => $this->getType(),
                'obj_id' => $this->getId(),
                'parent_ref_id' => $a_parent_ref,
            )
        );
    }

    /**
    * set permissions of object
    *
    * @param	integer	reference_id of parent object
    * @access	public
    */
    public function setPermissions($a_parent_ref)
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
        global $DIC;

        $rbacadmin = $DIC["rbacadmin"];
        $rbacreview = $DIC["rbacreview"];

        $parent_roles = $rbacreview->getParentRoleIds($a_parent_ref);
        foreach ((array) $parent_roles as $parent_role) {
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
    public function createReference()
    {
        $ilDB = $this->db;
        $ilErr = $this->error;

        if (!isset($this->id)) {
            $message = "ilObject::createNewReference(): No obj_id given!";
            $ilErr->raiseError($message, $ilErr->WARNING);
        }

        $next_id = $ilDB->nextId('object_reference');
        $query = "INSERT INTO object_reference " .
             "(ref_id, obj_id) VALUES (" . $ilDB->quote($next_id, 'integer') . ',' . $ilDB->quote($this->id, 'integer') . ")";
        $ilDB->query($query);

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
    public function countReferences()
    {
        $ilDB = $this->db;
        $ilErr = $this->error;

        if (!isset($this->id)) {
            $message = "ilObject::countReferences(): No obj_id given!";
            $ilErr->raiseError($message, $ilErr->WARNING);
        }

        $query = "SELECT COUNT(ref_id) num FROM object_reference " .
            "WHERE obj_id = " . $ilDB->quote($this->id, 'integer') . " ";
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
     * @access    public
     * @return    boolean    true if object was removed completely; false if only a references was
     *                       removed
     */
    public function delete()
    {
        global $DIC;

        $rbacadmin = $DIC["rbacadmin"];
        $ilLog = $this->log;
        $ilDB = $this->db;
        $ilAppEventHandler = $this->app_event_handler;
        $ilErr = $this->error;

        $remove = false;

        // delete object_data entry
        if ((!$this->referenced) || ($this->countReferences() == 1)) {
            // check type match
            $db_type = ilObject::_lookupType($this->getId());
            if ($this->type != $db_type) {
                $message = "ilObject::delete(): Type mismatch. Object with obj_id: " . $this->id . " " .
                    "was instantiated by type '" . $this->type . "'. DB type is: " . $db_type;

                // write log entry
                $ilLog->write($message);

                // raise error
                $ilErr->raiseError("ilObject::delete(): Type mismatch. (" . $this->type . "/" . $this->id . ")", $ilErr->WARNING);
            }

            $ilAppEventHandler->raise('Services/Object', 'beforeDeletion', array( 'object' => $this ));

            // delete entry in object_data
            $q = "DELETE FROM object_data " .
                "WHERE obj_id = " . $ilDB->quote($this->getId(), "integer");
            $ilDB->manipulate($q);

            // delete long description
            $query = "DELETE FROM object_description WHERE obj_id = " .
                $ilDB->quote($this->getId(), "integer");
            $ilDB->manipulate($query);

            // write log entry
            $ilLog->write("ilObject::delete(), deleted object, obj_id: " . $this->getId() . ", type: " .
                $this->getType() . ", title: " . $this->getTitle());

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

            // BEGIN WebDAV: Delete WebDAV properties
            $query = "DELETE FROM dav_property " .
                "WHERE obj_id = " . $ilDB->quote($this->getId(), 'integer');
            $res = $ilDB->manipulate($query);
            // END WebDAV: Delete WebDAV properties

            include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
            ilECSImport::_deleteByObjId($this->getId());

            include_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php");
            ilAdvancedMDValues::_deleteByObjId($this->getId());

            include_once("Services/Tracking/classes/class.ilLPObjSettings.php");
            ilLPObjSettings::_deleteByObjId($this->getId());

            $remove = true;
        } else {
            // write log entry
            $ilLog->write("ilObject::delete(), object not deleted, number of references: " .
                $this->countReferences() . ", obj_id: " . $this->getId() . ", type: " .
                $this->getType() . ", title: " . $this->getTitle());
        }

        // delete object_reference entry
        if ($this->referenced) {
            include_once "Services/Object/classes/class.ilObjectActivation.php";
            ilObjectActivation::deleteAllEntries($this->getRefId());

            $ilAppEventHandler->raise('Services/Object', 'deleteReference', array( 'ref_id' => $this->getRefId()));

            // delete entry in object_reference
            $query = "DELETE FROM object_reference " .
                "WHERE ref_id = " . $ilDB->quote($this->getRefId(), 'integer');
            $res = $ilDB->manipulate($query);

            // write log entry
            $ilLog->write("ilObject::delete(), reference deleted, ref_id: " . $this->getRefId() .
                ", obj_id: " . $this->getId() . ", type: " .
                $this->getType() . ", title: " . $this->getTitle());

            // DELETE PERMISSION ENTRIES IN RBAC_PA
            // DONE: method overwritten in ilObjRole & ilObjUser.
            // this call only applies for objects in rbac (not usr,role,rolt)
            // TODO: Do this for role templates too
            $rbacadmin->revokePermission($this->getRefId(), 0, false);

            include_once "Services/AccessControl/classes/class.ilRbacLog.php";
            ilRbacLog::delete($this->getRefId());

            // Remove applied didactic template setting
            include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateObjSettings.php';
            ilDidacticTemplateObjSettings::deleteByRefId($this->getRefId());
        }

        // remove conditions
        if ($this->referenced) {
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
    public function initDefaultRoles()
    {
        return array();
    }


    /**
     * Apply template
     * @param int $a_tpl_id
     */
    public function applyDidacticTemplate($a_tpl_id)
    {
        ilLoggerFactory::getLogger('obj')->debug('Applying didactic template with id: ' . (int) $a_tpl_id);
        if ($a_tpl_id) {
            include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateActionFactory.php';
            foreach (ilDidacticTemplateActionFactory::getActionsByTemplateId($a_tpl_id) as $action) {
                $action->setRefId($this->getRefId());
                $action->apply();
            }
        }

        include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateObjSettings.php';
        ilDidacticTemplateObjSettings::assignTemplate($this->getRefId(), $this->getId(), (int) $a_tpl_id);
        return $a_tpl_id ? true : false;
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
        global $DIC;

        $ilDB = $DIC->database();

        if ($a_reference) {
            $q = "SELECT * FROM object_data " .
                 "LEFT JOIN object_reference ON object_reference.obj_id=object_data.obj_id " .
                 "WHERE object_reference.ref_id= " . $ilDB->quote($a_id, "integer");
        } else {
            $q = "SELECT * FROM object_data WHERE obj_id=" . $ilDB->quote($a_id, "integer");
        }

        if ($a_type) {
            $q .= " AND object_data.type = " . $ilDB->quote($a_type, "text");
        }

        $r = $ilDB->query($q);

        return $ilDB->numRows($r) ? true : false;
    }

    // toggle subscription interface
    public function setRegisterMode($a_bool)
    {
        $this->register = (bool) $a_bool;
    }

    // check register status of current user
    // abstract method; overwrite in object type class
    public function isUserRegistered($a_user_id = 0)
    {
        return false;
    }

    public function requireRegistration()
    {
        return $this->register;
    }


    public function getXMLZip()
    {
        return false;
    }
    public function getHTMLDirectory()
    {
        return false;
    }

    /**
    * Get objects by type
    */
    public static function _getObjectsByType($a_obj_type = "", $a_owner = "")
    {
        global $DIC;

        $ilDB = $DIC->database();

        $order = " ORDER BY title";

        // where clause
        if ($a_obj_type) {
            $where_clause = "WHERE type = " .
                $ilDB->quote($a_obj_type, "text");

            if ($a_owner != "") {
                $where_clause .= " AND owner = " . $ilDB->quote($a_owner, "integer");
            }
        }

        $q = "SELECT * FROM object_data " . $where_clause . $order;
        $r = $ilDB->query($q);

        $arr = array();
        if ($ilDB->numRows($r) > 0) {
            while ($row = $ilDB->fetchAssoc($r)) {
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
     * @param array $a_ref_ids
     * @param string $new_type
     * @param bool $show_path
     * @return array
     */
    public static function _prepareCloneSelection($a_ref_ids, $new_type, $show_path = true)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $lng = $DIC->language();
        $objDefinition = $DIC["objDefinition"];

        $query = "SELECT obj_data.title obj_title,path_data.title path_title,child FROM tree " .
            "JOIN object_reference obj_ref ON child = obj_ref.ref_id " .
            "JOIN object_data obj_data ON obj_ref.obj_id = obj_data.obj_id " .
            "JOIN object_reference path_ref ON parent = path_ref.ref_id " .
            "JOIN object_data path_data ON path_ref.obj_id = path_data.obj_id " .
            "WHERE " . $ilDB->in("child", $a_ref_ids, false, "integer") . " " .
            "ORDER BY obj_data.title ";
        $res = $ilDB->query($query);

        if (!$objDefinition->isPlugin($new_type)) {
            $options[0] = $lng->txt('obj_' . $new_type . '_select');
        } else {
            require_once("Services/Repository/classes/class.ilObjectPlugin.php");
            $options[0] = ilObjectPlugin::lookupTxtById($new_type, "obj_" . $new_type . "_select");
        }

        while ($row = $ilDB->fetchObject($res)) {
            if (strlen($title = $row->obj_title) > 40) {
                $title = substr($title, 0, 40) . '...';
            }

            if ($show_path) {
                if (strlen($path = $row->path_title) > 40) {
                    $path = substr($path, 0, 40) . '...';
                }

                $title .= ' (' . $lng->txt('path') . ': ' . $path . ')';
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
    public function cloneObject($a_target_id, $a_copy_id = 0, $a_omit_tree = false)
    {
        global $DIC;

        $objDefinition = $this->objDefinition;
        $ilUser = $DIC["ilUser"];
        $rbacadmin = $DIC["rbacadmin"];
        $ilDB = $this->db;
        $ilAppEventHandler = $this->app_event_handler;
        /**
         * @var $ilAppEventHandler ilAppEventHandler
         */

        $location = $objDefinition->getLocation($this->getType());
        $class_name = ('ilObj' . $objDefinition->getClassName($this->getType()));

        include_once './Services/CopyWizard/classes/class.ilCopyWizardOptions.php';
        $options = ilCopyWizardOptions::_getInstance($a_copy_id);

        if (!$options->isTreeCopyDisabled() && !$a_omit_tree) {
            $title = $this->appendCopyInfo($a_target_id, $a_copy_id);
        } else {
            $title = $this->getTitle();
        }

        // create instance
        include_once($location . "/class." . $class_name . ".php");
        $new_obj = new $class_name(0, false);
        $new_obj->setOwner($ilUser->getId());
        $new_obj->setTitle($title);
        $new_obj->setDescription($this->getLongDescription());
        $new_obj->setType($this->getType());

        // Choose upload mode to avoid creation of additional settings, db entries ...
        $new_obj->create(true);

        if ($this->supportsOfflineHandling()) {
            $new_obj->setOffLineStatus($this->getOfflineStatus());
            $new_obj->update();
        }

        if (!$options->isTreeCopyDisabled() && !$a_omit_tree) {
            ilLoggerFactory::getLogger('obj')->debug('Tree copy is enabled');
            $new_obj->createReference();
            $new_obj->putInTree($a_target_id);
            $new_obj->setPermissions($a_target_id);

            // when copying from personal workspace we have no current ref id
            if ($this->getRefId()) {
                // copy local roles
                $rbacadmin->copyLocalRoles($this->getRefId(), $new_obj->getRefId());
            }
        } else {
            ilLoggerFactory::getLogger('obj')->debug('Tree copy is disabled');
        }

        include_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php');
        ilAdvancedMDValues::_cloneValues($this->getId(), $new_obj->getId());

        // BEGIN WebDAV: Clone WebDAV properties
        $query = "INSERT INTO dav_property (obj_id,node_id,ns,name,value) " .
            "SELECT " . $ilDB->quote($new_obj->getId(), 'integer') . ",node_id,ns,name,value " .
            "FROM dav_property " .
            "WHERE obj_id = " . $ilDB->quote($this->getId(), 'integer');
        $res = $ilDB->manipulate($query);
        // END WebDAV: Clone WebDAV properties

        /** @var \ilObjectCustomIconFactory  $customIconFactory */
        $customIconFactory = $DIC['object.customicons.factory'];
        $customIcon = $customIconFactory->getByObjId($this->getId(), $this->getType());
        $customIcon->copy($new_obj->getId());

        $tile_image = $DIC->object()->commonSettings()->tileImage()->getByObjId($this->getId());
        $tile_image->copy($new_obj->getId());

        $ilAppEventHandler->raise('Services/Object', 'cloneObject', array(
            'object' => $new_obj,
            'cloned_from_object' => $this,
        ));

        return $new_obj;
    }

    /**
     * Prepend Copy info if object with same name exists in that container
     *
     * @access public
     * @param int copy_id
     *
     */
    public function appendCopyInfo($a_target_id, $a_copy_id)
    {
        $tree = $this->tree;

        include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
        $cp_options = ilCopyWizardOptions::_getInstance($a_copy_id);
        if (!$cp_options->isRootNode($this->getRefId())) {
            return $this->getTitle();
        }
        $nodes = $tree->getChilds($a_target_id);

        $title_unique = false;
        require_once 'Modules/File/classes/class.ilObjFileAccess.php';
        $numberOfCopy = 1;
        $handleExtension = ($this->getType() == "file"); // #14883
        $title = ilObjFileAccess::_appendNumberOfCopyToFilename($this->getTitle(), $numberOfCopy, $handleExtension);
        while (!$title_unique) {
            $found = 0;
            foreach ($nodes as $node) {
                if (($title == $node['title']) and ($this->getType() == $node['type'])) {
                    $found++;
                }
            }
            if ($found > 0) {
                $title = ilObjFileAccess::_appendNumberOfCopyToFilename($this->getTitle(), ++$numberOfCopy, $handleExtension);
            } else {
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
    public function cloneDependencies($a_target_id, $a_copy_id)
    {
        include_once './Services/Conditions/classes/class.ilConditionHandler.php' ;
        ilConditionHandler::cloneDependencies($this->getRefId(), $a_target_id, $a_copy_id);

        include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateObjSettings.php';
        $tpl_id = ilDidacticTemplateObjSettings::lookupTemplateId($this->getRefId());
        if ($tpl_id) {
            include_once './Services/Object/classes/class.ilObjectFactory.php';
            $factory = new ilObjectFactory();
            $obj = $factory->getInstanceByRefId($a_target_id, false);
            if ($obj instanceof ilObject) {
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
        $md = new ilMD($this->getId(), 0, $this->getType());
        $md->cloneMD($target_obj->getId(), 0, $target_obj->getType());
        return true;
    }

    /**
     * @param int    $a_ref_id
     * @param int    $a_obj_id
     * @param string $a_size
     * @param string $a_type
     * @param bool   $a_offline
     * @return mixed|string
     */
    public static function getIconForReference(
        int $a_ref_id,
        int $a_obj_id,
        string $a_size,
        string $a_type = '',
        bool $a_offline = false
    ) {
        global $DIC;

        $ilSetting = $DIC->settings();
        $objDefinition = $DIC["objDefinition"];

        if ($a_obj_id == "" && $a_type == "") {
            return "";
        }

        if ($a_type == "") {
            $a_type = ilObject::_lookupType($a_obj_id);
        }

        if ($a_size == "") {
            $a_size = "big";
        }

        if (
            $a_obj_id &&
            $ilSetting->get('custom_icons')
        ) {
            /** @var \ilObjectCustomIconFactory  $customIconFactory */
            $customIconFactory = $DIC['object.customicons.factory'];
            $customIcon = $customIconFactory->getPresenterByObjId((int) $a_obj_id, (string) $a_type);
            if ($customIcon->exists()) {
                $filename = $customIcon->getFullPath();
                return $filename . '?tmp=' . filemtime($filename);
            }
        }
        if ($a_obj_id) {
            $dtpl_icon_factory = ilDidacticTemplateIconFactory::getInstance();
            if ($a_ref_id) {
                $path = $dtpl_icon_factory->getIconPathForReference((int) $a_ref_id);
            } else {
                $path = $dtpl_icon_factory->getIconPathForObject((int) $a_obj_id);
            }
            if ($path) {
                return $path . '?tmp=' . filemtime($path);
            }
        }

        if (!$a_offline) {
            if ($objDefinition->isPluginTypeName($a_type)) {
                if ($objDefinition->getClassName($a_type) != "") {
                    $class_name = "il" . $objDefinition->getClassName($a_type) . 'Plugin';
                    $location = $objDefinition->getLocation($a_type);
                    if (is_file($location . "/class." . $class_name . ".php")) {
                        include_once($location . "/class." . $class_name . ".php");
                        return call_user_func(array($class_name, "_getIcon"), $a_type, $a_size, $a_obj_id);
                    }
                }
                return ilUtil::getImagePath("icon_cmps.svg");
            }

            return ilUtil::getImagePath("icon_" . $a_type . ".svg");
        } else {
            return "./images/icon_" . $a_type . ".svg";
        }
    }

    /**
     * Get icon for repository item.
     *
     * @param    int            object id
     * @param    string        size (big, small, tiny)
     * @param    string        object type
     * @param    boolean        true: offline, false: online
     */
    public static function _getIcon(
        $a_obj_id = "",
        $a_size = "big",
        $a_type = "",
        $a_offline = false
    ) {
        return self::getIconForReference(
            0,
            (int) $a_obj_id,
            (string) $a_size,
            (string) $a_type,
            (bool) $a_offline
        );
    }

    /**
     * Collect deletion dependencies. E.g.
     *
     * @param
     * @return
     */
    public static function collectDeletionDependencies(&$deps, $a_ref_id, $a_obj_id, $a_type, $a_depth = 0)
    {
        global $DIC;

        $objDefinition = $DIC["objDefinition"];
        $tree = $DIC->repositoryTree();

        if ($a_depth == 0) {
            $deps["dep"] = array();
        }

        $deps["del_ids"][$a_obj_id] = $a_obj_id;

        if (!$objDefinition->isPluginTypeName($a_type)) {
            $class_name = "ilObj" . $objDefinition->getClassName($a_type);
            $location = $objDefinition->getLocation($a_type);
            include_once($location . "/class." . $class_name . ".php");
            $odeps = call_user_func(array($class_name, "getDeletionDependencies"), $a_obj_id);
            if (is_array($odeps)) {
                foreach ($odeps as $id => $message) {
                    $deps["dep"][$id][$a_obj_id][] = $message;
                }
            }

            // get deletion dependency of childs
            foreach ($tree->getChilds($a_ref_id) as $c) {
                ilObject::collectDeletionDependencies($deps, $c["child"], $c["obj_id"], $c["type"], $a_depth + 1);
            }
        }

        // delete all dependencies to objects that will be deleted, too
        if ($a_depth == 0) {
            foreach ($deps["del_ids"] as $obj_id) {
                unset($deps["dep"][$obj_id]);
            }
            $deps = $deps["dep"];
        }
    }

    /**
     * Get deletion dependencies
     *
     */
    public static function getDeletionDependencies($a_obj_id)
    {
        return false;
    }

    /**
     * Get long description data
     *
     * @param array $a_obj_ids
     * @return array
     */
    public static function getLongDescriptions(array $a_obj_ids)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $res = $ilDB->query("SELECT * FROM object_description" .
            " WHERE " . $ilDB->in("obj_id", $a_obj_ids, "", "integer"));
        $all = array();
        while ($row = $ilDB->fetchAssoc($res)) {
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
    public static function getAllOwnedRepositoryObjects($a_user_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $objDefinition = $DIC["objDefinition"];

        $all = array();

        // restrict to repository
        $types = array_keys($objDefinition->getSubObjectsRecursively("root"));

        $sql = "SELECT od.obj_id,od.type,od.title FROM object_data od" .
            " JOIN object_reference oref ON(oref.obj_id = od.obj_id)" .
            " JOIN tree ON (tree.child = oref.ref_id)";

        if ($a_user_id) {
            $sql .= " WHERE od.owner = " . $ilDB->quote($a_user_id, "integer");
        } else {
            $sql .= " LEFT JOIN usr_data ud ON (ud.usr_id = od.owner)" .
                " WHERE (od.owner < " . $ilDB->quote(1, "integer") .
                " OR od.owner IS NULL OR ud.login IS NULL)" .
                " AND od.owner <> " . $ilDB->quote(-1, "integer");
        }

        $sql .= " AND " . $ilDB->in("od.type", $types, "", "text") .
            " AND tree.tree > " . $ilDB->quote(0, "integer"); // #12485

        $res = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($res)) {
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
    public static function fixMissingTitles($a_type, array &$a_obj_title_map)
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (!in_array($a_type, array("catr", "crsr", "sess", "grpr", "prgr"))) {
            return;
        }

        // any missing titles?
        $missing_obj_ids = array();
        foreach ($a_obj_title_map as $obj_id => $title) {
            if (!trim($title)) {
                $missing_obj_ids[] = $obj_id;
            }
        }

        if (!sizeof($missing_obj_ids)) {
            return;
        }

        switch ($a_type) {
            case "grpr":
            case "catr":
            case "crsr":
            case "prgr":
                $set = $ilDB->query("SELECT oref.obj_id, od.type, od.title FROM object_data od" .
                    " JOIN container_reference oref ON (od.obj_id = oref.target_obj_id)" .
                    " AND " . $ilDB->in("oref.obj_id", $missing_obj_ids, "", "integer"));
                while ($row = $ilDB->fetchAssoc($set)) {
                    $a_obj_title_map[$row["obj_id"]] = $row["title"];
                }
                break;

            case "sess":
                include_once "Modules/Session/classes/class.ilObjSession.php";
                foreach ($missing_obj_ids as $obj_id) {
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
    public static function _lookupCreationDate($a_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT create_date FROM object_data " .
            " WHERE obj_id = " . $ilDB->quote($a_id, "integer"));
        $rec = $ilDB->fetchAssoc($set);
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
        global $DIC;

        $tree = $DIC->repositoryTree();

        if (!$a_ref_id ||
            !in_array($a_type, array("file", "lm", "wiki"))) {
            return false;
        }

        // find parent container
        $parent_ref_id = $tree->checkForParentType($a_ref_id, "grp");
        if (!$parent_ref_id) {
            $parent_ref_id = $tree->checkForParentType($a_ref_id, "crs");
        }
        if ($parent_ref_id) {
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
    public function getPossibleSubObjects($a_filter = true)
    {
        return $this->objDefinition->getSubObjects($this->type, $a_filter);
    }
} // END class.ilObject
