<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjOrgUnit
 *
 * Based on methods of ilObjCategoryGUI
 *
 * @author : Oskar Truffer <ot@studer-raimann.ch>
 * @author : Martin Studer <ms@studer-raimann.ch>
 * @author : Stefan Wanzenried <sw@studer-raimann.ch>
 * @author : Fabian Schmid <fs@studer-raimann.ch>
 *
 */
class ilObjOrgUnit extends ilContainer
{
    const TABLE_NAME = 'orgu_data';
    /**
     * @var int
     */
    protected static $root_ref_id;
    /**
     * @var int
     */
    protected static $root_id;
    /**
     * Cache storing OrgUnit objects that have OrgUnit types with custom icons assigned
     *
     * @var array
     */
    protected static $icons_cache;
    /**
     * ID of assigned OrgUnit type
     *
     * @var int
     */
    protected $orgu_type_id = 0;
    /**
     * Advanced Metadata Values for this OrgUnit
     *
     * @var array
     */
    protected $amd_data;


    /**
     * @param int  $a_id
     * @param bool $a_call_by_reference
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = "orgu";
        parent::__construct($a_id, $a_call_by_reference);
    }


    public function read()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        parent::read();
        /** @var ilDB $ilDB */
        $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE orgu_id = ' . $ilDB->quote($this->getId(), 'integer');
        $set = $ilDB->query($sql);
        if ($ilDB->numRows($set)) {
            $rec = $ilDB->fetchObject($set);
            $this->setOrgUnitTypeId($rec->orgu_type_id);
        }
    }


    public function create()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        parent::create();
        $ilDB->insert(self::TABLE_NAME, array(
            'orgu_type_id' => array( 'integer', $this->getOrgUnitTypeId() ),
            'orgu_id' => array( 'integer', $this->getId() ),
        ));
    }


    public function update()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        parent::update();
        $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE orgu_id = ' . $ilDB->quote($this->getId(), 'integer');
        $set = $ilDB->query($sql);
        if ($ilDB->numRows($set)) {
            $ilDB->update(self::TABLE_NAME, array(
                'orgu_type_id' => array( 'integer', $this->getOrgUnitTypeId() ),
            ), array(
                'orgu_id' => array( 'integer', $this->getId() ),
            ));
        } else {
            $ilDB->insert(self::TABLE_NAME, array(
                'orgu_type_id' => array( 'integer', $this->getOrgUnitTypeId() ),
                'orgu_id' => array( 'integer', $this->getId() ),
            ));
        }
        // Update selection for advanced meta data of the type
        if ($this->getOrgUnitTypeId()) {
            ilAdvancedMDRecord::saveObjRecSelection($this->getId(), 'orgu_type', $this->getOrgUnitType()->getAssignedAdvancedMDRecordIds());
        } else {
            // If no type is assigned, delete relations by passing an empty array
            ilAdvancedMDRecord::saveObjRecSelection($this->getId(), 'orgu_type', array());
        }
    }


    /**
     * @return int
     */
    public function getOrgUnitTypeId()
    {
        return $this->orgu_type_id;
    }


    /**
     * @return ilOrgUnitType|null
     */
    public function getOrgUnitType()
    {
        return ilOrgUnitType::getInstance($this->getOrgUnitTypeId());
    }


    /**
     * @param $a_id
     */
    public function setOrgUnitTypeId($a_id)
    {
        $this->orgu_type_id = $a_id;
    }


    /**
     * Get the assigned AMD Values.
     * If a record_id is given, returns an array with all Elements (instances of ilADT objects)
     * belonging to this record. If no record_id is given, returns an associative array with
     * record-IDs as keys and ilADT objects as values
     *
     * @param int $a_record_id
     *
     * @return array
     */
    public function getAdvancedMDValues($a_record_id = 0)
    {
        if (!$this->getOrgUnitTypeId()) {
            return array();
        }
        // Serve from cache?
        if (is_array($this->amd_data)) {
            if ($a_record_id) {
                return (isset($this->amd_data[$a_record_id])) ? $this->amd_data[$a_record_id] : array();
            } else {
                return $this->amd_data;
            }
        }
        /** @var ilAdvancedMDValues $amd_values */
        foreach (ilAdvancedMDValues::getInstancesForObjectId($this->getId(), 'orgu') as $record_id => $amd_values) {
            $amd_values = new ilAdvancedMDValues($record_id, $this->getId(), 'orgu_type', $this->getOrgUnitTypeId());
            $amd_values->read();
            $this->amd_data[$record_id] = $amd_values->getADTGroup()->getElements();
        }
        if ($a_record_id) {
            return (isset($this->amd_data[$a_record_id])) ? $this->amd_data[$a_record_id] : array();
        } else {
            return $this->amd_data;
        }
    }


    /**
     * Returns an array that maps from OrgUnit object IDs to its icon defined by the assigned
     * OrgUnit type. Keys = OrgUnit object IDs, values = Path to the icon This allows to get the
     * Icons of OrgUnits without loading the object (e.g. used in the tree explorer)
     *
     * @return array
     */
    public static function getIconsCache()
    {
        if (is_array(self::$icons_cache)) {
            return self::$icons_cache;
        }
        global $DIC;
        $ilDB = $DIC['ilDB'];
        /** @var ilDB $ilDB */
        $sql = 'SELECT orgu_id, ot.id AS type_id FROM orgu_data
                INNER JOIN orgu_types AS ot ON (ot.id = orgu_data.orgu_type_id)
                WHERE ot.icon IS NOT NULL';
        $set = $ilDB->query($sql);
        $icons_cache = array();
        while ($row = $ilDB->fetchObject($set)) {
            $type = ilOrgUnitType::getInstance($row->type_id);
            if ($type && is_file($type->getIconPath(true))) {
                $icons_cache[$row->orgu_id] = $type->getIconPath(true);
            }
        }
        self::$icons_cache = $icons_cache;

        return $icons_cache;
    }


    /**
     * @return int
     */
    public static function getRootOrgRefId()
    {
        self::loadRootOrgRefIdAndId();

        return self::$root_ref_id;
    }


    /**
     * @return int
     */
    public static function getRootOrgId()
    {
        self::loadRootOrgRefIdAndId();

        return self::$root_id;
    }


    private static function loadRootOrgRefIdAndId()
    {
        if (self::$root_ref_id === null || self::$root_id === null) {
            global $DIC;
            $ilDB = $DIC['ilDB'];
            $q = "SELECT o.obj_id, r.ref_id FROM object_data o
			INNER JOIN object_reference r ON r.obj_id = o.obj_id
			WHERE title = " . $ilDB->quote('__OrgUnitAdministration', 'text') . "";
            $set = $ilDB->query($q);
            $res = $ilDB->fetchAssoc($set);
            self::$root_id = $res["obj_id"];
            self::$root_ref_id = $res["ref_id"];
        }
    }


    /**
     * Adds the user ids to the position employee.
     *
     * @param $user_ids
     */
    public function assignUsersToEmployeeRole($user_ids)
    {
        global $DIC;
        $ilAppEventHandler = $DIC['ilAppEventHandler'];
        $position_id = ilOrgUnitPosition::getCorePositionId(ilOrgUnitPosition::CORE_POSITION_EMPLOYEE);

        foreach ($user_ids as $user_id) {
            ilOrgUnitUserAssignment::findOrCreateAssignment($user_id, $position_id, $this->getRefId());

            $ilAppEventHandler->raise('Modules/OrgUnit', 'assignUsersToEmployeeRole', array(
                'object' => $this,
                'obj_id' => $this->getId(),
                'ref_id' => $this->getRefId(),
                'position_id' => $position_id,
                'user_id' => $user_id,
            ));
        }
    }


    /**
     * Adds the user ids to the position superior.
     *
     * @param $user_ids
     */
    public function assignUsersToSuperiorRole($user_ids)
    {
        global $DIC;
        $ilAppEventHandler = $DIC['ilAppEventHandler'];
        $position_id = ilOrgUnitPosition::getCorePositionId(ilOrgUnitPosition::CORE_POSITION_SUPERIOR);

        foreach ($user_ids as $user_id) {
            ilOrgUnitUserAssignment::findOrCreateAssignment($user_id, $position_id, $this->getRefId());

            $ilAppEventHandler->raise('Modules/OrgUnit', 'assignUsersToSuperiorRole', array(
                'object' => $this,
                'obj_id' => $this->getId(),
                'ref_id' => $this->getRefId(),
                'position_id' => $position_id,
                'user_id' => $user_id,
            ));
        }
    }


    public function deassignUserFromEmployeeRole($user_id)
    {
        global $DIC;
        $position_id = ilOrgUnitPosition::getCorePositionId(ilOrgUnitPosition::CORE_POSITION_EMPLOYEE);

        $ilAppEventHandler = $DIC['ilAppEventHandler'];
        ilOrgUnitUserAssignment::findOrCreateAssignment($user_id, $position_id, $this->getRefId())->delete();

        $ilAppEventHandler->raise('Modules/OrgUnit', 'deassignUserFromEmployeeRole', array(
            'object' => $this,
            'obj_id' => $this->getId(),
            'ref_id' => $this->getRefId(),
            'position_id' => $position_id,
            'user_id' => $user_id,
        ));
    }


    public function deassignUserFromSuperiorRole($user_id)
    {
        global $DIC;
        $position_id = ilOrgUnitPosition::getCorePositionId(ilOrgUnitPosition::CORE_POSITION_SUPERIOR);

        $ilAppEventHandler = $DIC['ilAppEventHandler'];
        ilOrgUnitUserAssignment::findOrCreateAssignment($user_id, $position_id, $this->getRefId())->delete();

        $ilAppEventHandler->raise('Modules/OrgUnit', 'deassignUserFromSuperiorRole', array(
            'object' => $this,
            'obj_id' => $this->getId(),
            'ref_id' => $this->getRefId(),
            'position_id' => $position_id,
            'user_id' => $user_id,
        ));
    }


    /**
     * Assign a given user to a given local role
     *
     * @param int $role_id
     * @param int $user_id
     *
     * @return bool
     */
    public function assignUserToLocalRole($role_id, $user_id)
    {
        global $DIC;
        $rbacreview = $DIC['rbacreview'];
        $rbacadmin = $DIC['rbacadmin'];
        $ilAppEventHandler = $DIC['ilAppEventHandler'];

        $arrLocalRoles = $rbacreview->getLocalRoles($this->getRefId());
        if (!in_array($role_id, $arrLocalRoles)) {
            return false;
        }

        $return = $rbacadmin->assignUser($role_id, $user_id);

        $ilAppEventHandler->raise('Modules/OrgUnit', 'assignUserToLocalRole', array(
            'object' => $this,
            'obj_id' => $this->getId(),
            'ref_id' => $this->getRefId(),
            'role_id' => $role_id,
            'user_id' => $user_id,
        ));

        return $return;
    }


    /**
     * Deassign a given user to a given local role
     *
     * @param int $role_id
     * @param int $user_id
     *
     * @return bool
     */
    public function deassignUserFromLocalRole($role_id, $user_id)
    {
        global $DIC;
        $rbacreview = $DIC['rbacreview'];
        $rbacadmin = $DIC['rbacadmin'];
        $ilAppEventHandler = $DIC['ilAppEventHandler'];

        $arrLocalRoles = $rbacreview->getLocalRoles($this->getRefId());
        if (!in_array($role_id, $arrLocalRoles)) {
            return false;
        }

        $return = $rbacadmin->deassignUser($role_id, $user_id);

        $ilAppEventHandler->raise('Modules/OrgUnit', 'deassignUserFromLocalRole', array(
            'object' => $this,
            'obj_id' => $this->getId(),
            'ref_id' => $this->getRefId(),
            'role_id' => $role_id,
            'user_id' => $user_id,
        ));

        return $return;
    }


    /**
     * @param        $a_id
     * @param bool   $a_reference
     * @param string $type
     *
     * @return bool
     */
    public static function _exists($a_id, $a_reference = false, $type = "orgu")
    {
        return parent::_exists($a_id, $a_reference, "orgu");
    }


    /**
     * Return title
     *
     * @return string
     */
    public function getTitle()
    {
        if (parent::getTitle() != "__OrgUnitAdministration") {
            return parent::getTitle();
        } else {
            return $this->lng->txt("objs_orgu");
        }
    }


    /**
     * get object long description (stored in object_description)
     *
     * @access    public
     * @return    string        object description
     */
    public function getLongDescription()
    {
        if (parent::getTitle() == "__OrgUnitAdministration") {
            return $this->lng->txt("obj_orgu_description");
        } else {
            return parent::getLongDescription();
        }
    }


    /**
     * @return array This catches if by some means there is no translation.
     */
    public function getTranslations()
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilDB = $DIC['ilDB'];

        $q = "SELECT * FROM object_translation WHERE obj_id = " . $ilDB->quote($this->getId(), 'integer') . " ORDER BY lang_default DESC";
        $r = $this->db->query($q);

        $num = 0;
        $data = [ "Fobject" => [] ];
        while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $data["Fobject"][$num] = array(
                "title" => $row->title,
                "desc" => $row->description,
                "lang" => $row->lang_code,
                'lang_default' => $row->lang_default,
            );
            $num++;
        }

        $translations = $data;

        if (!count($translations["Fobject"])) {
            $this->addTranslation($this->getTitle(), "", $lng->getDefaultLanguage(), true);
            $translations["Fobject"][] = array(
                "title" => $this->getTitle(),
                "desc" => "",
                "lang" => $lng->getDefaultLanguage(),
            );
        }

        return $translations;
    }


    /**
     * delete orgunit, childs and all related data
     *
     * @return    boolean    true if all object data were removed; false if only a references were
     *                       removed
     */
    public function delete()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilAppEventHandler = $DIC['ilAppEventHandler'];

        //Delete all Childs
        foreach (ilObjOrgUnitTree::_getInstance()->getAllChildren($this->ref_id) as $child_ref_id) {
            if (!ilObjOrgUnit::_exists($child_ref_id, true) || $this->ref_id == $child_ref_id) {
                continue; //already deleted || the current org_unit
            }
            $org_unit = new ilObjOrgUnit($child_ref_id);
            $org_unit->delete();
        }

        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        // put here category specific stuff
        ilObjUserFolder::_updateUserFolderAssignment($this->ref_id, USER_FOLDER_ID);

        $query = "DELETE FROM object_translation WHERE obj_id = " . $ilDB->quote($this->getId(), 'integer');
        $ilDB->manipulate($query);

        $ilAppEventHandler->raise('Modules/OrgUnit', 'delete', array(
            'object' => $this,
            'obj_id' => $this->getId(),
        ));

        $sql = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE orgu_id = ' . $ilDB->quote($this->getId(), 'integer');
        $ilDB->manipulate($sql);

        $path = ilOrgUnitPathStorage::find($this->getRefId());
        if ($path instanceof ilOrgUnitPathStorage) {
            $path->delete();
        }

        // Delete all position assignments to this object.
        $assignments = ilOrgUnitUserAssignment::where(array(
            'orgu_id' => $this->getRefId(),
        ))->get();
        foreach ($assignments as $assignment) {
            $assignment->delete();
        }

        return true;
    }


    /**
     * remove all Translations of current OrgUnit
     */
    public function removeTranslations()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "DELETE FROM object_translation WHERE obj_id= " . $ilDB->quote($this->getId(), 'integer');
        $res = $ilDB->manipulate($query);
    }


    /**
     * remove translations of current OrgUnit
     *
     * @param $a_lang string en|de|...
     */
    public function deleteTranslation($a_lang)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "DELETE FROM object_translation WHERE obj_id= " . $ilDB->quote($this->getId(), 'integer') . " AND lang_code = "
            . $ilDB->quote($a_lang, 'text');
        $res = $ilDB->manipulate($query);
    }


    /**
     * add a new translation to current OrgUnit
     *
     * @param $a_title
     * @param $a_desc
     * @param $a_lang
     * @param $a_lang_default
     *
     * @return bool
     */
    public function addTranslation($a_title, $a_desc, $a_lang, $a_lang_default)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (empty($a_title)) {
            $a_title = "NO TITLE";
        }

        $query = "INSERT INTO object_translation " . "(obj_id,title,description,lang_code,lang_default) " . "VALUES " . "("
            . $ilDB->quote($this->getId(), 'integer') . "," . $ilDB->quote($a_title, 'text') . "," . $ilDB->quote($a_desc, 'text') . ","
            . $ilDB->quote($a_lang, 'text') . "," . $ilDB->quote($a_lang_default, 'integer') . ")";
        $res = $ilDB->manipulate($query);

        return true;
    }


    /**
     * update a translation to current OrgUnit
     *
     * @param $a_title
     * @param $a_desc
     * @param $a_lang
     * @param $a_lang_default
     *
     * @return bool
     */
    public function updateTranslation($a_title, $a_desc, $a_lang, $a_lang_default)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (empty($a_title)) {
            $a_title = "NO TITLE";
        }

        $query = "UPDATE object_translation SET ";

        $query .= " title = " . $ilDB->quote($a_title, 'text');

        if ($a_desc != "") {
            $query .= ", description = " . $ilDB->quote($a_desc, 'text') . " ";
        }

        if ($a_lang_default) {
            $query .= ", lang_default = " . $ilDB->quote($a_lang_default, 'integer') . " ";
        }

        $query .= " WHERE obj_id = " . $ilDB->quote($this->getId(), 'integer') . " AND lang_code = " . $ilDB->quote($a_lang, 'text');
        $ilDB->manipulate($query);

        return true;
    }


    public function writePath()
    {
        if ($this->getRefId()) {
            ilOrgUnitPathStorage::writePathByRefId($this->getRefId());
        }
    }
}
