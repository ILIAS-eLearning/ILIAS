<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject2.php";

/**
 * Help settings application class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesHelp
 */
class ilObjHelpSettings extends ilObject2
{
    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var ilSetting
     */
    protected $settings;


    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        global $DIC;

        $this->db = $DIC->database();
        $this->settings = $DIC->settings();
    }

    /**
     * Init type
     */
    public function initType()
    {
        $this->type = "hlps";
    }

    /**
     * Create help module
     *
     * @param
     * @return
     */
    public static function createHelpModule()
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $id = $ilDB->nextId("help_module");
        
        $ilDB->manipulate("INSERT INTO help_module " .
            "(id) VALUES (" .
            $ilDB->quote($id, "integer") .
            ")");
        
        return $id;
    }
    
    /**
     * Write help module lm id
     *
     * @param
     * @return
     */
    public static function writeHelpModuleLmId($a_id, $a_lm_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $ilDB->manipulate(
            "UPDATE help_module SET " .
            " lm_id = " . $ilDB->quote($a_lm_id, "integer") .
            " WHERE id = " . $ilDB->quote($a_id, "integer")
        );
    }
    
    
    /**
     * Upload help file
     *
     * @param
     * @return
     */
    public function uploadHelpModule($a_file)
    {
        $id = $this->createHelpModule();
        
        // create and insert object in objecttree
        /*include_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
        $newObj = new ilObjContentObject();
        $newObj->setType("lm");
        $newObj->setTitle("Help Module");
        $newObj->create(true);
        $newObj->createLMTree();*/

        try {
            include_once("./Services/Export/classes/class.ilImport.php");
            $imp = new ilImport();
            $conf = $imp->getConfig("Services/Help");
            $conf->setModuleId($id);
            $new_id = $imp->importObject("", $a_file["tmp_name"], $a_file["name"], "lm", "Modules/LearningModule");
            $newObj = new ilObjLearningModule($new_id, false);

            self::writeHelpModuleLmId($id, $newObj->getId());
        } catch (ilManifestFileNotFoundImportException $e) {
            // old import
            $t = $imp->getTemporaryImportDir();

            // create and insert object in objecttree
            include_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
            $newObj = new ilObjContentObject();
            $newObj->setType("lm");
            $newObj->setTitle("Help Module");
            $newObj->create(true);
            $newObj->createLMTree();

            $mess = $newObj->importFromDirectory($t, false);

            // this should only be true for help modules
            // search the zip file
            $dir = $t;
            $files = ilUtil::getDir($dir);
            foreach ($files as $file) {
                if (is_int(strpos($file["entry"], "__help_")) &&
                    is_int(strpos($file["entry"], ".zip"))) {
                    include_once("./Services/Export/classes/class.ilImport.php");
                    $imp = new ilImport();
                    $imp->getMapping()->addMapping('Services/Help', 'help_module', 0, $id);
                    include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
                    $chaps = ilLMObject::getObjectList($newObj->getId(), "st");
                    foreach ($chaps as $chap) {
                        $chap_arr = explode("_", $chap["import_id"]);
                        $imp->getMapping()->addMapping(
                            'Services/Help',
                            'help_chap',
                            $chap_arr[count($chap_arr) - 1],
                            $chap["obj_id"]
                        );
                    }
                    $imp->importEntity(
                        $dir . "/" . $file["entry"],
                        $file["entry"],
                        "help",
                        "Services/Help",
                        true
                    );
                }
            }

            // delete import directory
            ilUtil::delDir($t);

            self::writeHelpModuleLmId($id, $newObj->getId());
        }


        $GLOBALS['ilAppEventHandler']->raise(
            'Services/Help',
            'create',
            array(
                'obj_id' => $id,
                'obj_type' => 'lm'
            )
        );
    }
    
    /**
     * Get help modules
     *
     * @param
     * @return
     */
    public function getHelpModules()
    {
        $ilDB = $this->db;
        
        $set = $ilDB->query("SELECT * FROM help_module");
        
        $mods = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            if (ilObject::_lookupType($rec["lm_id"]) == "lm") {
                $rec["title"] = ilObject::_lookupTitle($rec["lm_id"]);
                $rec["create_date"] = ilObject::_lookupCreationDate($rec["lm_id"]);
            }
            
            $mods[] = $rec;
        }
        
        return $mods;
    }
    
    /**
     * lookup module title
     *
     * @param
     * @return
     */
    public static function lookupModuleTitle($a_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query(
            "SELECT * FROM help_module " .
            " WHERE id = " . $ilDB->quote($a_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        if (ilObject::_lookupType($rec["lm_id"]) == "lm") {
            return ilObject::_lookupTitle($rec["lm_id"]);
        }
        return "";
    }
    
    /**
     * lookup module lm id
     *
     * @param
     * @return
     */
    public static function lookupModuleLmId($a_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query(
            "SELECT lm_id FROM help_module " .
            " WHERE id = " . $ilDB->quote($a_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return $rec["lm_id"];
    }
    
    /**
     * Delete module
     *
     * @param
     * @return
     */
    public function deleteModule($a_id)
    {
        $ilDB = $this->db;
        $ilSetting = $this->settings;
        
        // if this is the currently activated one, deactivate it first
        if ($a_id == (int) $ilSetting->get("help_module")) {
            $ilSetting->set("help_module", "");
        }
        
        $set = $ilDB->query(
            "SELECT * FROM help_module " .
            " WHERE id = " . $ilDB->quote($a_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);

        // delete learning module
        if (ilObject::_lookupType($rec["lm_id"]) == "lm") {
            include_once("./Modules/LearningModule/classes/class.ilObjLearningModule.php");
            $lm = new ilObjLearningModule($rec["lm_id"], false);
            $lm->delete();
        }
        
        // delete mappings
        include_once("./Services/Help/classes/class.ilHelpMapping.php");
        ilHelpMapping::deleteEntriesOfModule($a_id);
        
        // delete tooltips
        include_once("./Services/Help/classes/class.ilHelp.php");
        ilHelp::deleteTooltipsOfModule($a_id);
        
        // delete help module record
        $ilDB->manipulate("DELETE FROM help_module WHERE " .
            " id = " . $ilDB->quote($a_id, "integer"));
    }

    /**
     * Check if LM is a help LM
     *
     * @param integer $a_lm_id lm id
     * @return bool true/false
     */
    public static function isHelpLM($a_lm_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT id FROM help_module " .
            " WHERE lm_id = " . $ilDB->quote($a_lm_id, "integer")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            return true;
        }
        return false;
    }
}
