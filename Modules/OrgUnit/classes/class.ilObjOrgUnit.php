<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjOrgUnit
 * Based on methods of ilObjCategoryGUI
 * @author : Oskar Truffer <ot@studer-raimann.ch>
 * @author : Martin Studer <ms@studer-raimann.ch>
 * @author : Stefan Wanzenried <sw@studer-raimann.ch>
 * @author : Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjOrgUnit extends ilContainer
{
    public const TABLE_NAME = 'orgu_data';
    protected static int $root_ref_id = 0;
    protected static int $root_id = 0;
    private ilDBInterface $ilDb;
    private ilAppEventHandler $ilAppEventHandler;
    private ilRbacReview $rbacreview;
    private ilRbacAdmin $rbacadmin;

    /**
     * Cache storing OrgUnit objects that have OrgUnit types with custom icons assigned
     */
    protected static array $icons_cache;
    /**
     * ID of assigned OrgUnit type
     */
    protected int $orgu_type_id = 0;
    /**
     * Advanced Metadata Values for this OrgUnit
     */
    protected array $amd_data;

    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        global $DIC;

        $this->ilDb = $DIC['ilDB'];
        $this->type = "orgu";
        $this->ilAppEventHandler = $DIC['ilAppEventHandler'];
        $this->rbacreview = $DIC['rbacreview'];
        $this->rbacadmin = $DIC['rbacadmin'];

        parent::__construct($a_id, $a_call_by_reference);
    }

    final public function read() : void
    {
        parent::read();
        /** @var */
        $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE orgu_id = ' . $ilDB->quote($this->getId(), 'integer');
        $set = $ilDB->query($sql);
        if ($ilDB->numRows($set)) {
            $rec = $this->ilDb->fetchObject($set);
            $this->setOrgUnitTypeId($rec->orgu_type_id);
        }
    }

    final public function create() : int
    {
        $id = parent::create();
        $this->ilDb->insert(self::TABLE_NAME, array(
            'orgu_type_id' => array('integer', $this->getOrgUnitTypeId()),
            'orgu_id' => array('integer', $this->getId()),
        ));
        return $id;
    }

    final public function update() : bool
    {
        parent::update();
        $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE orgu_id = ' . $this->ilDb->quote($this->getId(),
                'integer');
        $set = $this->ilDb->query($sql);
        if ($this->ilDb->numRows($set)) {
            $this->ilDb->update(self::TABLE_NAME, array(
                'orgu_type_id' => array('integer', $this->getOrgUnitTypeId()),
            ), array(
                'orgu_id' => array('integer', $this->getId()),
            ));
        } else {
            $this->ilDb->insert(self::TABLE_NAME, array(
                'orgu_type_id' => array('integer', $this->getOrgUnitTypeId()),
                'orgu_id' => array('integer', $this->getId()),
            ));
        }
        // Update selection for advanced meta data of the type
        if ($this->getOrgUnitTypeId()) {
            ilAdvancedMDRecord::saveObjRecSelection($this->getId(), 'orgu_type',
                $this->getOrgUnitType()->getAssignedAdvancedMDRecordIds());
        } else {
            // If no type is assigned, delete relations by passing an empty array
            ilAdvancedMDRecord::saveObjRecSelection($this->getId(), 'orgu_type', array());
        }

        return true;
    }

    final public function getOrgUnitTypeId() : int
    {
        return $this->orgu_type_id;
    }

    final public function getOrgUnitType() : ilOrgUnitType|null
    {
        return ilOrgUnitType::getInstance($this->getOrgUnitTypeId());
    }

    final public function setOrgUnitTypeId(int $a_id) : void
    {
        $this->orgu_type_id = $a_id;
    }

    /**
     * Get the assigned AMD Values.
     * If a record_id is given, returns an array with all Elements (instances of ilADT objects)
     * belonging to this record. If no record_id is given, returns an associative array with
     * record-IDs as keys and ilADT objects as values
     */
    final public function getAdvancedMDValues(int $a_record_id = 0) : array
    {
        if (!$this->getOrgUnitTypeId()) {
            return array();
        }
        // Serve from cache?
        if (is_array($this->amd_data)) {
            if ($a_record_id) {
                return $this->amd_data[$a_record_id] ?? array();
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
            return $this->amd_data[$a_record_id] ?? array();
        } else {
            return $this->amd_data;
        }
    }

    /**
     * Returns an array that maps from OrgUnit object IDs to its icon defined by the assigned
     * OrgUnit type. Keys = OrgUnit object IDs, values = Path to the icon This allows to get the
     * Icons of OrgUnits without loading the object (e.g. used in the tree explorer)
     */
    final public static function getIconsCache() : array
    {
        if (is_array(self::$icons_cache)) {
            return self::$icons_cache;
        }
        global $DIC;
        $ilDb = $DIC->database();
        $sql = 'SELECT orgu_id, ot.id AS type_id FROM orgu_data
                INNER JOIN orgu_types AS ot ON (ot.id = orgu_data.orgu_type_id)
                WHERE ot.icon IS NOT NULL';
        $set = $ilDb->query($sql);
        $icons_cache = array();
        while ($row = $ilDb->fetchObject($set)) {
            $type = ilOrgUnitType::getInstance($row->type_id);
            if ($type && is_file($type->getIconPath(true))) {
                $icons_cache[$row->orgu_id] = $type->getIconPath(true);
            }
        }
        self::$icons_cache = $icons_cache;

        return $icons_cache;
    }

    final public static function getRootOrgRefId() : int
    {
        self::loadRootOrgRefIdAndId();

        return self::$root_ref_id;
    }

    final public static function getRootOrgId() : int
    {
        self::loadRootOrgRefIdAndId();

        return self::$root_id;
    }

    private static function loadRootOrgRefIdAndId() : void
    {
        if (self::$root_ref_id === 0 || self::$root_id === 0) {
            global $DIC;
            $ilDb = $DIC['ilDB'];
            $q = "SELECT o.obj_id, r.ref_id FROM object_data o
			INNER JOIN object_reference r ON r.obj_id = o.obj_id
			WHERE title = " . $ilDb->quote('__OrgUnitAdministration', 'text') . "";
            $set = $ilDb->query($q);
            $res = $ilDb->fetchAssoc($set);
            self::$root_id = (int) $res["obj_id"];
            self::$root_ref_id = (int) $res["ref_id"];
        }
    }

    /**
     * Adds the user ids to the position employee.
     * @param int[] $user_ids
     */
    final public function assignUsersToEmployeeRole(array $user_ids) : void
    {

        $position_id = ilOrgUnitPosition::getCorePositionId(ilOrgUnitPosition::CORE_POSITION_EMPLOYEE);

        foreach ($user_ids as $user_id) {
            ilOrgUnitUserAssignment::findOrCreateAssignment($user_id, $position_id, $this->getRefId());

            $this->ilAppEventHandler->raise('Modules/OrgUnit', 'assignUsersToEmployeeRole', array(
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
     * @param int[] $user_ids
     */
    final public function assignUsersToSuperiorRole(array $user_ids) : void
    {
        $position_id = ilOrgUnitPosition::getCorePositionId(ilOrgUnitPosition::CORE_POSITION_SUPERIOR);

        foreach ($user_ids as $user_id) {
            ilOrgUnitUserAssignment::findOrCreateAssignment($user_id, $position_id, $this->getRefId());

            $this->ilAppEventHandler->raise('Modules/OrgUnit', 'assignUsersToSuperiorRole', array(
                'object' => $this,
                'obj_id' => $this->getId(),
                'ref_id' => $this->getRefId(),
                'position_id' => $position_id,
                'user_id' => $user_id,
            ));
        }
    }

    final public function deassignUserFromEmployeeRole(int $user_id) : void
    {
        $position_id = ilOrgUnitPosition::getCorePositionId(ilOrgUnitPosition::CORE_POSITION_EMPLOYEE);
        ilOrgUnitUserAssignment::findOrCreateAssignment($user_id, $position_id, $this->getRefId())->delete();

        $this->ilAppEventHandler->raise('Modules/OrgUnit', 'deassignUserFromEmployeeRole', array(
            'object' => $this,
            'obj_id' => $this->getId(),
            'ref_id' => $this->getRefId(),
            'position_id' => $position_id,
            'user_id' => $user_id,
        ));
    }

    final public function deassignUserFromSuperiorRole(int $user_id) : void
    {
        $position_id = ilOrgUnitPosition::getCorePositionId(ilOrgUnitPosition::CORE_POSITION_SUPERIOR);
        ilOrgUnitUserAssignment::findOrCreateAssignment($user_id, $position_id, $this->getRefId())->delete();

        $this->ilAppEventHandler->raise('Modules/OrgUnit', 'deassignUserFromSuperiorRole', array(
            'object' => $this,
            'obj_id' => $this->getId(),
            'ref_id' => $this->getRefId(),
            'position_id' => $position_id,
            'user_id' => $user_id,
        ));
    }

    /**
     * Assign a given user to a given local role
     */
    final public function assignUserToLocalRole(int $role_id, int $user_id) : bool
    {

        $arrLocalRoles = $this->rbacreview->getLocalRoles($this->getRefId());
        if (!in_array($role_id, $arrLocalRoles)) {
            return false;
        }

        $return = $this->rbacadmin->assignUser($role_id, $user_id);

        $this->ilAppEventHandler->raise('Modules/OrgUnit', 'assignUserToLocalRole', array(
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
     */
    final public function deassignUserFromLocalRole(int $role_id, int $user_id) : bool
    {
        $arrLocalRoles = $this->rbacreview->getLocalRoles($this->getRefId());
        if (!in_array($role_id, $arrLocalRoles)) {
            return false;
        }

        $return = $this->rbacadmin->deassignUser($role_id, $user_id);

        $this->ilAppEventHandler->raise('Modules/OrgUnit', 'deassignUserFromLocalRole', array(
            'object' => $this,
            'obj_id' => $this->getId(),
            'ref_id' => $this->getRefId(),
            'role_id' => $role_id,
            'user_id' => $user_id,
        ));

        return $return;
    }

    final public static function _exists(int $id, bool $isReference = false, ?string $type = "orgu") : bool
    {
        return parent::_exists($id, $isReference, "orgu");
    }

    final public function getTitle() : string
    {
        if (parent::getTitle() !== "__OrgUnitAdministration") {
            return parent::getTitle();
        } else {
            return $this->lng->txt("objs_orgu");
        }
    }

    /**
     * get object long description (stored in object_description)
     */
    final public function getLongDescription() : string
    {
        if (parent::getTitle() === "__OrgUnitAdministration") {
            return $this->lng->txt("obj_orgu_description");
        } else {
            return parent::getLongDescription();
        }
    }

    /**
     * @return array This catches if by some means there is no translation.
     */
    final public function getTranslations()
    {
        $q = "SELECT * FROM object_translation WHERE obj_id = " . $this->ilDb->quote($this->getId(),
                'integer') . " ORDER BY lang_default DESC";
        $r = $this->db->query($q);

        $data = [];
        while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $data[$row->lang_code] = array(
                "title" => $row->title,
                "desc" => $row->description,
                "lang" => $row->lang_code,
                'default' => $row->lang_default,
            );
        }

        $translations = $data;

        if (!count($translations)) {
            $this->addTranslation($this->getTitle(), "", $this->lng->getDefaultLanguage(), true);
            $translations[$this->lng->getDefaultLanguage()] = array(
                "title" => $this->getTitle(),
                "desc" => "",
                "lang" => $this->lng->getDefaultLanguage(),
            );
        }

        return $translations;
    }

    /**
     * delete orgunit, childs and all related data
     * @return    bool    true if all object data were removed; false if only a references were
     *                       removed
     */
    final public function delete() : bool
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        // put here category specific stuff
        ilObjUserFolder::_updateUserFolderAssignment($this->ref_id, USER_FOLDER_ID);

        $query = "DELETE FROM object_translation WHERE obj_id = " . $this->ilDb->quote($this->getId(), 'integer');
        $this->ilDb->manipulate($query);

        $this->ilAppEventHandler->raise('Modules/OrgUnit', 'delete', array(
            'object' => $this,
            'obj_id' => $this->getId(),
        ));

        $sql = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE orgu_id = ' . $this->ilDb->quote($this->getId(), 'integer');
        $this->ilDb->manipulate($sql);

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
    final public function removeTranslations() : void
    {
        $query = "DELETE FROM object_translation WHERE obj_id= " . $this->ilDb->quote($this->getId(), 'integer');
        $res = $this->ilDb->manipulate($query);
    }

    /**
     * remove translations of current OrgUnit
     * @param $a_lang string en|de|...
     */
    final public function deleteTranslation(string $a_lang) : void
    {
        $query = "DELETE FROM object_translation WHERE obj_id= " . $this->quote($this->getId(),
                'integer') . " AND lang_code = "
            . $this->quote($a_lang, 'text');
        $this->ilDb->manipulate($query);
    }

    /**
     * add a new translation to current OrgUnit
     */
    final public function addTranslation(string $a_title, string $a_desc, string $a_lang, string $a_lang_default) : void
    {
        if (empty($a_title)) {
            $a_title = "NO TITLE";
        }

        $query = "INSERT INTO object_translation " . "(obj_id,title,description,lang_code,lang_default) " . "VALUES " . "("
            . $this->ilDb->quote($this->getId(), 'integer') . "," . $this->ilDb->quote($a_title,
                'text') . "," . $this->ilDb->quote($a_desc, 'text') . ","
            . $this->ilDb->quote($a_lang, 'text') . "," . $this->ilDb->quote($a_lang_default, 'integer') . ")";
        $this->ilDb->manipulate($query);
    }

    /**
     * update a translation to current OrgUnit
     */
    final public function updateTranslation(string $title, string $desc, string $lang, string $lang_default) : void
    {
        if (empty($title)) {
            $a_title = "NO TITLE";
        }

        $query = "UPDATE object_translation SET ";

        $query .= " title = " . $this->ilDb->quote($title, 'text');

        if ($desc !== "") {
            $query .= ", description = " . $this->ilDb->quote($desc, 'text') . " ";
        }

        $query .= ", lang_default = " . $this->ilDb->quote($lang_default, 'integer') . " ";

        $query .= " WHERE obj_id = " . $this->ilDb->quote($this->getId(),
                'integer') . " AND lang_code = " . $this->ilDb->quote($lang, 'text');
        $this->ilDb->manipulate($query);
    }

    final public function writePath() : void
    {
        if ($this->getRefId()) {
            ilOrgUnitPathStorage::writePathByRefId($this->getRefId());
        }
    }
}
