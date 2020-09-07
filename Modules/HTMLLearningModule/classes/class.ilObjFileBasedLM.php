<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @defgroup ModulesHTMLLearningModule Modules/HTMLLearningModule
 */

require_once "./Services/Object/classes/class.ilObject.php";
//require_once "Services/MetaData/classes/class.ilMDLanguageItem.php";

/**
* File Based Learning Module (HTML) object
*
* @author Alex Killing <alex.killing@gmx.de>
*
* $Id$
*
* @ingroup ModulesHTMLLearningModule
*/
class ilObjFileBasedLM extends ilObject
{
    public $tree;
    
    protected $online; // [bool]

    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        global $DIC;

        // default is offline
        $this->setOfflineStatus(true);

        $this->db = $DIC->database();
        // this also calls read() method! (if $a_id is set)
        $this->type = "htlm";
        parent::__construct($a_id, $a_call_by_reference);
    }


    /**
    * update object data
    *
    * @access	public
    * @return	boolean
    */
    public function update($a_skip_meta = false)
    {
        $ilDB = $this->db;

        if (!$a_skip_meta) {
            $this->updateMetaData();
        }
        parent::update();

        $ilDB->manipulate($q = "UPDATE file_based_lm SET " .
            " startfile = " . $ilDB->quote($this->getStartFile(), "text") . " " .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer"));
        return true;
    }

    /**
    * read object
    */
    public function read()
    {
        $ilDB = $this->db;
        
        parent::read();

        $q = "SELECT * FROM file_based_lm WHERE id = " . $ilDB->quote($this->getId(), "integer");
        $lm_set = $ilDB->query($q);
        $lm_rec = $ilDB->fetchAssoc($lm_set);
        $this->setStartFile((string) $lm_rec["startfile"]);
    }



    /**
    * create file based lm
    */
    public function create($a_skip_meta = false)
    {
        $ilDB = $this->db;

        parent::create();
        $this->createDataDirectory();

        $ilDB->manipulate("INSERT INTO file_based_lm (id, startfile) VALUES " .
            " (" . $ilDB->quote($this->getID(), "integer") . "," .
            $ilDB->quote($this->getStartfile(), "text") . ")");
        if (!$a_skip_meta) {
            $this->createMetaData();
        }
    }

    public function getDataDirectory($mode = "filesystem")
    {
        $lm_data_dir = ilUtil::getWebspaceDir($mode) . "/lm_data";
        $lm_dir = $lm_data_dir . "/lm_" . $this->getId();

        return $lm_dir;
    }

    public function createDataDirectory()
    {
        ilUtil::makeDir($this->getDataDirectory());
    }

    public function getStartFile()
    {
        return $this->start_file;
    }

    public function setStartFile($a_file, $a_omit_file_check = false)
    {
        if ($a_file &&
            (file_exists($this->getDataDirectory() . "/" . $a_file) || $a_omit_file_check)) {
            $this->start_file = $a_file;
        }
    }

    /**
    * Gets the disk usage of the object in bytes.
    *
    * @access	public
    * @return	integer		the disk usage in bytes
    */
    public function getDiskUsage()
    {
        require_once("./Modules/HTMLLearningModule/classes/class.ilObjFileBasedLMAccess.php");
        return ilObjFileBasedLMAccess::_lookupDiskUsage($this->id);
    }



    /**
    * delete object and all related data
    *
    * this method has been tested on may 9th 2004
    * data directory, meta data, file based lm data
    * have been deleted correctly as desired
    *
    * @access	public
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete()
    {
        $ilDB = $this->db;

        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        // Delete meta data
        $this->deleteMetaData();

        // delete file_based_lm record
        $ilDB->manipulate("DELETE FROM file_based_lm WHERE id = " .
            $ilDB->quote($this->getID(), "integer"));

        // delete data directory
        ilUtil::delDir($this->getDataDirectory());

        return true;
    }

    /**
     * Populate by directory. Add a filename to do a special check for
     * ILIAS HTML export files. If the corresponding directory is found
     * within the passed directory path (i.e. "htlm_<id>") this
     * subdirectory is used instead.
     *
     * @param
     * @return
     */
    public function populateByDirectoy($a_dir, $a_filename = "")
    {
        preg_match("/.*htlm_([0-9]*)\.zip/", $a_filename, $match);
        if (is_dir($a_dir . "/htlm_" . $match[1])) {
            $a_dir = $a_dir . "/htlm_" . $match[1];
        }
        ilUtil::rCopy($a_dir, $this->getDataDirectory());
        ilUtil::renameExecutables($this->getDataDirectory());
    }
    
    /**
     * Clone HTML learning module
     *
     * @param int target ref_id
     * @param int copy id
     */
    public function cloneObject($a_target_id, $a_copy_id = 0, $a_omit_tree = false)
    {
        $new_obj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);
        $this->cloneMetaData($new_obj);

        //copy online status if object is not the root copy object
        $cp_options = ilCopyWizardOptions::_getInstance($a_copy_id);

        if (!$cp_options->isRootNode($this->getRefId())) {
            $new_obj->setOfflineStatus($this->getOfflineStatus());
        }

        // copy content
        $new_obj->populateByDirectoy($this->getDataDirectory());

        $new_obj->setStartFile($this->getStartFile());
        $new_obj->update();

        return $new_obj;
    }
}
