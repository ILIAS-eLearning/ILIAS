<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* ILIAS Data Validator & Recovery Tool
*
* @author	Sascha Hofmann <shofmann@databay.de>
* @version	$Id$
*
*/
class ilValidator
{
    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var Logger
     */
    protected $log;

    /**
     * @var ilRbacAdmin
     */
    protected $rbacadmin;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilObjUser
     */
    protected $user;

    protected $media_pool_ids = null;
    
    /**
    * all valid rbac object types
    * @var	string
    */
    public $rbac_object_types = null;

    /**
    * list of object types to exclude from recovering
    * @var	array
    */
    // i added folder due to bug #1860 (even if this will not completely fix it)
    // and the fact, that media pool folders may find their way into
    // the recovery folder (what results in broken pools, if the are deleted)
    // Alex, 2006-07-21
    // I removed file objects from this exclusion list, because file objects
    // can be in the repository tree, and thus can suffer from data
    // inconsistencies as well.
    // Werner, 2007-04-16
    public $object_types_exclude = array("adm","root","mail","usrf","objf","lngf",
        "trac","taxf","auth","rolf","assf","svyf","extt","adve","fold");

    /**
    * set mode
    * @var	array
    */
    public $mode = array(
                        "scan"			=> true,		// gather information about corrupted entries
                        "dump_tree"		=> false,		// dump tree
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
    public $invalid_references = array();

    /**
    * invalid childs (tree entries)
    * @var	array
    */
    public $invalid_childs = array();

    /**
    * missing objects
    * @var	array
    */
    public $missing_objects = array();

    /**
    * unbound objects
    * @var	array
    */
    public $unbound_objects = array();

    /**
    * objects in trash
    * @var	array
    */
    public $deleted_objects = array();

    /**
    * contains missing objects that are rolefolders. found by this::
    * findMissingObjects()' these rolefolders must be removed before any
    * restore operations
    * @var	array
    */
    public $invalid_rolefolders = array();
    
    /**
    * contains correct registrated objects but data are corrupted (experimental)
    * @var	array
    */
    public $invalid_objects = array();
    
    /**
    * true enables scan log
    * @var	boolean
    */
    public $logging = false;
    
    /**
    * contains ilLog object
    * @var	object
    */
    public $scan_log;
    
    public $scan_log_file = "scanlog.log";
    
    public $scan_log_separator = "<!-- scan log start -->";

    /**
    * Constructor
    *
    * @access	public
    * @param	integer	mode
    */
    public function __construct($a_log = false)
    {
        global $DIC;

        $this->obj_definition = $DIC["objDefinition"];
        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        $this->log = $DIC["ilLog"];
        $this->rbacadmin = $DIC->rbac()->admin();
        $this->tree = $DIC->repositoryTree();
        $this->user = $DIC->user();
        $objDefinition = $DIC["objDefinition"];
        $ilDB = $DIC->database();
        
        $this->db =&$ilDB;
        $this->rbac_object_types = "'" . implode("','", $objDefinition->getAllRBACObjects()) . "'";
        $this->rbac_object_types = $objDefinition->getAllRBACObjects();

        if ($a_log === true) {
            $this->logging = true;

            // should be available thru inc.header.php
            // TODO: move log functionality to new class ilScanLog
            include_once "./Services/Logging/classes/class.ilLog.php";
        
            // Delete old scan log
            $this->deleteScanLog();
        
            // create scan log
            include_once './Services/Logging/classes/class.ilLog.php';
            $this->scan_log = new ilLog(CLIENT_DATA_DIR, "scanlog.log");
            $this->scan_log->setLogFormat("");
            $this->writeScanLogLine($this->scan_log_separator);
            $this->writeScanLogLine("\n[Systemscan from " . date("y-m-d H:i]"));
        }
    }
    
    /**
    * get possible ilValidator modes
    * @access	public
    * @return	array modes
    */
    public function getPossibleModes()
    {
        return array_keys($this->mode);
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
    public function setMode($a_mode, $a_value)
    {
        if ((!in_array($a_mode, array_keys($this->mode)) and $a_mode != "all") or !is_bool($a_value)) {
            $this->throwError(INVALID_PARAM, FATAL, DEBUG);
            return false;
        }
        
        if ($a_mode == "all") {
            foreach ($this->mode as $mode => $value) {
                $this->mode[$mode] = $a_value;
            }
        } else {
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
    public function isModeEnabled($a_mode)
    {
        if (!in_array($a_mode, array_keys($this->mode))) {
            $this->throwError(VALIDATER_UNKNOWN_MODE, WARNING, DEBUG);
            return false;
        }
        
        return $this->mode[$a_mode];
    }
    
    public function isLogEnabled()
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
    public function setModeDependencies()
    {
        // DO NOT change the order
        
        if ($this->mode["restore"] === true) {
            $this->mode["scan"] = true;
            $this->mode["purge"] = false;
        }

        if ($this->mode["purge"] === true) {
            $this->mode["scan"] = true;
            $this->mode["restore"] = false;
        }

        if ($this->mode["restore_trash"] === true) {
            $this->mode["scan"] = true;
            $this->mode["purge_trash"] = false;
        }

        if ($this->mode["purge_trash"] === true) {
            $this->mode["scan"] = true;
            $this->mode["restore_trash"] = false;
        }

        if ($this->mode["clean"] === true) {
            $this->mode["scan"] = true;
        }
    }

    /**
     * Performs the validation for each enabled mode.
     * Returns a validation summary for display to the user.
    *
    * @return	string	Validation summary.
    * @access	public
    * @see		this::setMode()
     */
    public function validate()
    {
        $lng = $this->lng;
        
        // The validation summary.
        $summary = "";


        // STEP 1: Scan
        // -------------------
        $summary .= $lng->txt("scanning_system");
        if (!$this->isModeEnabled("scan")) {
            $summary .= $lng->txt("disabled");
        } else {
            $summary .= "<br/>" . $lng->txt("searching_invalid_refs");
            if ($this->findInvalidReferences()) {
                $summary .= count($this->getInvalidReferences()) . " " . $lng->txt("found");
            } else {
                $summary .= $lng->txt("found_none");
            }
            
            $summary .= "<br/>" . $lng->txt("searching_invalid_childs");
            if ($this->findInvalidChilds()) {
                $summary .= count($this->getInvalidChilds()) . " " . $lng->txt("found");
            } else {
                $summary .= $lng->txt("found_none");
            }
            
            $summary .= "<br/>" . $lng->txt("searching_missing_objs");
            if ($this->findMissingObjects()) {
                $summary .= count($this->getMissingObjects()) . " " . $lng->txt("found");
            } else {
                $summary .= $lng->txt("found_none");
            }
        
            $summary .= "<br/>" . $lng->txt("searching_unbound_objs");
            if ($this->findUnboundObjects()) {
                $summary .=  count($this->getUnboundObjects()) . " " . $lng->txt("found");
            } else {
                $summary .= $lng->txt("found_none");
            }
        
            $summary .= "<br/>" . $lng->txt("searching_deleted_objs");
            if ($this->findDeletedObjects()) {
                $summary .= count($this->getDeletedObjects()) . " " . $lng->txt("found");
            } else {
                $summary .= $lng->txt("found_none");
            }

            $summary .= "<br/>" . $lng->txt("searching_invalid_rolfs");
            if ($this->findInvalidRolefolders()) {
                $summary .= count($this->getInvalidRolefolders()) . " " . $lng->txt("found");
            } else {
                $summary .= $lng->txt("found_none");
            }
            
            $summary .= "<br/><br/>" . $lng->txt("analyzing_tree_structure");
            if ($this->checkTreeStructure()) {
                $summary .= $lng->txt("tree_corrupt");
            } else {
                $summary .= $lng->txt("done");
            }
        }
        
        // STEP 2: Dump tree
        // -------------------
        $summary .= "<br /><br />" . $lng->txt("dumping_tree");
        if (!$this->isModeEnabled("dump_tree")) {
            $summary .= $lng->txt("disabled");
        } else {
            $error_count = $this->dumpTree();
            if ($error_count > 0) {
                $summary .= $lng->txt("tree_corrupt");
            } else {
                $summary .= $lng->txt("done");
            }
        }

        // STEP 3: Clean Up
        // -------------------
        $summary .= "<br /><br />" . $lng->txt("cleaning");
        if (!$this->isModeEnabled("clean")) {
            $summary .= $lng->txt("disabled");
        } else {
            $summary .= "<br />" . $lng->txt("removing_invalid_refs");
            if ($this->removeInvalidReferences()) {
                $summary .= strtolower($lng->txt("done"));
            } else {
                $summary .= $lng->txt("nothing_to_remove") . $lng->txt("skipped");
            }
            
            $summary .= "<br />" . $lng->txt("removing_invalid_childs");
            if ($this->removeInvalidChilds()) {
                $summary .= strtolower($lng->txt("done"));
            } else {
                $summary .= $lng->txt("nothing_to_remove") . $lng->txt("skipped");
            }

            $summary .= "<br />" . $lng->txt("removing_invalid_rolfs");
            if ($this->removeInvalidRolefolders()) {
                $summary .= strtolower($lng->txt("done"));
            } else {
                $summary .= $lng->txt("nothing_to_remove") . $lng->txt("skipped");
            }

            // find unbound objects again AFTER cleaning process!
            // This updates the array 'unboundobjects' required for the further steps
            // There might be other objects unbounded now due to removal of object_data/reference entries.
            $this->findUnboundObjects();
        }

        // STEP 4: Restore objects
        $summary .= "<br /><br />" . $lng->txt("restoring");
        
        if (!$this->isModeEnabled("restore")) {
            $summary .= $lng->txt("disabled");
        } else {
            $summary .= "<br />" . $lng->txt("restoring_missing_objs");
            if ($this->restoreMissingObjects()) {
                $summary .= strtolower($lng->txt("done"));
            } else {
                $summary .= $lng->txt("nothing_to_restore") . $lng->txt("skipped");
            }
            
            $summary .= "<br />" . $lng->txt("restoring_unbound_objs");
            if ($this->restoreUnboundObjects()) {
                $summary .= strtolower($lng->txt("done"));
            } else {
                $summary .= $lng->txt("nothing_to_restore") . $lng->txt("skipped");
            }
        }
        
        // STEP 5: Restoring Trash
        $summary .= "<br /><br />" . $lng->txt("restoring_trash");

        if (!$this->isModeEnabled("restore_trash")) {
            $summary .= $lng->txt("disabled");
        } else {
            if ($this->restoreTrash()) {
                $summary .= strtolower($lng->txt("done"));
            } else {
                $summary .= $lng->txt("nothing_to_restore") . $lng->txt("skipped");
            }
        }
        
        // STEP 6: Purging...
        $summary .= "<br /><br />" . $lng->txt("purging");
        
        if (!$this->isModeEnabled("purge")) {
            $summary .= $lng->txt("disabled");
        } else {
            $summary .= "<br />" . $lng->txt("purging_missing_objs");
            if ($this->purgeMissingObjects()) {
                $summary .= strtolower($lng->txt("done"));
            } else {
                $summary .= $lng->txt("nothing_to_purge") . $lng->txt("skipped");
            }

            $summary .= "<br />" . $lng->txt("purging_unbound_objs");
            if ($this->purgeUnboundObjects()) {
                $summary .= strtolower($lng->txt("done"));
            } else {
                $summary .= $lng->txt("nothing_to_purge") . $lng->txt("skipped");
            }
        }

        // STEP 7: Purging trash...
        $summary .= "<br /><br />" . $lng->txt("purging_trash");
        
        if (!$this->isModeEnabled("purge_trash")) {
            $summary .= $lng->txt("disabled");
        } else {
            if ($this->purgeTrash()) {
                $summary .= strtolower($lng->txt("done"));
            } else {
                $summary .= $lng->txt("nothing_to_purge") . $lng->txt("skipped");
            }
        }
        
        // STEP 8: Initialize gaps in tree
        if ($this->isModeEnabled("clean")) {
            $summary .= "<br /><br />" . $lng->txt("cleaning_final");
            if ($this->initGapsInTree()) {
                $summary .= "<br />" . $lng->txt("initializing_gaps") . " " . strtolower($lng->txt("done"));
            }
        }
        
        // check RBAC starts here
        // ...
        
        // le fin
        foreach ($this->mode as $mode => $value) {
            $arr[] = $mode . "[" . (int) $value . "]";
        }
        
        return $summary;
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
    public function findMissingObjects()
    {
        $ilDB = $this->db;
        
        // check mode: analyze
        if ($this->mode["scan"] !== true) {
            return false;
        }
        
        // init
        $this->missing_objects = array();
    
        $this->writeScanLogLine("\nfindMissingObjects:");
        
        // Repair missing objects.
        // We only repair file objects which have an entry in table object_reference.
        // XXX - We should check all references to file objects which don't
        //       have an object_reference. If we can't find any reference to such
        //       a file object, we should repair it too!
        $q = "SELECT object_data.*, ref_id FROM object_data " .
             "LEFT JOIN object_reference ON object_data.obj_id = object_reference.obj_id " .
             "LEFT JOIN tree ON object_reference.ref_id = tree.child " .
             "WHERE tree.child IS NULL " .
             "AND (object_reference.obj_id IS NOT NULL " .
             "    OR object_data.type <> 'file' AND " .
             $ilDB->in('object_data.type', $this->rbac_object_types, false, 'text') .
             ")";
        $r = $this->db->query($q);
        
        while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            #if (!in_array($row->type,$this->object_types_exclude))
            if (!$this->isExcludedFromRecovery($row->type, $row->obj_id)) {
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
        
        $this->filterWorkspaceObjects($this->missing_objects);
        if (count($this->missing_objects) > 0) {
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
    public function findInvalidRolefolders()
    {
        $ilDB = $this->db;
        
        // check mode: analyze
        if ($this->mode["scan"] !== true) {
            return false;
        }
        
        // init
        $this->invalid_rolefolders = array();
        
        $this->writeScanLogLine("\nfindInvalidRolefolders:");

        // find rolfs without reference/tree entry
        $q = "SELECT object_data.*, ref_id FROM object_data " .
             "LEFT JOIN object_reference ON object_data.obj_id = object_reference.obj_id " .
             "LEFT JOIN tree ON object_reference.ref_id = tree.child " .
             "WHERE (object_reference.obj_id IS NULL OR tree.child IS NULL) " .
             "AND object_data.type='rolf'";
        $r = $this->db->query($q);
        
        while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
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
        $q = "SELECT object_data.*, ref_id FROM object_data " .
             "LEFT JOIN object_reference ON object_data.obj_id = object_reference.obj_id " .
             "LEFT JOIN tree ON object_reference.ref_id = tree.child " .
             "WHERE object_reference.ref_id = " . $ilDB->quote(RECOVERY_FOLDER_ID, 'integer') . " " .
             "AND object_data.type='rolf'";
        $r = $this->db->query($q);
        
        while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
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
            
        $this->filterWorkspaceObjects($this->invalid_rolefolders);
        if (count($this->invalid_rolefolders) > 0) {
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
    public function findInvalidRBACEntries()
    {
        $ilDB = $this->db;
        
        // check mode: analyze
        if ($this->mode["scan"] !== true) {
            return false;
        }
        
        // init
        $this->invalid_rbac_entries = array();
        
        $this->writeScanLogLine("\nfindInvalidRBACEntries:");

        $q = "SELECT object_data.*, ref_id FROM object_data " .
             "LEFT JOIN object_reference ON object_data.obj_id = object_reference.obj_id " .
             "LEFT JOIN tree ON object_reference.ref_id = tree.child " .
             "WHERE (object_reference.obj_id IS NULL OR tree.child IS NULL) " .
             "AND object_data.type='rolf'";
        $r = $this->db->query($q);
        
        while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
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
        $q = "SELECT object_data.*, ref_id FROM object_data " .
             "LEFT JOIN object_reference ON object_data.obj_id = object_reference.obj_id " .
             "LEFT JOIN tree ON object_reference.ref_id = tree.child " .
             "WHERE object_reference.ref_id =" . $ilDB->quote(RECOVERY_FOLDER_ID) . " " .
             "AND object_data.type='rolf'";
        $r = $this->db->query($q);
        
        while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
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
            
        $this->filterWorkspaceObjects($this->invalid_rolefolders);
        if (count($this->invalid_rolefolders) > 0) {
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
    public function getMissingObjects()
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
    public function findInvalidReferences()
    {
        $ilDB = $this->db;
        
        // check mode: analyze
        if ($this->mode["scan"] !== true) {
            return false;
        }

        // init
        $this->invalid_references = array();
        
        $this->writeScanLogLine("\nfindInvalidReferences:");
        $q = "SELECT object_reference.* FROM object_reference " .
             "LEFT JOIN object_data ON object_data.obj_id = object_reference.obj_id " .
             "WHERE object_data.obj_id IS NULL " .
             "OR " . $ilDB->in('object_data.type', $this->rbac_object_types, true, 'text');
        $r = $this->db->query($q);

        while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->invalid_references[] = array(
                                            "ref_id"	=> $row->ref_id,
                                            "obj_id"	=> $row->obj_id,
                                            "msg"		=> "Object does not exist."
                                            );
        }

        $this->filterWorkspaceObjects($this->invalid_references);
        if (count($this->invalid_references) > 0) {
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
    public function getInvalidReferences()
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
    public function findInvalidChilds()
    {
        $ilDB = $this->db;
        
        // check mode: analyze
        if ($this->mode["scan"] !== true) {
            return false;
        }

        // init
        $this->invalid_childs = array();

        $this->writeScanLogLine("\nfindInvalidChilds:");

        $q = "SELECT tree.*,object_reference.ref_id FROM tree " .
             "LEFT JOIN object_reference ON tree.child = object_reference.ref_id " .
             "LEFT JOIN object_data ON object_reference.obj_id = object_data.obj_id " .
             "WHERE object_reference.ref_id IS NULL or object_data.obj_id IS NULL";
        $r = $this->db->query($q);
        while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->invalid_childs[] = array(
                                            "child"		=> $row->child,
                                            "ref_id"	=> $row->ref_id,
                                            "msg"		=> "No object found"
                                            );
        }

        if (count($this->invalid_childs) > 0) {
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
    public function getInvalidChilds()
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
    public function findUnboundObjects()
    {
        // check mode: analyze
        if ($this->mode["scan"] !== true) {
            return false;
        }

        // init
        $this->unbound_objects = array();

        $this->writeScanLogLine("\nfindUnboundObjects:");

        $q = "SELECT T1.tree,T1.child,T1.parent," .
                "T2.tree deleted,T2.parent grandparent " .
             "FROM tree T1 " .
             "LEFT JOIN tree T2 ON T2.child=T1.parent " .
             "WHERE (T2.tree!=1 OR T2.tree IS NULL) AND T1.parent!=0";
        $r = $this->db->query($q);
        
        while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            // exclude deleted nodes
            if ($row->deleted === null) {
                $this->unbound_objects[] = array(
                                                "child"			=> $row->child,
                                                "parent"		=> $row->parent,
                                                "tree"			=> $row->tree,
                                                "msg"			=> "No valid parent node found"
                                                );
            }
        }

        if (count($this->unbound_objects) > 0) {
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
    public function findDeletedObjects()
    {
        // check mode: analyze
        if ($this->mode["scan"] !== true) {
            return false;
        }

        // init
        $this->deleted_objects = array();

        $this->writeScanLogLine("\nfindDeletedObjects:");

        // Delete objects, start with the oldest objects first
        $query = "SELECT object_data.*,tree.tree,tree.child,tree.parent,deleted " .
             "FROM object_data " .
             "LEFT JOIN object_reference ON object_data.obj_id=object_reference.obj_id " .
             "LEFT JOIN tree ON tree.child=object_reference.ref_id " .
             " WHERE tree != 1 " .
             " ORDER BY deleted";
        $r = $this->db->query($query);
        
        include_once './Services/Calendar/classes/class.ilDateTime.php';
        while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $tmp_date = new ilDateTime($row->deleted, IL_CAL_DATETIME);
            
            $this->deleted_objects[] = array(
                                            "child"			=> $row->child,
                                            "parent"		=> $row->parent,
                                            "tree"			=> $row->tree,
                                            "type"			=> $row->type,
                                            "title"			=> $row->title,
                                            "desc"			=> $row->description,
                                            "owner"			=> $row->owner,
                                            "deleted"		=> $row->deleted,
                                            "deleted_timestamp"	=> $tmp_date->get(IL_CAL_UNIX),
                                            "create_date"	=> $row->create_date,
                                            "last_update"	=> $row->last_update
                                            );
        }

        if (count($this->deleted_objects) > 0) {
            $this->writeScanLogArray(array(array_keys($this->deleted_objects[0])));
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
    public function getUnboundObjects()
    {
        return $this->unbound_objects;
    }

    /**
    * Gets all object in trash
    *
    * @access	public
    * @return	array	objects in trash
    */
    public function getDeletedObjects()
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
    public function getInvalidRolefolders()
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
    public function removeInvalidReferences($a_invalid_refs = null)
    {
        $ilLog = $this->log;
        $ilDB = $this->db;
        
        // check mode: clean
        if ($this->mode["clean"] !== true) {
            return false;
        }

        $this->writeScanLogLine("\nremoveInvalidReferences:");

        if ($a_invalid_refs === null and isset($this->invalid_references)) {
            $a_invalid_refs =&$this->invalid_references;
        }

        // handle wrong input
        if (!is_array($a_invalid_refs)) {
            $this->throwError(INVALID_PARAM, WARNING, DEBUG);
            return false;
        }
        // no unbound references found. do nothing
        if (count($a_invalid_refs) == 0) {
            $this->writeScanLogLine("none");
            return false;
        }

        /*******************
        removal starts here
        ********************/

        $message = sprintf(
            '%s::removeInvalidReferences(): Started...',
            get_class($this)
        );
        $ilLog->write($message, $ilLog->WARNING);
        
        // to make sure
        $this->filterWorkspaceObjects($a_invalid_refs);

        foreach ($a_invalid_refs as $entry) {
            $query = "DELETE FROM object_reference WHERE ref_id= " . $this->db->quote($entry["ref_id"], 'integer') .
                " AND obj_id = " . $this->db->quote($entry["obj_id"], 'integer') . " ";
            $res = $ilDB->manipulate($query);
    
            $message = sprintf(
                '%s::removeInvalidReferences(): Reference %s removed',
                get_class($this),
                $entry["ref_id"]
            );
            $ilLog->write($message, $ilLog->WARNING);
            
            $this->writeScanLogLine("Entry " . $entry["ref_id"] . " removed");
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
    public function removeInvalidChilds($a_invalid_childs = null)
    {
        $ilLog = $this->log;

        // check mode: clean
        if ($this->mode["clean"] !== true) {
            return false;
        }

        $this->writeScanLogLine("\nremoveInvalidChilds:");

        if ($a_invalid_childs === null and isset($this->invalid_childs)) {
            $a_invalid_childs =&$this->invalid_childs;
        }

        // handle wrong input
        if (!is_array($a_invalid_childs)) {
            $this->throwError(INVALID_PARAM, WARNING, DEBUG);
            return false;
        }

        // no unbound childs found. do nothing
        if (count($a_invalid_childs) == 0) {
            $this->writeScanLogLine("none");
            return false;
        }

        /*******************
        removal starts here
        ********************/

        $message = sprintf(
            '%s::removeInvalidChilds(): Started...',
            get_class($this)
        );
        $ilLog->write($message, $ilLog->WARNING);

        foreach ($a_invalid_childs as $entry) {
            $q = "DELETE FROM tree WHERE child='" . $entry["child"] . "'";
            $this->db->query($q);

            $message = sprintf(
                '%s::removeInvalidChilds(): Entry child=%s removed',
                get_class($this),
                $entry["child"]
            );
            $ilLog->write($message, $ilLog->WARNING);
                
            $this->writeScanLogLine("Entry " . $entry["child"] . " removed");
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
    public function removeInvalidRolefolders($a_invalid_rolefolders = null)
    {
        $ilLog = $this->log;
        
        // check mode: clean
        if ($this->mode["clean"] !== true) {
            return false;
        }

        $this->writeScanLogLine("\nremoveInvalidRolefolders:");

        if ($a_invalid_rolefolders === null and isset($this->invalid_rolefolders)) {
            $a_invalid_rolefolders = $this->invalid_rolefolders;
        }

        // handle wrong input
        if (!is_array($a_invalid_rolefolders)) {
            $this->throwError(INVALID_PARAM, WARNING, DEBUG);
            return false;
        }

        // no invalid rolefolders found. do nothing
        if (count($a_invalid_rolefolders) == 0) {
            $this->writeScanLogLine("none");
            return false;
        }
        
        /*******************
        removal starts here
        ********************/

        $removed = false;
        
        $message = sprintf(
            '%s::removeInvalidRolefolders(): Started...',
            get_class($this)
        );
        $ilLog->write($message, $ilLog->WARNING);
        
        // to make sure
        $this->filterWorkspaceObjects($a_invalid_rolefolders);
        
        foreach ($a_invalid_rolefolders as $rolf) {
            // restore ref_id in case of missing
            if ($rolf["ref_id"] === null) {
                $rolf["ref_id"] = $this->restoreReference($rolf["obj_id"]);

                $this->writeScanLogLine("Created missing reference '" . $rolf["ref_id"] . "' for rolefolder object '" . $rolf["obj_id"] . "'");
            }

            // now delete rolefolder
            $obj_data = ilObjectFactory::getInstanceByRefId($rolf["ref_id"]);
            $obj_data->delete();
            unset($obj_data);
            $removed = true;
            $this->writeScanLogLine("Removed invalid rolefolder '" . $rolf["title"] . "' (id=" . $rolf["obj_id"] . ",ref=" . $rolf["ref_id"] . ") from system");
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
    public function restoreMissingObjects($a_missing_objects = null)
    {
        $rbacadmin = $this->rbacadmin;
        $ilLog = $this->log;
        
        // check mode: restore
        if ($this->mode["restore"] !== true) {
            return false;
        }

        $this->writeScanLogLine("\nrestoreMissingObjects:");

        if ($a_missing_objects === null and isset($this->missing_objects)) {
            $a_missing_objects = $this->missing_objects;
        }

        // handle wrong input
        if (!is_array($a_missing_objects)) {
            $this->throwError(INVALID_PARAM, WARNING, DEBUG);
            return false;
        }

        // no missing objects found. do nothing
        if (count($a_missing_objects) == 0) {
            $this->writeScanLogLine("none");
            return false;
        }
        
        /*******************
        restore starts here
        ********************/

        $restored = false;
        
        $message = sprintf(
            '%s::restoreMissingObjects(): Started...',
            get_class($this)
        );
        $ilLog->write($message, $ilLog->WARNING);
        
        // to make sure
        $this->filterWorkspaceObjects($a_missing_objects);
        
        foreach ($a_missing_objects as $missing_obj) {
            // restore ref_id in case of missing
            if ($missing_obj["ref_id"] === null) {
                $missing_obj["ref_id"] = $this->restoreReference($missing_obj["obj_id"]);

                $this->writeScanLogLine("Created missing reference '" . $missing_obj["ref_id"] . "' for object '" . $missing_obj["obj_id"] . "'");
            }

            // put in tree under RecoveryFolder if not on exclude list
            #if (!in_array($missing_obj["type"],$this->object_types_exclude))
            if (!$this->isExcludedFromRecovery($missing_obj['type'], $missing_obj['obj_id'])) {
                $rbacadmin->revokePermission($missing_obj["ref_id"]);
                $obj_data = ilObjectFactory::getInstanceByRefId($missing_obj["ref_id"]);
                $obj_data->putInTree(RECOVERY_FOLDER_ID);
                $obj_data->setPermissions(RECOVERY_FOLDER_ID);
                unset($obj_data);
                //$tree->insertNode($missing_obj["ref_id"],RECOVERY_FOLDER_ID);
                $restored = true;
                $this->writeScanLogLine("Restored object '" . $missing_obj["title"] . "' (id=" . $missing_obj["obj_id"] . ",ref=" . $missing_obj["ref_id"] . ") in 'Restored objects folder'");
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
    public function restoreReference($a_obj_id)
    {
        $ilLog = $this->log;
        $ilDB = $this->db;

        if (empty($a_obj_id)) {
            $this->throwError(INVALID_PARAM, WARNING, DEBUG);
            return false;
        }
        
        $query = "INSERT INTO object_reference (ref_id,obj_id) " .
            "VALUES (" . $next_id = $ilDB->nextId('object_reference') . "," . $this->db->quote($a_obj_id, 'integer') . " )";
        $res = $ilDB->manipulate($query);

        $message = sprintf(
            '%s::restoreReference(): new reference %s for obj_id %s created',
            get_class($this),
            $next_id,
            $a_obj_id
        );
        $ilLog->write($message, $ilLog->WARNING);

        return $next_id;
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
    public function restoreUnboundObjects($a_unbound_objects = null)
    {
        $ilLog = $this->log;

        // check mode: restore
        if ($this->mode["restore"] !== true) {
            return false;
        }

        $this->writeScanLogLine("\nrestoreUnboundObjects:");

        if ($a_unbound_objects === null and isset($this->unbound_objects)) {
            $a_unbound_objects = $this->unbound_objects;
        }

        // handle wrong input
        if (!is_array($a_unbound_objects)) {
            $this->throwError(INVALID_PARAM, WARNING, DEBUG);
            return false;
        }

        $message = sprintf(
            '%s::restoreUnboundObjects(): Started...',
            get_class($this)
        );
        $ilLog->write($message, $ilLog->WARNING);
        
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
    public function restoreTrash($a_deleted_objects = null)
    {
        $ilLog = $this->log;

        // check mode: restore
        if ($this->mode["restore_trash"] !== true) {
            return false;
        }

        $this->writeScanLogLine("\nrestoreTrash:");
    
        if ($a_deleted_objects === null and isset($this->deleted_objects)) {
            $a_deleted_objects = $this->deleted_objects;
        }

        // handle wrong input
        if (!is_array($a_deleted_objects)) {
            $this->throwError(INVALID_PARAM, WARNING, DEBUG);
            return false;
        }

        $message = sprintf(
            '%s::restoreTrash(): Started...',
            get_class($this)
        );
        $ilLog->write($message, $ilLog->WARNING);
    
        // start restore process
        $restored = $this->restoreDeletedObjects($a_deleted_objects);
        
        if ($restored) {
            $q = "DELETE FROM tree WHERE tree!=1";
            $this->db->query($q);

            $message = sprintf(
                '%s::restoreTrash(): Removed all trees with tree id <> 1',
                get_class($this)
            );
            $ilLog->write($message, $ilLog->WARNING);
        
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
    public function restoreDeletedObjects($a_nodes)
    {
        $tree = $this->tree;
        $rbacadmin = $this->rbacadmin;
        $ilLog = $this->log;
        //vd($a_nodes);exit;
        // handle wrong input
        if (!is_array($a_nodes)) {
            $this->throwError(INVALID_PARAM, WARNING, DEBUG);
            return false;
        }

        // no invalid parents found. do nothing
        if (count($a_nodes) == 0) {
            $this->writeScanLogLine("none");
            return false;
        }

        $message = sprintf(
            '%s::restoreDeletedObjects()): Started...',
            get_class($this)
        );
        $ilLog->write($message, $ilLog->WARNING);

        // first delete all rolefolders
        // don't save rolefolders, remove them
        // TODO process ROLE_FOLDER_ID
        foreach ($a_nodes as $key => $node) {
            if ($node["type"] == "rolf") {
                // delete old tree entries
                $tree->deleteTree($node);

                $obj_data = ilObjectFactory::getInstanceByRefId($node["child"]);
                $obj_data->delete();
                unset($a_nodes[$key]);
            }
        }
        
        // process move
        foreach ($a_nodes as $node) {
            // delete old tree entries
            $tree->deleteTree($node);
            
            $rbacadmin->revokePermission($node["child"]);
            $obj_data = ilObjectFactory::getInstanceByRefId($node["child"]);
            $obj_data->putInTree(RECOVERY_FOLDER_ID);
            $obj_data->setPermissions(RECOVERY_FOLDER_ID);
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
    public function restoreSubTrees($a_nodes)
    {
        $tree = $this->tree;
        $rbacadmin = $this->rbacadmin;
        $ilLog = $this->log;
        
        // handle wrong input
        if (!is_array($a_nodes)) {
            $this->throwError(INVALID_PARAM, WARNING, DEBUG);
            return false;
        }

        // no invalid parents found. do nothing
        if (count($a_nodes) == 0) {
            $this->writeScanLogLine("none");
            return false;
        }
        
        /*******************
        restore starts here
        ********************/

        $subnodes = array();
        $topnode = array();

        $message = sprintf(
            '%s::restoreSubTrees(): Started...',
            get_class($this)
        );
        $ilLog->write($message, $ilLog->WARNING);
        
        // process move subtree
        foreach ($a_nodes as $node) {
            // get node data
            $topnode = $tree->getNodeData($node["child"], $node['tree']);
            
            // don't save rolefolders, remove them
            // TODO process ROLE_FOLDER_ID
            if ($topnode["type"] == "rolf") {
                $rolfObj = ilObjectFactory::getInstanceByRefId($topnode["child"]);
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
        foreach ($subnodes as $key => $subnode) {

            // first paste top_node ...
            $rbacadmin->revokePermission($key);
            $obj_data = ilObjectFactory::getInstanceByRefId($key);
            $obj_data->putInTree(RECOVERY_FOLDER_ID);
            $obj_data->setPermissions(RECOVERY_FOLDER_ID);
            
            $this->writeScanLogLine("Object '" . $obj_data->getId() . "' restored.");

            // ... remove top_node from list ...
            array_shift($subnode);
            
            // ... insert subtree of top_node if any subnodes exist
            if (count($subnode) > 0) {
                foreach ($subnode as $node) {
                    $rbacadmin->revokePermission($node["child"]);
                    $obj_data = ilObjectFactory::getInstanceByRefId($node["child"]);
                    $obj_data->putInTree($node["parent"]);
                    $obj_data->setPermissions($node["parent"]);
                    
                    $this->writeScanLogLine("Object '" . $obj_data->getId() . "' restored.");
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
    public function purgeTrash($a_nodes = null)
    {
        $ilLog = $this->log;

        // check mode: purge_trash
        if ($this->mode["purge_trash"] !== true) {
            return false;
        }

        $this->writeScanLogLine("\npurgeTrash:");
    
        if ($a_nodes === null and isset($this->deleted_objects)) {
            $a_nodes = $this->deleted_objects;
        }
        $message = sprintf(
            '%s::purgeTrash(): Started...',
            get_class($this)
        );
        $ilLog->write($message, $ilLog->WARNING);
        
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
    public function purgeUnboundObjects($a_nodes = null)
    {
        $ilLog = $this->log;
        
        // check mode: purge
        if ($this->mode["purge"] !== true) {
            return false;
        }

        $this->writeScanLogLine("\npurgeUnboundObjects:");

        if ($a_nodes === null and isset($this->unbound_objects)) {
            $a_nodes = $this->unbound_objects;
        }

        $message = sprintf(
            '%s::purgeUnboundObjects(): Started...',
            get_class($this)
        );
        $ilLog->write($message, $ilLog->WARNING);
        
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
    public function purgeMissingObjects($a_nodes = null)
    {
        $ilLog = $this->log;
        
        // check mode: purge
        if ($this->mode["purge"] !== true) {
            return false;
        }

        $this->writeScanLogLine("\npurgeMissingObjects:");

        if ($a_nodes === null and isset($this->missing_objects)) {
            $a_nodes = $this->missing_objects;
        }

        $message = sprintf(
            '%s::purgeMissingObjects(): Started...',
            get_class($this)
        );
        $ilLog->write($message, $ilLog->WARNING);
        
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
    public function purgeObjects($a_nodes)
    {
        $ilLog = $this->log;
        $ilUser = $this->user;

        // Get purge limits
        $count_limit = $ilUser->getPref("systemcheck_count_limit");
        if (!is_numeric($count_limit) || $count_limit < 0) {
            $count_limit = count($a_nodes);
        }
        $timestamp_limit = time();
        $age_limit = $ilUser->getPref("systemcheck_age_limit");
        if (is_numeric($age_limit) && $age_limit > 0) {
            $timestamp_limit -= $age_limit * 60 * 60 * 24;
        }
        $type_limit = $ilUser->getPref("systemcheck_type_limit");
        if ($type_limit) {
            $type_limit = trim($type_limit);
            if (strlen($type_limit) == 0) {
                $type_limit = null;
            }
        }
        
        // handle wrong input
        if (!is_array($a_nodes)) {
            $this->throwError(INVALID_PARAM, WARNING, DEBUG);
            return false;
        }

        // start delete process
        $this->writeScanLogLine("action\tref_id\tobj_id\ttype\telapsed\ttitle");
        $count = 0;
        foreach ($a_nodes as $node) {
            if ($type_limit && $node['type'] != $type_limit) {
                $this->writeScanLogLine(
                    "skip\t" .
                        $node['child'] . "\t\t" . $node['type'] . "\t\t" . $node['title']
                );
                continue;
            }


            $count++;
            if ($count > $count_limit) {
                $this->writeScanLogLine("Stopped purging after " . ($count - 1) . " objects, because count limit was reached: " . $count_limit);
                break;
            }
            if ($node["deleted_timestamp"] > $timestamp_limit) {
                $this->writeScanLogLine("Stopped purging after " . ($count - 1) . " objects, because timestamp limit was reached: " . date("c", $timestamp_limit));
                continue;
            }

            $ref_id = ($node["child"]) ? $node["child"] : $node["ref_id"];
            $node_obj = ilObjectFactory::getInstanceByRefId($ref_id, false);
            
            if ($node_obj === false) {
                $this->invalid_objects[] = $node;
                continue;
            }

            $message = sprintf(
                '%s::purgeObjects(): Removing object (id:%s ref:%s)',
                get_class($this),
                $ref_id,
                $node_obj->getId()
            );
            $ilLog->write($message, $ilLog->WARNING);
        
            $startTime = microtime(true);
            $node_obj->delete();
            ilTree::_removeEntry($node["tree"], $ref_id);
            $endTime = microtime(true);
            
            $this->writeScanLogLine("purged\t" . $ref_id . "\t" . $node_obj->getId() .
                "\t" . $node['type'] . "\t" . round($endTime-$startTime, 1) . "\t" . $node['title']);
        }
        
        $this->findInvalidChilds();
        $this->removeInvalidChilds();

        return true;
    }

    /**
    * Initializes gaps in lft/rgt values of a tree.
    *
    * Depending on the value of the gap property of the tree, this function
    * either closes all gaps in the tree, or equally distributes gaps all over
    * the tree.
    *
    * Wrapper for ilTree::renumber()
    *
    * @access	public
    * @return	boolean false if clean mode disabled
    * @see		ilTree::renumber()
    */
    public function initGapsInTree()
    {
        $tree = $this->tree;
        $ilLog = $this->log;
        
        $message = sprintf(
            '%s::initGapsInTree(): Started...',
            get_class($this)
        );
        $ilLog->write($message, $ilLog->WARNING);

        // check mode: clean
        if ($this->mode["clean"] !== true) {
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
    public function handleErr($error)
    {
        $call_loc = $error->backtrace[count($error->backtrace)-1];
        $num_args = count($call_loc["args"]);

        if ($num_args > 0) {
            foreach ($call_loc["args"] as $arg) {
                $type = gettype($arg);
                
                switch ($type) {
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
                                    "value"	=> "(" . $value . ")"
                                    );
            }
            
            foreach ($arg_list as $arg) {
                $arg_str .= implode("", $arg) . " ";
            }
        }

        $err_msg = "<br/><b>" . $error->getCode() . ":</b> " . $error->getMessage() . " in " . $call_loc["class"] . $call_loc["type"] . $call_loc["function"] . "()" .
                   "<br/>Called from: " . basename($call_loc["file"]) . " , line " . $call_loc["line"] .
                   "<br/>Passed parameters: [" . $num_args . "] " . $arg_str . "<br/>";
        printf($err_msg);
        
        if ($error->getUserInfo()) {
            printf("<br/>Parameter details:");
            echo "<pre>";
            var_dump($call_loc["args"]);
            echo "</pre>";
        }
        
        if ($error->getCode() == FATAL) {
            exit();
        }
    }
    
    public function writeScanLogArray($a_arr)
    {
        if (!$this->isLogEnabled()) {
            return false;
        }
        
        foreach ($a_arr as $entry) {
            $this->scan_log->write(implode("\t", $entry));
        }
    }
    
    public function writeScanLogLine($a_msg)
    {
        if (!$this->isLogEnabled()) {
            return false;
        }
        
        $this->scan_log->write($a_msg);
    }

    /**
     * Quickly determine if there is a scan log
     */
    public function hasScanLog()
    {
        // file check
        return is_file(CLIENT_DATA_DIR . "/" . $this->scan_log_file);
    }

    /**
     * Delete scan log.
     */
    public function deleteScanLog()
    {
        @unlink(CLIENT_DATA_DIR . "/" . $this->scan_log_file);
    }

    public function readScanLog()
    {
        // file check
        if (!$this->hasScanLog()) {
            return false;
        }

        $scanfile =&file(CLIENT_DATA_DIR . "/" . $this->scan_log_file);
        if (!$scan_log =&$this->get_last_scan($scanfile)) {
            return false;
        }
        // Ensure that memory is freed
        unset($scanfile);
        
        return $scan_log;
    }
    
    public function get_last_scan($a_scan_log)
    {
        $logs = array_keys($a_scan_log, $this->scan_log_separator . "\n");
        
        if (count($logs) > 0) {
            return array_slice($a_scan_log, array_pop($logs)+2);
        }
        
        return false;
    }
    
    public function checkTreeStructure($a_startnode = null)
    {
        $tree = $this->tree;

        $this->writeScanLogLine("\nchecking tree structure is disabled");
        
        return false;
    }

    /**
    * Dumps the Tree structure into the scan log
    *
    * @access	public
    * @return number of errors found while dumping tree
    */
    public function dumpTree()
    {
        $ilDB = $this->db;
        
        $this->writeScanLogLine("BEGIN dumpTree:");

        // collect nodes with duplicate child Id's
        // (We use this, to mark these nodes later in the output as being
        // erroneous.).
        $q = 'SELECT child FROM tree GROUP BY child HAVING COUNT(*) > 1';
        $r = $this->db->query($q);
        $duplicateNodes = array();
        while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $duplicateNodes[] = $row->child;
        }
        
        // dump tree
        $q = "SELECT tree.*,ref.ref_id,dat.obj_id objobj_id,ref.obj_id refobj_id,ref.deleted,dat.* "
            . "FROM tree "
            . "RIGHT JOIN object_reference ref ON tree.child = ref.ref_id "
            . "RIGHT JOIN object_data dat ON ref.obj_id = dat.obj_id "
//			."LEFT JOIN usr_data usr ON usr.usr_id = dat.owner "
            . "ORDER BY tree, lft, type, dat.title";
        $r = $this->db->query($q);
        
        $this->writeScanLogLine(
            '<table><tr>'
            . '<td>tree, child, parent, lft, rgt, depth</td>'
            . '<td>ref_id, ref.obj_id, deleted</td>'
            . '<td>obj_id, type, owner, title</td>'
            . '</tr>'
        );
        
        // We use a stack to represent the path to the current node.
        // This allows us to do analyze the tree structure without having
        // to implement a recursive algorithm.
        $stack = array();
        $error_count = 0;
        $repository_tree_count = 0;
        $trash_trees_count = 0;
        $other_trees_count = 0;
        $not_in_tree_count = 0;

        // The previous number is used for gap checking
        $previousNumber = 0;
        
        $this->initWorkspaceObjects();
        
        while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            // workspace objects are not to be processed
            if ($this->workspace_object_ids &&
                in_array($row->objobj_id, $this->workspace_object_ids)) {
                continue;
            }
            
            // If there is no entry in table tree for the object, we display it here
            if (is_null($row->child)) {
                switch ($row->type) {
                    case 'crsg':
                    case 'usr':
                    case 'typ':
                    case 'lng':
                    case 'rolt':
                    case 'role':
                    case 'mob':
                    case 'sty':
                    case 'tax': // #13798
                        // We are not interested in dumping these object types.
                        continue 2;
                        //break; NOT REACHED
                    case 'file':
                        if (is_null($row->ref_id)) {
                            // File objects can be part of a learning module.
                            // In this case, they do not have a row in table object_reference.
                            // We are not interested in dumping these file objects.
                            continue 2;
                        } else {
                            // File objects which have a row in table object_reference, but
                            // none in table tree are an error.
                            $error_count++;
                            $isRowOkay = false;
                            $isParentOkay = false;
                            $isLftOkay = false;
                            $isRgtOkay = false;
                            $isDepthOkay = false;
                        }
                        break;
                        
                        
                    case 'fold':
                        // ignore folders on media pools
                        if ($this->isMediaFolder($row->obj_id)) {
                            continue 2;
                        }
                        // no break
                    default:
                        $error_count++;
                        $isRowOkay = false;
                        $isParentOkay = false;
                        $isLftOkay = false;
                        $isRgtOkay = false;
                        $isDepthOkay = false;
                        break;
                }

                // moved here (below continues in switch)
                $not_in_tree_count++;
                
                $this->writeScanLogLine(
                    '<tr>'
                    . '<td>'
                    . (($isRowOkay) ? '' : '<font color=#ff0000>')
                    . $row->tree . ', '
                    . $row->child . ', '
                    . (($isParentOkay) ? '' : 'parent:<b>')
                    . $row->parent
                    . (($isParentOkay) ? '' : '</b>')
                    . ', '
                    . (($isLftOkay) ? '' : 'lft:<b>')
                    . $row->lft
                    . (($isLftOkay) ? '' : '</b>')
                    . ', '
                    . (($isRgtOkay) ? '' : 'rgt:<b>')
                    . $row->rgt
                    . (($isRgtOkay) ? '' : '</b>')
                    . ', '
                    . (($isDepthOkay) ? '' : 'depth:<b>')
                    . $row->depth
                    . (($isDepthOkay) ? '' : '</b>')
                    . (($isRowOkay) ? '' : '</font>')
                    . '</td><td>'
                    . (($isRowOkay) ? '' : '<font color=#ff0000>')
                    . (($isRefRefOkay && $isChildOkay) ? '' : 'ref.ref_id:<b>')
                    . $row->ref_id
                    . (($isRefRefOkay && $isChildOkay) ? '' : '</b>')
                    . ', '
                    . (($isRefObjOkay) ? '' : 'ref.obj_id:<b>')
                    . $row->refobj_id
                    . (($isRefObjOkay) ? '' : '</b>')
                    . (($isRowOkay) ? '' : '<font color=#ff0000>')
                    . (($row->deleted != null) ? ', ' . $row->deleted : '')
                    . '</td><td>'
                    . (($isRowOkay) ? '' : '<font color=#ff0000>')
                    . $indent
                    . $row->obj_id . ', '
                    . $row->type . ', '
                    . $row->login . ', '
                    . $row->title
                    . (($isRowOkay) ? '' : ' <b>*ERROR*</b><font color=#ff0000>')
                    . '</td>'
                    . '</tr>'
                );
                continue;
            }
        
            // Update stack
            // -------------------
            $indent = "";
            for ($i = 1; $i < $row->depth; $i++) {
                $indent .= ". ";
            }
            
            // Initialize the stack and the previous number if we are in a new tree
            if (count($stack) == 0 || $stack[0]->tree != $row->tree) {
                $stack = array();
                $previousNumber = $row->lft - 1;
                $this->writeScanLogLine('<tr><td>&nbsp;</td></tr>');
            }
            // Pop old stack entries
            
            
            while (count($stack) > 0 && $stack[count($stack) - 1]->rgt < $row->lft) {
                $popped = array_pop($stack);

                // check for gap
                $gap = $popped->rgt - $previousNumber - 1;
                if ($gap > 0) {
                    $poppedIndent = "";
                    for ($i = 1; $i < $popped->depth; $i++) {
                        $poppedIndent .= ". ";
                    }
                    $this->writeScanLogLine(
                        '<tr>'
                        . '<td colspan=2><div align="right">'
                        . '<font color=#00cc00>*gap* for ' . ($gap/2) . ' nodes at end of&nbsp;</font>'
                        . '</div></td>'
                        . '<td>'
                        . '<font color=#00cc00>'
                        . $poppedIndent
                        . $popped->obj_id . ', '
                        . $popped->type . ', '
                        . $popped->login . ', '
                        . $popped->title
                        . '</font>'
                        . '</td>'
                        . '</tr>'
                    );
                }
                $previousNumber = $popped->rgt;
                unset($popped);
            }

            // Check row integrity
            // -------------------
            $isRowOkay = true;
            
            // Check tree structure
            $isChildOkay = true;
            $isParentOkay = true;
            $isLftOkay = true;
            $isRgtOkay = true;
            $isDepthOkay = true;
            $isGap = false;
            
            if (count($stack) > 0) {
                $parent = $stack[count($stack) - 1];
                if ($parent->depth + 1 != $row->depth) {
                    $isDepthOkay = false;
                    $isRowOkay = false;
                }
                if ($parent->child != $row->parent) {
                    $isParentOkay = false;
                    $isRowOkay = false;
                }
                if ($GLOBALS['ilSetting']->get('main_tree_impl', 'ns') == 'ns') {
                    if ($parent->lft >= $row->lft) {
                        $isLftOkay = false;
                        $isRowOkay = false;
                    }
                    if ($parent->rgt <= $row->rgt) {
                        $isRgtOkay = false;
                        $isRowOkay = false;
                    }
                }
            }

            // Check lft rgt
            if ($GLOBALS['ilSetting']->get('main_tree_impl', 'ns') == 'ns') {
                if ($row->lft >= $row->rgt) {
                    $isLftOkay = false;
                    $isRgtOkay = false;
                    $isRowOkay = false;
                }
            }
            if (in_array($row->child, $duplicateNodes)) {
                $isChildOkay = false;
                $isRowOkay = false;
            }
            
            // Check object reference
            $isRefRefOkay = true;
            $isRefObjOkay = true;
            if ($row->ref_id == null) {
                $isRefRefOkay = false;
                $isRowOkay = false;
            }
            if ($row->obj_id == null) {
                $isRefObjOkay = false;
                $isRowOkay = false;
            }
            
            if (!$isRowOkay) {
                $error_count++;
            }

            // Check for gap between siblings,
            // and eventually write a log line
            if ($GLOBALS['ilSetting']->get('main_tree_impl', 'ns') == 'ns') {
                $gap = $row->lft - $previousNumber - 1;
                $previousNumber = $row->lft;
                if ($gap > 0) {
                    $this->writeScanLogLine(
                        '<tr>'
                        . '<td colspan=2><div align="right">'
                        . '<font color=#00cc00>*gap* for ' . ($gap/2) . ' nodes between&nbsp;</font>'
                        . '</div></td>'
                        . '<td>'
                        . '<font color=#00cc00>siblings</font>'
                        . '</td>'
                        . '</tr>'
                    );
                }
            }
                        
            // Write log line
            // -------------------
            $this->writeScanLogLine(
                '<tr>'
                    . '<td>'
                    . (($isRowOkay) ? '' : '<font color=#ff0000>')
                    . $row->tree . ', '
                    . $row->child . ', '
                    . (($isParentOkay) ? '' : 'parent:<b>')
                    . $row->parent
                    . (($isParentOkay) ? '' : '</b>')
                    . ', '
                    . (($isLftOkay) ? '' : 'lft:<b>')
                    . $row->lft
                    . (($isLftOkay) ? '' : '</b>')
                    . ', '
                    . (($isRgtOkay) ? '' : 'rgt:<b>')
                    . $row->rgt
                    . (($isRgtOkay) ? '' : '</b>')
                    . ', '
                    . (($isDepthOkay) ? '' : 'depth:<b>')
                    . $row->depth
                    . (($isDepthOkay) ? '' : '</b>')
                    . (($isRowOkay) ? '' : '</font>')
                    . '</td><td>'
                    . (($isRowOkay) ? '' : '<font color=#ff0000>')
                    . (($isRefRefOkay && $isChildOkay) ? '' : 'ref.ref_id:<b>')
                    . $row->ref_id
                    . (($isRefRefOkay && $isChildOkay) ? '' : '</b>')
                    . ', '
                    . (($isRefObjOkay) ? '' : 'ref.obj_id:<b>')
                    . $row->refobj_id
                    . (($isRefObjOkay) ? '' : '</b>')
                    . (($isRowOkay) ? '' : '<font color=#ff0000>')
                    . (($row->tree < 0) ? ', ' . $row->deleted : '')
                    . '</td><td>'
                    . (($isRowOkay) ? '' : '<font color=#ff0000>')
                    . $indent
                    . $row->obj_id . ', '
                    . $row->type . ', '
                    . $row->login . ', '
                    . $row->title
                    . (($isRowOkay) ? '' : ' <b>*ERROR*</b><font color=#ff0000>')
                    . '</td>'
                    . '</tr>'
            );

            // Update stack
            // -------------------
            // Push node on stack
            $stack[] = $row;

            // Count nodes
            // -----------------
            if ($row->tree == 1) {
                $repository_tree_count++;
            } elseif ($row->tree < 0) {
                $trash_trees_count++;
            } else {
                $other_trees_count++;
            }
        }
        //
        // Pop remaining stack entries

        while (count($stack) > 0) {
            $popped = array_pop($stack);

            // check for gap
            $gap = $popped->rgt - $previousNumber - 1;
            if ($gap > 0) {
                $poppedIndent = "";
                for ($i = 1; $i < $popped->depth; $i++) {
                    $poppedIndent .= ". ";
                }
                $this->writeScanLogLine(
                    '<tr>'
                    . '<td colspan=2><div align="right">'
                    . '<font color=#00cc00>*gap* for ' . ($gap/2) . ' nodes at end of&nbsp;</font>'
                    . '</div></td>'
                    . '<td>'
                    . '<font color=#00cc00>'
                    . $poppedIndent
                    . $popped->obj_id . ', '
                    . $popped->type . ', '
                    . $popped->login . ', '
                    . $popped->title
                    . '</font>'
                    . '</td>'
                    . '</tr>'
                );
            }
            $previousNumber = $popped->rgt;
            unset($popped);
        }
        
        //
        $this->writeScanLogLine("</table>");

        if ($error_count > 0) {
            $this->writeScanLogLine('<font color=#ff0000>' . $error_count . ' errors found while dumping tree.</font>');
        } else {
            $this->writeScanLogLine('No errors found while dumping tree.');
        }
        $this->writeScanLogLine("$repository_tree_count nodes in repository tree");
        $this->writeScanLogLine("$trash_trees_count nodes in trash trees");
        $this->writeScanLogLine("$other_trees_count nodes in other trees");
        $this->writeScanLogLine("$not_in_tree_count nodes are not in a tree");
        $this->writeScanLogLine("END dumpTree");
        
        return $error_count;
    }
    
    protected function isMediaFolder($a_obj_id)
    {
        $ilDB = $this->db;
        
        if (!is_array($this->media_pool_ids)) {
            $this->media_pool_ids = array();
            $query = "SELECT child FROM mep_tree ";
            $res = $ilDB->query($query);
            while ($row = $ilDB->fetchObject($res)) {
                $this->media_pool_ids[] = $row->child;
            }
        }

        return in_array($a_obj_id, $this->media_pool_ids) ? true : false;
    }
    
    /**
     * Check if type is excluded from recovery
     * @param string $a_type
     * @param int	$a_obj_id
     * @return bool
     */
    protected function isExcludedFromRecovery($a_type, $a_obj_id)
    {
        switch ($a_type) {
            case 'fold':
                if (!$this->isMediaFolder($a_obj_id)) {
                    return false;
                }
        }
        return in_array($a_type, $this->object_types_exclude);
    }
    
    protected function initWorkspaceObjects()
    {
        $ilDB = $this->db;
        
        if ($this->workspace_object_ids === null) {
            $this->workspace_object_ids = array();
            
            // workspace objects
            $set = $ilDB->query("SELECT DISTINCT(obj_id) FROM object_reference_ws");
            while ($row = $ilDB->fetchAssoc($set)) {
                $this->workspace_object_ids[] = $row["obj_id"];
            }
            
            // portfolios
            $set = $ilDB->query("SELECT id FROM usr_portfolio");
            while ($row = $ilDB->fetchAssoc($set)) {
                $this->workspace_object_ids[] = $row["id"];
            }
        }
    }
    
    protected function filterWorkspaceObjects(array &$a_data, $a_index = "obj_id")
    {
        if (sizeof($a_data)) {
            $this->initWorkspaceObjects();
            
            // remove workspace objects from result objects
            if (is_array($this->workspace_object_ids)) {
                foreach ($a_data as $idx => $item) {
                    if (in_array($item[$a_index], $this->workspace_object_ids)) {
                        unset($a_data[$idx]);
                    }
                }
            }
        }
    }
} // END class.ilValidator
