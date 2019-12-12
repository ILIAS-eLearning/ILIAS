<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Tracking/classes/class.ilLPObjSettings.php";

/**
 * Base class for object lp connectors
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilLPStatusPlugin.php 43734 2013-07-29 15:27:58Z jluetzen $
 * @package ServicesTracking
 */
class ilObjectLP
{
    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilDB
     */
    protected $db;

    protected $obj_id; // [int]
    protected $collection_instance; // [ilLPCollection]
    protected $mode; // [int]
    
    protected static $type_defaults; // [array]
    
    protected function __construct($a_obj_id)
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->db = $DIC->database();
        $this->obj_id = (int) $a_obj_id;
    }
    
    public static function getInstance($a_obj_id)
    {
        static $instances = array();
        
        if (!isset($instances[$a_obj_id])) {
            $type = ilObject::_lookupType($a_obj_id);
            $class = self::getTypeClass($type);
            if ($class) {
                $instance = new $class($a_obj_id);
            } else {
                // :TODO: should we return anything?
                $instance = new self($a_obj_id);
            }
            $instances[$a_obj_id] = $instance;
        }
    
        return $instances[$a_obj_id];
    }
            
    public static function getTypeClass($a_type)
    {
        global $DIC;

        $objDefinition = $DIC["objDefinition"];
        
        if (self::isSupportedObjectType($a_type)) {
            switch ($a_type) {
                // container

                case "crs":
                    include_once "Modules/Course/classes/class.ilCourseLP.php";
                    return "ilCourseLP";

                case "grp":
                    include_once "Modules/Group/classes/class.ilGroupLP.php";
                    return "ilGroupLP";

                case "fold":
                    include_once "Modules/Folder/classes/class.ilFolderLP.php";
                    return "ilFolderLP";

                case "lso":
                    include_once "Modules/LearningSequence/classes/LearnerProgress/class.ilLSLP.php";
                    return "ilLSLP";


                // learning resources

                case "lm":
                    include_once "Modules/LearningModule/classes/class.ilLearningModuleLP.php";
                    return "ilLearningModuleLP";

                case "htlm":
                    include_once "Modules/HTMLLearningModule/classes/class.ilHTMLLearningModuleLP.php";
                    return "ilHTMLLearningModuleLP";

                case "sahs":
                    include_once "Modules/ScormAicc/classes/class.ilScormLP.php";
                    return "ilScormLP";
                    

                // misc

                case "tst":
                    include_once "Modules/Test/classes/class.ilTestLP.php";
                    return "ilTestLP";

                case "exc":
                    include_once "Modules/Exercise/classes/class.ilExerciseLP.php";
                    return "ilExerciseLP";
                    
                case 'file':
                    require_once 'Modules/File/classes/class.ilFileLP.php';
                    return 'ilFileLP';
                    
                case "mcst":
                    require_once "Modules/MediaCast/classes/class.ilMediaCastLP.php";
                    return "ilMediaCastLP";
            
                case "sess":
                    include_once "Modules/Session/classes/class.ilSessionLP.php";
                    return  "ilSessionLP";
                    
                case "svy":
                    include_once "Modules/Survey/classes/class.ilSurveyLP.php";
                    return  "ilSurveyLP";

                case "prg":
                    include_once "Modules/StudyProgramme/classes/class.ilStudyProgrammeLP.php";
                    return "ilStudyProgrammeLP";

                case "iass":
                    include_once "Modules/IndividualAssessment/classes/class.ilIndividualAssessmentLP.php";
                    return "ilIndividualAssessmentLP";

                case "copa":
                    return "ilContentPageLP";

                // plugin
                case $objDefinition->isPluginTypeName($a_type):
                    include_once "Services/Component/classes/class.ilPluginLP.php";
                    return "ilPluginLP";
            }
        }
    }
    public static function isSupportedObjectType($a_type)
    {
        global $DIC;

        $objDefinition = $DIC["objDefinition"];

        $valid = array("crs", "grp", "fold", "lm", "htlm", "sahs", "tst", "exc",
            "sess", "svy", "file", "mcst", "prg", "iass", "copa", "lso");

        if (in_array($a_type, $valid)) {
            return true;
        }
        
        if ($objDefinition->isPluginTypeName($a_type)) {
            include_once 'Services/Repository/classes/class.ilRepositoryObjectPluginSlot.php';
            return ilRepositoryObjectPluginSlot::isTypePluginWithLP($a_type);
        }
        
        return false;
    }
        
    public function resetCaches()
    {
        $this->mode = null;
        $this->collection_instance = null;
    }
    
    public function isAnonymized()
    {
        // see ilLPCollectionOfRepositoryObjects::validateEntry()
        return false;
    }
    
    
    //
    // MODE
    //
    
    public function getDefaultMode()
    {
        return ilLPObjSettings::LP_MODE_UNDEFINED;
    }
    
    public function getValidModes()
    {
        return array();
    }
    
    public function getCurrentMode()
    {
        if ($this->mode === null) {
            // using global type default if LP is inactive
            include_once "Services/Tracking/classes/class.ilObjUserTracking.php";
            if (!ilObjUserTracking::_enabledLearningProgress()) {
                $mode = self::getTypeDefaultFromDB(ilObject::_lookupType($this->obj_id));
                if ($mode === null) {
                    // fallback: inactive as type default may not be suitable
                    $mode = ilLPObjSettings::LP_MODE_DEACTIVATED;
                }
            }
            // use object LP setting
            else {
                $mode = ilLPObjSettings::_lookupDBMode($this->obj_id);
                if ($mode === null) {
                    // fallback: object type default
                    $mode = $this->getDefaultMode();
                }
            }
            $this->mode = (int) $mode;
        }
        
        return $this->mode;
    }
    
    public function isActive()
    {
        // :TODO: check LP activation?
        
        $mode = $this->getCurrentMode();
        if ($mode == ilLPObjSettings::LP_MODE_DEACTIVATED ||
            $mode == ilLPObjSettings::LP_MODE_UNDEFINED) {
            return false;
        }
        return true;
    }
    
    public function getModeText($a_mode)
    {
        return ilLPObjSettings::_mode2Text($a_mode);
    }
    
    public function getModeInfoText($a_mode)
    {
        return ilLPObjSettings::_mode2InfoText($a_mode);
    }
    
    public function getSettingsInfo()
    {
        // type-specific
    }
    
    
    //
    // COLLECTION
    //
        
    public function getCollectionInstance()
    {
        if ($this->collection_instance === null) {
            include_once "Services/Tracking/classes/collection/class.ilLPCollection.php";
            $this->collection_instance = ilLPCollection::getInstanceByMode($this->obj_id, $this->getCurrentMode());
        }
        
        return $this->collection_instance;
    }
            
    
    //
    // MEMBERS
    //
    
    public function getMembers($a_search = true)
    {
        $tree = $this->tree;
        
        if (!$a_search) {
            return;
        }
        
        $ref_ids = ilObject::_getAllReferences($this->obj_id);
        $ref_id = current($ref_ids);
        
        // walk path to find parent with specific members
        $path = $tree->getPathId($ref_id);
        array_pop($path);
        foreach (array_reverse($path) as $path_ref_id) {
            $olp = self::getInstance(ilObject::_lookupObjId($path_ref_id));
            $all = $olp->getMembers(false);
            if (is_array($all)) {
                return $all;
            }
        }
    }
    
    
    //
    // RESET
    //
    
    final public function resetLPDataForCompleteObject($a_recursive = true)
    {
        $user_ids = $this->gatherLPUsers();
        if (sizeof($user_ids)) {
            $this->resetLPDataForUserIds(array_unique($user_ids), $a_recursive);
        }
    }
    
    final public function resetLPDataForUserIds(array $a_user_ids, $a_recursive = true)
    {
        if ((bool) $a_recursive &&
            method_exists($this, "getPossibleCollectionItems")) { // #15203
            $subitems = $this->getPossibleCollectionItems();
            if (is_array($subitems)) {
                foreach ($subitems as $sub_ref_id) {
                    $olp = self::getInstance(ilObject::_lookupObjId($sub_ref_id));
                    $olp->resetLPDataForUserIds($a_user_ids, false);
                }
            }
        }
        
        $this->resetCustomLPDataForUserIds($a_user_ids, (bool) $a_recursive);
                        
        include_once "Services/Tracking/classes/class.ilLPMarks.php";
        ilLPMarks::_deleteForUsers($this->obj_id, $a_user_ids);

        include_once "Services/Tracking/classes/class.ilChangeEvent.php";
        ilChangeEvent::_deleteReadEventsForUsers($this->obj_id, $a_user_ids);
                
        // update LP status to get collections up-to-date
        include_once "Services/Tracking/classes/class.ilLPStatusWrapper.php";
        foreach ($a_user_ids as $user_id) {
            ilLPStatusWrapper::_updateStatus($this->obj_id, $user_id);
        }
    }
        
    protected function resetCustomLPDataForUserIds(array $a_user_ids, $a_recursive = true)
    {
        // this should delete all data that is relevant for the supported LP modes
    }
    
    protected function gatherLPUsers()
    {
        include_once "Services/Tracking/classes/class.ilLPMarks.php";
        $user_ids = ilLPMarks::_getAllUserIds($this->obj_id);
        
        include_once "Services/Tracking/classes/class.ilChangeEvent.php";
        $user_ids = array_merge($user_ids, ilChangeEvent::_getAllUserIds($this->obj_id));
        
        return $user_ids;
    }
    
    
    //
    // EVENTS
    //
        
    final public static function handleMove($a_source_ref_id)
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        $ilDB = $DIC->database();
        
        $ref_ids = $tree->getSubTreeIds($a_source_ref_id);
        $ref_ids[] = $a_source_ref_id;
        
        // get "parent" path to source node (not including source node)
        $new_path = $tree->getPathId($a_source_ref_id);
        array_pop($new_path);
        $new_path = implode("/", $new_path);
    
        include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
        
        // find collections with ref_ids
        $set = $ilDB->query("SELECT DISTINCT(ut_lp_collections.obj_id) obj_id" .
            " FROM object_reference" .
            " JOIN ut_lp_collections ON" .
            " (" . $ilDB->in("object_reference.ref_id", $ref_ids, "", "integer") .
            " AND object_reference.ref_id = ut_lp_collections.item_id)");
        while ($rec = $ilDB->fetchAssoc($set)) {
            if (in_array(ilObject::_lookupType($rec["obj_id"]), array("crs", "grp", "fold"))) {
                $coll_ref_id = ilObject::_getAllReferences($rec["obj_id"]);
                $coll_ref_id = array_pop($coll_ref_id);
                
                // #13402
                if ($coll_ref_id == $a_source_ref_id) {
                    continue;
                }
                
                // #17703 - collection has also been moved - nothing todo
                if ($tree->isGrandChild($a_source_ref_id, $coll_ref_id)) {
                    continue;
                }
                
                // get path to collection (including collection "parent")
                $coll_path = $tree->getPathId($coll_ref_id);
                $coll_path = implode("/", $coll_path);
                
                // collection path is not inside new path
                if (!stristr($new_path, $coll_path)) {
                    // delete all items of moved (sub-)tree
                    $query = "DELETE FROM ut_lp_collections" .
                        " WHERE obj_id = " . $ilDB->quote($rec["obj_id"], "integer") .
                        " AND " . $ilDB->in("item_id", $ref_ids, "", "integer");
                    $ilDB->manipulate($query);
                    
                    ilLPStatusWrapper::_refreshStatus($rec["obj_id"]);
                }
            }
        }
    }
    
    final public function handleToTrash()
    {
        $this->updateParentCollections();
    }
    
    final public function handleDelete()
    {
        include_once "Services/Tracking/classes/class.ilLPMarks.php";
        ilLPMarks::deleteObject($this->obj_id);

        include_once "Services/Tracking/classes/class.ilChangeEvent.php";
        ilChangeEvent::_delete($this->obj_id);
        
        $collection = $this->getCollectionInstance();
        if ($collection) {
            $collection->delete();
        }
        
        $this->updateParentCollections();
    }
    
    final protected function updateParentCollections()
    {
        $ilDB = $this->db;
        
        include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
        
        // update parent collections?
        $set = $ilDB->query("SELECT ut_lp_collections.obj_id obj_id FROM " .
                "object_reference JOIN ut_lp_collections ON " .
                "(object_reference.obj_id = " . $ilDB->quote($this->obj_id, "integer") .
                " AND object_reference.ref_id = ut_lp_collections.item_id)");
        while ($rec = $ilDB->fetchAssoc($set)) {
            if (in_array(ilObject::_lookupType($rec["obj_id"]), array("crs", "grp", "fold"))) {
                // remove from parent collection
                $query = "DELETE FROM ut_lp_collections" .
                    " WHERE obj_id = " . $ilDB->quote($rec["obj_id"], "integer") .
                    " AND item_id = " . $ilDB->quote($this->obj_id, "integer");
                $ilDB->manipulate($query);
                
                ilLPStatusWrapper::_refreshStatus($rec["obj_id"]);
            }
        }
    }
    
    
    //
    // LP-relevant memberships
    //
    
    /**
     * Find (lp-relevant) members for given object ids
     *
     * @param array $a_res
     * @param int $a_usr_id
     * @param array $a_obj_ids
     */
    protected static function isLPMember(array &$a_res, $a_usr_id, $a_obj_ids)
    {
        // should be overwritten by object-type-specific class
        return false;
    }
    
    /**
     * Find (lp-relevant) memberships by path
     *
     * @param array $a_res
     * @param int $a_usr_id
     * @param int $a_parent_ref_id
     * @param array $a_obj_ids
     * @param bool $a_mapped_ref_ids
     * @return array
     */
    protected static function findMembershipsByPath(array &$a_res, $a_usr_id, $a_parent_ref_id, array $a_obj_ids, $a_mapped_ref_ids = false)
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        
        $found = array();
                
        // walk path to find course or group object and check members of that object
        $path = $tree->getPathId($a_parent_ref_id);
        foreach (array_reverse($path) as $path_ref_id) {
            $type = ilObject::_lookupType($path_ref_id, true);
            if ($type == "crs" ||
                $type == "grp") {
                $class = self::getTypeClass($type);
                $path_ob_id = ilObject::_lookupObjId($path_ref_id);
                $chk = array();
                $class::isLPMember($chk, $a_usr_id, array($path_ob_id));
                if (!$a_mapped_ref_ids) {
                    // we found a grp/crs in path of (single) parent - mark all objects
                    foreach ($a_obj_ids as $obj_id) {
                        $found[] = $obj_id;
                        if ($chk[$path_ob_id]) {
                            $a_res[$obj_id] = true;
                        }
                    }
                } else {
                    // all children from current node are "lp-valid"
                    foreach ($a_obj_ids as $obj_id => $ref_ids) {
                        foreach ($ref_ids as $ref_id) {
                            if ($tree->isGrandChild($path_ref_id, $ref_id)) {
                                $found[$obj_id][] = $ref_id;
                                if ($chk[$path_ob_id]) {
                                    $a_res[$obj_id] = true;
                                }
                                break;
                            }
                        }
                    }
                }
                break;
            }
        }
        
        return $found;
    }
    
    /**
     * Get all objects where given user is member (from LP POV)
     *
     * @param int $a_usr_id
     * @param array $a_obj_ids
     * @param int $a_parent_ref_id
     * @param bool $a_mapped_ref_ids
     * @return array
     */
    public static function getLPMemberships($a_usr_id, array $a_obj_ids, $a_parent_ref_id = null, $a_mapped_ref_ids = false)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $tree = $DIC->repositoryTree();
        
        // see ilTrQuery::getParticipantsForObject() [single object only]
        // this is optimized for larger number of objects, e.g. list GUIs

        $ref_map = [];
        if ((bool) $a_mapped_ref_ids) {
            $ref_map = $a_obj_ids;
            $a_obj_ids = array_keys($a_obj_ids);
        }
        
        $res = array();
        
        // get object types
        $types_map = array();
        $query = " SELECT obj_id, type" .
            " FROM object_data" .
            " WHERE " . $ilDB->in("obj_id", $a_obj_ids, "", "integer");
        $set = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($set)) {
            $types_map[$row["type"]][] = $row["obj_id"];
            $res[$row["obj_id"]] = false;
        }
        
        $find_by_parent = array();
        foreach ($types_map as $type => $type_obj_ids) {
            $class = self::getTypeClass($type);
            if ($class) {
                // lp-supported type?
                if (!$class::isLPMember($res, $a_usr_id, $type_obj_ids)) {
                    $find_by_parent = array_merge($find_by_parent, $type_obj_ids);
                }
            }
        }
        
        if (sizeof($find_by_parent)) {
            // single parent for all objects (repository/ilObjectListGUI)
            if ($a_parent_ref_id) {
                if (self::findMembershipsByPath($res, $a_usr_id, $a_parent_ref_id, $find_by_parent)) {
                    // we found a crs/grp in path, so no need to check read_events
                    $find_by_parent = null;
                }
            }
            // different parents (PD > LP)
            elseif (is_array($ref_map) && count($ref_map) > 0) {
                foreach ($find_by_parent as $obj_id) {
                    // maybe already found by path search from other object/reference
                    if ($res[$obj_id] === false) {
                        if (isset($ref_map[$obj_id]) && is_array($ref_map[$obj_id])) {
                            // check all references
                            foreach ($ref_map[$obj_id] as $ref_id) {
                                $parent_ref_id = $tree->getParentId($ref_id);
                                if ($parent_ref_id == ROOT_FOLDER_ID) {
                                    continue;
                                }
                                
                                // we are checking the complete ref_map
                                // to find all relevant objects in subtree of current ref_id
                                $found = self::findMembershipsByPath($res, $a_usr_id, $parent_ref_id, $ref_map, true);
                                if (is_array($found) && count($found) > 0) {
                                    // if any references were found in a crs/grp-subtree
                                    // remove from "read-event"-last-resort-pool
                                    foreach ($found as $found_obj_id => $found_ref_ids) {
                                        $diff = array_diff($ref_map[$found_obj_id], $found_ref_ids);
                                        if ($diff) {
                                            // 1-n refs are in another subtree
                                            // have to be checked separately
                                            $ref_map[$found_obj_id] = $diff;
                                        } else {
                                            // all references found in subtree
                                            // no need to check again
                                            unset($ref_map[$found_obj_id]);
                                        }
                                    }
                                    break;
                                }
                            }
                        }
                    }
                }
                
                $find_by_parent = array_keys($ref_map);
            }
            
            // last resort: use read_event?
            if (is_array($find_by_parent) && count($find_by_parent) > 0) {
                $set = $ilDB->query("SELECT obj_id" .
                    " FROM read_event" .
                    " WHERE " . $ilDB->in("obj_id", $find_by_parent, "", "integer") .
                    " AND usr_id = " . $ilDB->quote($a_usr_id, "integer"));
                while ($row = $ilDB->fetchAssoc($set)) {
                    $res[$row["obj_id"]] = true;
                }
            }
        }
        
        return $res;
    }
    
    public function getMailTemplateId()
    {
        // type-specific
    }
    
    
    //
    // type-specific support of features (should be enhanced)
    //
    
    public static function supportsSpentSeconds($a_obj_type)
    {
        return !in_array($a_obj_type, array("exc", "file", "mcst", "mob", "htlm", "copa"));
    }
    
    public static function supportsMark($a_obj_type)
    {
        return !in_array($a_obj_type, array("lm", "dbk"));
    }
    
    public static function supportsMatrixView($a_obj_type)
    {
        return !in_array($a_obj_type, array('svy', 'tst', 'htlm', 'exc', 'sess', 'file', 'prg', 'copa'));
    }
    
    
    // type-wide default
        
    /**
     * Get available type-specific default modes (no administration needed)
     * @param bool $a_lp_active
     * @return array
     */
    public static function getDefaultModes($a_lp_active)
    {
        return array(ilLPObjSettings::LP_MODE_UNDEFINED);
    }
    
    protected static function getTypeDefaultFromDB($a_type)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        if (!is_array(self::$type_defaults)) {
            self::$type_defaults = array();
            $set = $ilDB->query("SELECT * FROM ut_lp_defaults");
            while ($row = $ilDB->fetchAssoc($set)) {
                self::$type_defaults[$row["type_id"]] = $row["lp_mode"];
            }
        }
        return self::$type_defaults[$a_type];
    }
    
    public static function saveTypeDefaults(array $a_data)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $ilDB->manipulate("DELETE FROM ut_lp_defaults");
        foreach ($a_data as $type => $mode) {
            $ilDB->insert("ut_lp_defaults", array(
                "type_id" => array("text", $type),
                "lp_mode" => array("integer", $mode)
            ));
        }
    }
    
    /**
     * Get current type default
     *
     * @param string $a_type
     * @return int
     */
    public static function getTypeDefault($a_type)
    {
        $db = self::getTypeDefaultFromDB($a_type);
        if ($db !== null) {
            return $db;
        }
        
        $class = self::getTypeClass($a_type);
        $olp = new $class(0);
        return $olp->getDefaultMode();
    }
}
