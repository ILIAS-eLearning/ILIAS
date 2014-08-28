<?php
require_once('class.ilOrgUnitTypeTranslation.php');
require_once('./Modules/OrgUnit/exceptions/class.ilOrgUnitTypeException.php');
require_once('./Modules/OrgUnit/exceptions/class.ilOrgUnitTypePluginException.php');
require_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');

/**
 * Class ilOrgUnitType
 *
 * @author: Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilOrgUnitType {

    const TABLE_NAME = 'orgu_types';

    /**
     * Folder in ILIAS webdir to store the icons
     */
    const WEB_DATA_FOLDER = 'orgu_data';

    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var string
     */
    protected $default_lang = '';

    /**
     * @var int
     */
    protected $owner;

    /**
     * @var string
     */
    protected $create_date;

    /**
     * @var string
     */
    protected $last_update;

    /**
     * @var string
     */
    protected $icon;

    /**
     * @var array
     */
    protected $translations = array();

    /**
     * @var array
     */
    protected $amd_records_assigned;

    /**
     * @var array
     */
    protected static $amd_records_available;

    /**
     * @var array
     */
    protected $orgus;

    /**
     * @var
     */
    protected $orgus_ids;

    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var ilLog
     */
    protected $log;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilPluginAdmin
     */
    protected $pluginAdmin;

    /**
     * @var array
     */
    protected $active_plugins;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * Object cache
     * @var array
     */
    protected static $instances = array();


    public function __construct($a_id=0) {
        global $ilDB, $ilLog, $ilUser, $ilPluginAdmin, $lng;
        $this->db = $ilDB;
        $this->log = $ilLog;
        $this->user = $ilUser;
        $this->pluginAdmin = $ilPluginAdmin;
        $this->lng = $lng;
        if ($a_id) {
            $this->id = (int) $a_id;
            $this->read();
        }
    }

    /**
     * Public
     */

    /**
     * Get instance of an ilOrgUnitType object
     * Returns object from cache or from database, returns null if no object was found
     *
     * @param int $a_id ID of the OrgUnit type
     * @return ilOrgUnitType|null
     */
    public static function getInstance($a_id) {
        if (!$a_id) {
            return null;
        }
        if (isset(self::$instances[$a_id])) {
            return self::$instances[$a_id];
        } else {
            try {
                $type = new self($a_id);
                self::$instances[$a_id] = $type;
                return $type;
            } catch (ilOrgUnitTypeException $e) {
                return null;
            }
        }
    }

    /**
     * Get array of all instances of ilOrgUnitType objects
     *
     * @return array
     */
    public static function getAllTypes() {
        global $ilDB;
        $sql = 'SELECT id FROM ' . self::TABLE_NAME;
        $set = $ilDB->query($sql);
        $types = array();
        while ($rec = $ilDB->fetchObject($set)) {
            $type = new self($rec->id);
            $types[] = $type;
            self::$instances[$rec->id] = $type;
        }
        return $types;
    }

    /**
     * Create object in database. Also invokes creating of translation objects.
     *
     * @throws ilOrgUnitTypeException
     */
    public function create() {
        $default_lang = $this->getDefaultLang();
        $title = $this->getTranslation('title', $default_lang);
        if (!$default_lang || !$title)
            throw new ilOrgUnitTypeException($this->lng->txt('orgu_type_msg_missing_title_default_language'));

        $this->id = $this->db->nextId(self::TABLE_NAME);
        $this->db->insert(self::TABLE_NAME, array(
            'id' => array('integer', $this->getId()),
            'default_lang' => array('text', $this->getDefaultLang()),
            'owner' => array('integer', $this->user->getId()),
            'icon' => array('text', $this->getIcon()),
            'create_date' => array('text', date('Y-m-d H:i:s')),
            'last_update' => array('text', date('Y-m-d H:i:s')),
        ));

        // Create translation(s)
        /** @var $trans ilOrgUnitTypeTranslation */
        foreach ($this->translations as $lang => $trans) {
            $trans->setOrguTypeId($this->getId());
            $trans->create();
        }
    }


    /**
     * Update changes to database
     *
     * @throws ilOrgUnitTypePluginException
     * @throws ilOrgUnitTypeException
     */
    public function update() {
        $title = $this->getTranslation('title', $this->getDefaultLang());
        if (!$title) {
            throw new ilOrgUnitTypeException($this->lng->txt('orgu_type_msg_missing_title'));
        }

        $disallowed = array();
        $titles = array();
        /** @var ilOrgUnitTypeHookPlugin $plugin */
        foreach ($this->getActivePlugins() as $plugin) {
            if (!$plugin->allowUpdate($this->getId())) {
                $disallowed[] = $plugin;
                $titles[] = $plugin->getPluginName();
            }
        }
        if (count($disallowed)) {
            $msg = sprintf($this->lng->txt('orgu_type_msg_updating_prevented'), implode(', ', $titles));
            throw new ilOrgUnitTypePluginException($msg, $disallowed);
        }

        $this->db->update(self::TABLE_NAME, array(
            'default_lang' => array('text', $this->getDefaultLang()),
            'owner' => array('integer', $this->getOwner()),
            'icon' => array('text', $this->getIcon()),
            'last_update' => array('text', date('Y-m-d H:i:s')),
        ), array(
            'id' => array('integer', $this->getId()),
        ));

        // Update translation(s)
        /** @var $trans ilOrgUnitTypeTranslation */
        foreach ($this->translations as $trans) {
            $trans->update();
        }
    }

    /**
     * Wrapper around create() and update() methods.
     *
     * @throws ilOrgUnitTypePluginException
     */
    public function save() {
        if ($this->getId()) {
            $this->update();
        } else {
            $this->create();
        }
    }


    /**
     * Delete object by removing all database entries.
     * Deletion is only possible if this type is not assigned to any OrgUnit and if no plugin disallowed deletion process.
     *
     * @throws ilOrgUnitTypeException
     */
    public function delete() {
        $orgus = $this->getOrgUnits(false);
        if (count($orgus)) {
            $titles = array();
            /** @var $orgu ilObjOrgUnit */
            foreach ($orgus as $orgu) {
                $titles[] = $orgu->getTitle();
            }
            throw new ilOrgUnitTypeException(sprintf($this->lng->txt('orgu_type_msg_unable_delete'), implode(', ', $titles)));
        }

        $disallowed = array();
        $titles = array();
        /** @var ilOrgUnitTypeHookPlugin $plugin */
        foreach ($this->getActivePlugins() as $plugin) {
            if (!$plugin->allowDelete($this->getId())) {
                $disallowed[] = $plugin;
                $titles[] = $plugin->getPluginName();
            }
        }
        if (count($disallowed)) {
            $msg = sprintf($this->lng->txt('orgu_type_msg_deletion_prevented'), implode(', ', $titles));
            throw new ilOrgUnitTypePluginException($msg, $disallowed);
        }

        $sql = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE id = ' . $this->db->quote($this->getId(), 'integer');
        $this->db->manipulate($sql);

        // Reset Type of OrgUnits (in Trash)
        $this->db->update('orgu_data', array(
            'orgu_type_id' => array('integer', 0),
        ), array(
            'orgu_type_id' => array('integer', $this->getId()),
        ));

        // Delete all translations
        ilOrgUnitTypeTranslation::deleteAllTranslations($this->getId());

        // Delete icon & folder
        if (is_file($this->getIconPath(true))) {
            unlink($this->getIconPath(true));
        }
        if (is_dir($this->getIconPath())) {
            rmdir($this->getIconPath());
        }

        // Delete relations to advanced metadata records
        $sql = 'DELETE FROM orgu_types_adv_md_rec WHERE type_id = ' . $this->db->quote($this->getId(), 'integer');
        $this->db->manipulate($sql);
    }

    /**
     * Get the title of an OrgUnit type. If no language code is given, a translation in the user-language is
     * returned. If no such translation exists, the translation of the default language is substituted.
     * If a language code is provided, returns title for the given language or null.
     *
     * @param string $a_lang_code
     * @return null|string
     */
    public function getTitle($a_lang_code='') {
        return $this->getTranslation('title', $a_lang_code);
    }


    /**
     * Set title of OrgUnit type.
     * If no lang code is given, sets title for default language.
     *
     * @param $a_title
     * @param string $a_lang_code
     */
    public function setTitle($a_title, $a_lang_code='') {
        $lang = ($a_lang_code) ? $a_lang_code : $this->getDefaultLang();
        $this->setTranslation('title', $a_title, $lang);
    }


    /**
     * Get the description of an OrgUnit type. If no language code is given, a translation in the user-language is
     * returned. If no such translation exists, the description of the default language is substituted.
     * If a language code is provided, returns description for the given language or null.
     *
     * @param string $a_lang_code
     * @return null|string
     */
    public function getDescription($a_lang_code=''){
        return $this->getTranslation('description', $a_lang_code);
    }


    /**
     * Set description of OrgUnit type.
     * If no lang code is given, sets description for default language.
     *
     * @param $a_description
     * @param string $a_lang_code
     */
    public function setDescription($a_description, $a_lang_code='') {
        $lang = ($a_lang_code) ? $a_lang_code : $this->getDefaultLang();
        $this->setTranslation('description', $a_description, $lang);
    }

    /**
     * Get an array of IDs of ilObjOrgUnit objects using this type
     *
     * @param bool $include_deleted
     * @return array
     */
    public function getOrgUnitIds($include_deleted=true) {
        $cache_key = ($include_deleted) ? 1 : 0;
        if (is_array($this->orgus_ids[$cache_key])) {
            return $this->orgus_ids[$cache_key];
        }
        if ($include_deleted) {
            $sql = 'SELECT * FROM orgu_data WHERE orgu_type_id = ' . $this->db->quote($this->getId(), 'integer');
        } else {
            $sql = 'SELECT DISTINCT orgu_id FROM orgu_data od '.
                   'JOIN object_reference oref ON oref.obj_id = od.orgu_id '.
                   'WHERE od.orgu_type_id = ' . $this->db->quote($this->getId(), 'integer').
                   ' AND oref.deleted IS NULL';
        }
        $set = $this->db->query($sql);
        $this->orgus_ids[$cache_key] = array();
        while ($rec = $this->db->fetchObject($set)) {
            $this->orgus_ids[$cache_key][] = $rec->orgu_id;
        }
        return $this->orgus_ids[$cache_key];
    }

    /**
     * Get an array of ilObjOrgUnit objects using this type
     *
     * @param bool $include_deleted True if also deleted OrgUnits are returned
     * @return array
     */
    public function getOrgUnits($include_deleted=true) {
        $cache_key = ($include_deleted) ? 1 : 0;
        if (is_array($this->orgus[$cache_key])) {
            return $this->orgus[$cache_key];
        }
        $this->orgus[$cache_key] = array();
        $ids = $this->getOrgUnitIds($include_deleted);
        foreach ($ids as $id) {
            $orgu = new ilObjOrgUnit($id, false);
            if (!$include_deleted) {
                // Check if OrgUnit is in trash (each OrgUnit does only have one reference)
                $ref_ids = ilObject::_getAllReferences($id);
                $ref_ids = array_values($ref_ids);
                $ref_id = $ref_ids[0];
                if ($orgu->_isInTrash($ref_id)) {
                    continue;
                }
            }
            $this->orgus[$cache_key][] = $orgu;
        }
        return $this->orgus[$cache_key];
    }

    /**
     * Get assigned AdvancedMDRecord objects
     *
     * @param bool $a_only_active True if only active AMDRecords are returned
     * @return array
     */
    public function getAssignedAdvancedMDRecords($a_only_active=false) {
        $active = ($a_only_active) ? 1 : 0; // Cache key
        if (is_array($this->amd_records_assigned[$active])) {
            return $this->amd_records_assigned[$active];
        }
        $this->amd_records_assigned[$active] = array();
        $sql = 'SELECT * FROM orgu_types_adv_md_rec WHERE type_id = ' . $this->db->quote($this->getId(), 'integer');
        $set = $this->db->query($sql);
        while ($rec = $this->db->fetchObject($set)) {
            $amd_record = new ilAdvancedMDRecord($rec->rec_id);
            if ($a_only_active) {
                if ($amd_record->isActive()) {
                    $this->amd_records_assigned[1][] = $amd_record;
                }
            } else {
                $this->amd_records_assigned[0][] = $amd_record;
            }
        }
        return $this->amd_records_assigned[$active];
    }

    /**
     * Get IDs of assigned AdvancedMDRecord objects
     *
     * @param bool $a_only_active True if only IDs of active AMDRecords are returned
     * @return array
     */
    public function getAssignedAdvancedMDRecordIds($a_only_active=false) {
        $ids = array();
        /** @var ilAdvancedMDRecord $record */
        foreach ($this->getAssignedAdvancedMDRecords($a_only_active) as $record) {
            $ids[] = $record->getRecordId();
        }
        return $ids;
    }

    /**
     * Get all available AdvancedMDRecord objects for OrgUnits/Types
     *
     * @return array
     */
    public static function getAvailableAdvancedMDRecords() {
        if (is_array(self::$amd_records_available)) {
            return self::$amd_records_available;
        }
        self::$amd_records_available = ilAdvancedMDRecord::_getActivatedRecordsByObjectType('orgu', 'orgu_type');
        return self::$amd_records_available;
    }

    /**
     * Get IDs of all available AdvancedMDRecord objects for OrgUnit/Types
     *
     * @return array
     */
    public static function getAvailableAdvancedMDRecordIds() {
        $ids = array();
        /** @var ilAdvancedMDRecord $record */
        foreach (self::getAvailableAdvancedMDRecords() as $record) {
            $ids[] = $record->getRecordId();
        }
        return $ids;
    }


    /**
     * Assign a given AdvancedMDRecord to this type.
     * If the AMDRecord is already assigned, nothing is done. If the AMDRecord cannot be assigned to OrgUnits/Types,
     * an Exception is thrown. Otherwise the AMDRecord is assigned (relation gets stored in DB).
     *
     * @param int $a_record_id
     * @throws ilOrgUnitTypePluginException
     * @throws ilOrgUnitTypeException
     */
    public function assignAdvancedMDRecord($a_record_id) {
        if (!in_array($a_record_id, $this->getAssignedAdvancedMDRecordIds())) {
            if (!in_array($a_record_id, self::getAvailableAdvancedMDRecordIds())) {
                throw new ilOrgUnitTypeException("AdvancedMDRecord with ID {$a_record_id} cannot be assigned to OrgUnit types");
            }
            /** @var ilOrgUnitTypeHookPlugin $plugin */
            $disallowed = array();
            $titles = array();
            foreach ($this->getActivePlugins() as $plugin) {
                if (!$plugin->allowAssignAdvancedMDRecord($this->getId(), $a_record_id)) {
                    $disallowed[] = $plugin;
                    $titles[] = $plugin->getPluginName();
                }
            }
            if (count($disallowed)) {
                $msg = sprintf($this->lng->txt('orgu_type_msg_assign_amd_prevented'), implode(', ', $titles));
                throw new ilOrgUnitTypePluginException($msg, $disallowed);
            }
            $record_ids = $this->getAssignedAdvancedMDRecordIds();
            $record_ids[] = $a_record_id;
            $this->db->insert('orgu_types_adv_md_rec', array(
               'type_id' => array('integer', $this->getId()),
               'rec_id' => array('integer', $a_record_id),
            ));
            // We need to update each OrgUnit from this type and map the selected records to object_id
            foreach ($this->getOrgUnitIds() as $orgu_id) {
                ilAdvancedMDRecord::saveObjRecSelection($orgu_id, 'orgu_type', $record_ids);
            }
            $this->amd_records_assigned = null; // Force reload of assigned objects
        }
    }


    /**
     * Deassign a given AdvancedMD record from this type.
     *
     * @param int $a_record_id
     * @throws ilOrgUnitTypePluginException
     */
    public function deassignAdvancedMdRecord($a_record_id) {
        $record_ids = $this->getAssignedAdvancedMDRecordIds();
        $key = array_search($a_record_id, $record_ids);
        if ($key !== false) {
            /** @var ilOrgUnitTypeHookPlugin $plugin */
            $disallowed = array();
            $titles = array();
            foreach ($this->getActivePlugins() as $plugin) {
                if (!$plugin->allowDeassignAdvancedMDRecord($this->getId(), $a_record_id)) {
                    $disallowed[] = $plugin;
                    $titles[] = $plugin->getPluginName();
                }
            }
            if (count($disallowed)) {
                $msg = sprintf($this->lng->txt('orgu_type_msg_deassign_amd_prevented'), implode(', ', $titles));
                throw new ilOrgUnitTypePluginException($msg, $disallowed);
            }
            unset($record_ids[$key]);
            $sql = 'DELETE FROM orgu_types_adv_md_rec
                    WHERE type_id = ' . $this->db->quote($this->getId(), 'integer') . '
                    AND rec_id = ' . $this->db->quote($a_record_id, 'integer');
            $this->db->query($sql);
            // We need to update each OrgUnit from this type and map the selected records to object_id
            foreach ($this->getOrgUnitIds() as $orgu_id) {
                ilAdvancedMDRecord::saveObjRecSelection($orgu_id, 'orgu_type', $record_ids);
            }
            $this->amd_records_assigned = null; // Force reload of assigned objects
        }
    }


    /**
     * Resize and store an icon file for this object
     *
     * @param array $file_data The array containing file information from the icon from PHPs $_FILES array
     * @return bool
     */
    public function processAndStoreIconFile(array $file_data) {
        if (!$this->updateable()) {
            return false;
        }
        if (!count($file_data) || !$file_data['name']) {
            return false;
        }
        if (!is_dir($this->getIconPath())) {
            ilUtil::makeDirParents($this->getIconPath());
        }
        $filename = $this->getIcon() ? $this->getIcon() : $file_data['name'];
        $return = ilUtil::moveUploadedFile($file_data['tmp_name'], $filename, $this->getIconPath(true), false);
            // TODO Resize
        return $return;
    }


    /**
     * Remove the icon file on disk
     */
    public function removeIconFile() {
        if (!$this->updateable()) {
            return;
        }
        if (is_file($this->getIconPath(true))) {
            unlink($this->getIconPath(true));
            $this->setIcon('');
        }
    }


    /**
     * Protected
     */


    /**
     * Helper method to return a translation for a given member and language
     *
     * @param $a_member
     * @param $a_lang_code
     * @return null|string
     */
    protected function getTranslation($a_member, $a_lang_code) {
        $lang = ($a_lang_code) ? $a_lang_code : $this->user->getLanguage();
        $trans_obj = $this->loadTranslation($lang);
        if (!is_null($trans_obj)) {
            $translation = $trans_obj->getMember($a_member);
            // If the translation does exist but is an empty string and there was no lang code given,
            // substitute default language anyway because an empty string provides no information
            if (!$a_lang_code && !$translation) {
                $trans_obj = $this->loadTranslation($this->getDefaultLang());
                return $trans_obj->getMember($a_member);
            }
            return $translation;
        } else {
            // If no lang code was given and there was no translation found, return string in default language
            if (!$a_lang_code) {
                $trans_obj = $this->loadTranslation($this->getDefaultLang());
                return $trans_obj->getMember($a_member);
            }
            return null;
        }
    }

    /**
     * Helper method to set a translation for a given member and language
     *
     * @param string $a_member
     * @param string $a_value
     * @param string $a_lang_code
     * @throws ilOrgUnitTypePluginException
     */
    protected function setTranslation($a_member, $a_value, $a_lang_code) {
        $a_value = trim($a_value);
        // If the value is identical, quit early and do not execute plugin checks
        $existing_translation = $this->getTranslation($a_member, $a_lang_code);
        if ($existing_translation == $a_value) {
            return;
        }
        // #19 Title should be unique per language
//        if ($a_value && $a_member == 'title') {
//            if (ilOrgUnitTypeTranslation::exists($this->getId(), 'title', $a_lang_code, $a_value)) {
//                throw new ilOrgUnitTypeException($this->lng->txt('orgu_type_msg_title_already_exists'));
//            }
//        }
        $disallowed = array();
        $titles = array();
        /** @var ilOrgUnitTypeHookPlugin $plugin */
        foreach ($this->getActivePlugins() as $plugin) {
            $allowed = true;
            if ($a_member == 'title') {
                $allowed = $plugin->allowSetTitle($this->getId(), $a_lang_code, $a_value);
            } else if ($a_member == 'description') {
                $allowed = $plugin->allowSetDescription($this->getId(), $a_lang_code, $a_value);
            }
            if (!$allowed) {
                $disallowed[] = $plugin;
                $titles[] = $plugin->getPluginName();
            }
        }
        if (count($disallowed)) {
            $msg = sprintf($this->lng->txt('orgu_type_msg_setting_member_prevented'), $a_value, implode(', ', $titles));
            throw new ilOrgUnitTypePluginException($msg, $disallowed);
        }
        $trans_obj = $this->loadTranslation($a_lang_code);
        if (!is_null($trans_obj)) {
            $trans_obj->setMember($a_member, $a_value);
        } else {
            $trans_obj = new ilOrgUnitTypeTranslation();
            $trans_obj->setOrguTypeId($this->getId());
            $trans_obj->setLang($a_lang_code);
            $trans_obj->setMember($a_member, $a_value);
            $this->translations[$a_lang_code] = $trans_obj;
            // Create language object here if this type is already in DB.
            // Otherwise, translations are created when calling create() on this object.
            if ($this->getId()) {
                $trans_obj->create();
            }
        }
    }

    /**
     * Get array of all acitve plugins for the ilOrgUnitTypeHook plugin slot
     *
     * @return array
     */
    protected function getActivePlugins() {
        if ($this->active_plugins === null) {
            $active_plugins = $this->pluginAdmin->getActivePluginsForSlot(IL_COMP_MODULE, 'OrgUnit', 'orgutypehk');
            $this->active_plugins = array();
            foreach ($active_plugins as $pl_name) {
                /** @var ilOrgUnitTypeHookPlugin $plugin */
                $plugin = $this->pluginAdmin->getPluginObject(IL_COMP_MODULE, 'OrgUnit', 'orgutypehk', $pl_name);
                $this->active_plugins[] = $plugin;
            }
        }
        return $this->active_plugins;
    }

    /**
     * Helper function to load a translation.
     * Returns translation object from cache or null, if no translation exists for the given code.
     *
     * @param string $a_lang_code A language code
     * @return ilOrgUnitTypeTranslation|null
     */
    protected function loadTranslation($a_lang_code) {
        if (isset($this->translations[$a_lang_code])) {
            return $this->translations[$a_lang_code];
        } else {
            $trans_obj = ilOrgUnitTypeTranslation::getInstance($this->getId(), $a_lang_code);
            if (!is_null($trans_obj)) {
                $this->translations[$a_lang_code] = $trans_obj;
                return $trans_obj;
            }
        }
        return null;
    }


    /**
     * Read object data from database
     *
     * @throws ilOrgUnitTypeException
     */
    protected function read() {
        $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = ' . $this->db->quote($this->id, 'integer');
        $set = $this->db->query($sql);
        if (!$this->db->numRows($set)) {
            throw new ilOrgUnitTypeException("OrgUnit type with id {$this->id} does not exist in database");
        }
        $rec = $this->db->fetchObject($set);
        $this->default_lang = $rec->default_lang; // Don't use Setter because of unnecessary plugin checks
        $this->setCreateDate($rec->create_date);
        $this->setLastUpdate($rec->last_update);
        $this->setOwner($rec->owner);
        $this->setIcon($rec->icon);
    }

    /**
     * Helper function to check if this type can be updated
     *
     * @return bool
     */
    protected function updateable() {
        foreach ($this->getActivePlugins() as $plugin) {
            if (!$plugin->allowUpdate($this->getId())) {
                return false;
            }
        }
        return true;
    }


    /**
     * Getters & Setters
     */


    /**
     * @param array $translations
     */
    public function setTranslations($translations)
    {
        $this->translations = $translations;
    }

    /**
     * Returns the loaded translation objects
     *
     * @return array
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Returns all existing translation objects
     *
     * @return array
     */
    public function getAllTranslations() {
        $translations = ilOrgUnitTypeTranslation::getAllTranslations($this->getId());
        /** @var ilOrgUnitTypeTranslation $trans */
        foreach ($translations as $trans) {
            $this->translations[$trans->getLang()] = $trans;
        }
        return $this->translations;
    }

    /**
     * @param int $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return int
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param string $last_update
     */
    public function setLastUpdate($last_update)
    {
        $this->last_update = $last_update;
    }

    /**
     * @return string
     */
    public function getLastUpdate()
    {
        return $this->last_update;
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set new Icon filename.
     *
     * Note that if you did also send a new icon image file with a form, make sure to call
     * ilOrgUnitType::processAndStoreIconFile() to store the file additionally on disk.
     *
     * If you want to delete the icon, set call ilOrgUnitType::removeIconFile() first and set an empty string here.
     *
     * @param string $icon
     * @throws ilOrgUnitTypeException
     */
    public function setIcon($icon)
    {
        if ($icon && !preg_match('/\.(jpg|jpeg|png|gif)$/', $icon)) {
            throw new ilOrgUnitTypeException('Icon must be set with file extension (jpg,jpeg,png or gif)');
        }
        $this->icon = $icon;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Return the path to the icon
     *
     * @param bool $append_filename If true, append filename of icon
     * @return string
     */
    public function getIconPath($append_filename=false) {
        $path = ilUtil::getWebspaceDir() . DIRECTORY_SEPARATOR . self::WEB_DATA_FOLDER . DIRECTORY_SEPARATOR . "type_" . $this->getId() . '/';
        if ($append_filename) {
            $path .= $this->getIcon();
        }
        return $path;
    }

    /**
     * @param string $default_lang
     * @throws ilOrgUnitTypePluginException
     */
    public function setDefaultLang($default_lang)
    {
        // If the new default_lang is identical, quit early and do not execute plugin checks
        if ($this->default_lang == $default_lang) {
            return;
        }
        $disallowed = array();
        $titles = array();
        /** @var ilOrgUnitTypeHookPlugin $plugin */
        foreach ($this->getActivePlugins() as $plugin) {
            if (!$plugin->allowSetDefaultLanguage($this->getId(), $default_lang)) {
                $disallowed[] = $plugin;
                $titles[] = $plugin->getPluginName();
            }
        }
        if (count($disallowed)) {
            $msg = sprintf($this->lng->txt('orgu_type_msg_setting_default_lang_prevented'), $default_lang, implode(', ', $titles));
            throw new ilOrgUnitTypePluginException($msg, $disallowed);
        }

        $this->default_lang = $default_lang;
    }

    /**
     * @return string
     */
    public function getDefaultLang()
    {
        return $this->default_lang;
    }


    /**
     * @param string $create_date
     */
    public function setCreateDate($create_date)
    {
        $this->create_date = $create_date;
    }

    /**
     * @return string
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

}