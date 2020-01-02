<?php
require_once("./Services/ActiveRecord/class.ActiveRecord.php");
require_once('class.ilStudyProgrammeTypeTranslation.php');
require_once('./Modules/StudyProgramme/classes/exceptions/class.ilStudyProgrammeTypeException.php');
require_once('./Modules/StudyProgramme/classes/exceptions/class.ilStudyProgrammeTypePluginException.php');
require_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');
require_once('./Modules/StudyProgramme/classes/model/class.ilStudyProgramme.php');
require_once('./Modules/StudyProgramme/classes/model/class.ilStudyProgrammeAdvancedMetadataRecord.php');

/**
 * Class ilStudyProgrammeType
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilStudyProgrammeType extends ActiveRecord
{

    /**
     * Folder in ILIAS webdir to store the icons
     */
    const WEB_DATA_FOLDER = 'prg_data';

    /**
     * @var int
     *
     * @con_is_primary  true
     * @con_sequence    true
     * @con_is_unique   true
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected $id = 0;

    /**
     * @var string
     *
     * @con_has_field   true
     * @con_fieldtype   text
     * @con_length      4
     */
    protected $default_lang = '';

    /**
     * @var int
     *
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected $owner;

    /**
     * @var ilDateTime
     *
     * @con_has_field   true
     * @con_fieldtype   timestamp
     * @con_is_notnull  true
     */
    protected $create_date;

    /**
     * @var string
     *
     * @con_has_field   true
     * @con_fieldtype   timestamp
     * @con_is_notnull  false
     */
    protected $last_update;

    /**
     * @var string
     *
     * @con_has_field   true
     * @con_fieldtype   text
     * @con_length      255
     */
    protected $icon;


    /**
     * @var array
     */
    protected $amd_records_assigned;

    /**
     * @var array
     */
    protected static $amd_records_available;

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
     * @var array
     */
    protected $translations;
    /**
     * @var array with the changed TypeTranslations
     */
    protected $changed_translations = array();

    /**
     * @return string
     * @description Return the Name of your Database Table
     * @deprecated
     */
    public static function returnDbTableName()
    {
        return 'prg_type';
    }

    /**
     * @param int $a_id
     *
     * @throws ilStudyProgrammeTypeException
     */
    public function __construct($primary_key = 0)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilLog = $DIC['ilLog'];
        $ilUser = $DIC['ilUser'];
        $ilPluginAdmin = $DIC['ilPluginAdmin'];
        $lng = $DIC['lng'];

        $this->webdir = $DIC->filesystem()->web();
        $this->db = $ilDB;
        $this->log = $ilLog;
        $this->user = $ilUser;
        $this->pluginAdmin = $ilPluginAdmin;
        $this->lng = $lng;

        parent::__construct($primary_key);
    }

    /**
     * Public
     */

    /**
     * Get array of all instances of ilStudyProgrammeType objects
     *
     * @return array
     */
    public static function getAllTypes()
    {
        return self::get();
    }

    public static function getAllTypesArray()
    {
        $out = array();

        foreach (self::getAllTypes() as $type) {
            $out[$type->getId()] = $type->getTitle();
        }

        return $out;
    }

    /**
     * Create object in database. Also invokes creating of translation objects.
     *
     * @throws ilStudyProgrammeTypeException
     */
    public function create()
    {
        $default_lang = $this->getDefaultLang();
        $title = $this->getTranslation('title', $default_lang);
        if (!$default_lang || !$title) {
            throw new ilStudyProgrammeTypeException($this->lng->txt('prg_type_msg_missing_title_default_language'));
        }

        $this->setOwner($this->user->getId());
        $now = new ilDateTime(time(), IL_CAL_UNIX);
        $this->setCreateDate($now);
        $this->setLastUpdate($now);

        parent::create();

        // Create translation(s)
        /** @var $trans ilStudyProgrammeTypeTranslation */
        foreach ($this->changed_translations as $lang => $trans_objects) {
            foreach ($trans_objects as $trans) {
                $trans->setPrgTypeId($this->getId());
                $trans->store();
            }
        }
        $this->changed_translations = array();
    }


    /**
     * Update changes to database
     *
     * @throws ilStudyProgrammeTypePluginException
     * @throws ilStudyProgrammeTypeException
     */
    public function update()
    {
        $title = $this->getTranslation('title', $this->getDefaultLang());
        if (!$title) {
            throw new ilStudyProgrammeTypeException($this->lng->txt('prg_type_msg_missing_title'));
        }

        $disallowed = array();
        $titles = array();
        /** @var ilStudyProgrammeTypeHookPlugin $plugin */
        foreach ($this->getActivePlugins() as $plugin) {
            if (!$plugin->allowUpdate($this->getId())) {
                $disallowed[] = $plugin;
                $titles[] = $plugin->getPluginName();
            }
        }
        /*if (count($disallowed)) {
            $msg = sprintf($this->lng->txt('prg_type_msg_updating_prevented'), implode(', ', $titles));
            throw new ilStudyProgrammeTypePluginException($msg, $disallowed);
        }*/

        parent::update();

        // Update translation(s)
        /** @var $trans ilStudyProgrammeTypeTranslation */
        foreach ($this->changed_translations as $lang => $trans_objects) {
            foreach ($trans_objects as $trans) {
                $trans->setPrgTypeId($this->getId());
                $trans->store();
            }
        }
        $this->changed_translations = array();
    }

    /**
     * Delete object by removing all database entries.
     * Deletion is only possible if this type is not assigned to any StudyProgramme and if no plugin disallowed deletion process.
     *
     * @throws ilStudyProgrammeTypeException
     */
    public function delete()
    {
        $prgs = ilStudyProgramme::where(array('subtype_id'=>$this->getId()))->get();

        if (count($prgs)) {
            $titles = array();
            /** @var $prg ilStudyProgramme */
            foreach ($prgs as $key=>$prg) {
                require_once("Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php");
                $container = new ilObjStudyProgramme($prg->getObjId(), false);
                $titles[] = $container->getTitle();
            }

            throw new ilStudyProgrammeTypeException(sprintf($this->lng->txt('prg_type_msg_unable_delete'), implode(', ', $titles)));
        }

        $disallowed = array();
        $titles = array();

        /** @var ilStudyProgrammeTypeHookPlugin $plugin */
        foreach ($this->getActivePlugins() as $plugin) {
            if (!$plugin->allowDelete($this->getId())) {
                $disallowed[] = $plugin;
                $titles[] = $plugin->getPluginName();
            }
        }
        if (count($disallowed)) {
            $msg = sprintf($this->lng->txt('prg_type_msg_deletion_prevented'), implode(', ', $titles));
            throw new ilStudyProgrammeTypePluginException($msg, $disallowed);
        }

        parent::delete();

        // Reset Type of StudyProgrammes (in Trash)
        /*$this->db->update('prg_data', array(
            'prg_type_id' => array( 'integer', 0 ),
        ), array(
            'prg_type_id' => array( 'integer', $this->getId() ),
        ));*/

        // Delete all translations
        ilStudyProgrammeTypeTranslation::deleteAllTranslations($this->getId());

        // Delete icon & folder
        if ($this->webdir->has($this->getIconPath(true))) {
            $this->webdir->delete($this->getIconPath(true));
        }
        if ($this->webdir->has($this->getIconPath())) {
            $this->webdir->deleteDir($this->getIconPath());
        }

        // Delete relations to advanced metadata records
        $records = ilStudyProgrammeAdvancedMetadataRecord::where(array('type_id'=>$this->getId()))->get();
        foreach ($records as $record) {
            $record->delete();
        }
    }


    /**
     * Get the title of an StudyProgramme type. If no language code is given, a translation in the user-language is
     * returned. If no such translation exists, the translation of the default language is substituted.
     * If a language code is provided, returns title for the given language or null.
     *
     * @param string $a_lang_code
     *
     * @return null|string
     */
    public function getTitle($a_lang_code = '')
    {
        return $this->getTranslation('title', $a_lang_code);
    }


    /**
     * Set title of StudyProgramme type.
     * If no lang code is given, sets title for default language.
     *
     * @param        $a_title
     * @param string $a_lang_code
     */
    public function setTitle($a_title, $a_lang_code = '')
    {
        $lang = ($a_lang_code) ? $a_lang_code : $this->getDefaultLang();
        $this->setTranslation('title', $a_title, $lang);
    }


    /**
     * Get the description of an StudyProgramme type. If no language code is given, a translation in the user-language is
     * returned. If no such translation exists, the description of the default language is substituted.
     * If a language code is provided, returns description for the given language or null.
     *
     * @param string $a_lang_code
     *
     * @return null|string
     */
    public function getDescription($a_lang_code = '')
    {
        return $this->getTranslation('description', $a_lang_code);
    }


    /**
     * Set description of StudyProgramme type.
     * If no lang code is given, sets description for default language.
     *
     * @param        $a_description
     * @param string $a_lang_code
     */
    public function setDescription($a_description, $a_lang_code = '')
    {
        $lang = ($a_lang_code) ? $a_lang_code : $this->getDefaultLang();
        $this->setTranslation('description', $a_description, $lang);
    }

    /**
     * Get an array of ilObjStudyProgramme objects using this type
     *
     * @param bool $include_deleted True if also deleted StudyProgrammes are returned
     *
     * @return array
     */
    public function getAssignedStudyProgrammes($include_deleted = true)
    {
        return ilStudyProgramme::where(array('subtype_id'=>$this->getId()))->get();
    }

    public function getAssignedStudyProgrammeIds()
    {
        $study_programmes = $this->getAssignedStudyProgrammes();

        $out = array();
        foreach ($study_programmes as $study_program) {
            $out[] = $study_program->getObjId();
        }

        return $out;
    }

    /**
     * Update the Icons of assigned objects.
     *
     * @return void
     */
    public function updateAssignedStudyProgrammesIcons()
    {
        $obj_ids = $this->getAssignedStudyProgrammeIds();

        foreach ($obj_ids as $id) {
            $ref_id = ilObject::_getAllReferences($id);
            $osp = ilObjStudyProgramme::getInstanceByRefId(array_pop($ref_id));
            $osp->updateCustomIcon();
        }
    }

    /**
     * Get assigned AdvancedMDRecord objects
     *
     * @param bool $a_only_active True if only active AMDRecords are returned
     *
     * @return array
     */
    public function getAssignedAdvancedMDRecords($a_only_active = false)
    {
        $active = ($a_only_active) ? 1 : 0; // Cache key
        if (is_array($this->amd_records_assigned[$active])) {
            return $this->amd_records_assigned[$active];
        }
        $this->amd_records_assigned[$active] = array();
        $sets = ilStudyProgrammeAdvancedMetadataRecord::where(array('type_id'=>$this->getId()))->get();

        foreach ($sets as $set) {
            $amd_record = new ilAdvancedMDRecord($set->getRecId());
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
     *
     * @return array
     */
    public function getAssignedAdvancedMDRecordIds($a_only_active = false)
    {
        $ids = array();
        /** @var ilAdvancedMDRecord $record */
        foreach ($this->getAssignedAdvancedMDRecords($a_only_active) as $record) {
            $ids[] = $record->getRecordId();
        }

        return $ids;
    }


    /**
     * Get all available AdvancedMDRecord objects for StudyProgrammes/Types
     *
     * @return array
     */
    public static function getAvailableAdvancedMDRecords()
    {
        if (is_array(self::$amd_records_available)) {
            return self::$amd_records_available;
        }
        self::$amd_records_available = ilAdvancedMDRecord::_getActivatedRecordsByObjectType('prg', 'prg_type');

        return self::$amd_records_available;
    }


    /**
     * Get IDs of all available AdvancedMDRecord objects for StudyProgramme/Types
     *
     * @return array
     */
    public static function getAvailableAdvancedMDRecordIds()
    {
        $ids = array();
        /** @var ilAdvancedMDRecord $record */
        foreach (self::getAvailableAdvancedMDRecords() as $record) {
            $ids[] = $record->getRecordId();
        }

        return $ids;
    }


    /**
     * Assign a given AdvancedMDRecord to this type.
     * If the AMDRecord is already assigned, nothing is done. If the AMDRecord cannot be assigned to StudyProgrammes/Types,
     * an Exception is thrown. Otherwise the AMDRecord is assigned (relation gets stored in DB).
     *
     * @param int $a_record_id
     *
     * @throws ilStudyProgrammeTypePluginException
     * @throws ilStudyProgrammeTypeException
     */
    public function assignAdvancedMDRecord($a_record_id)
    {
        if (!in_array($a_record_id, $this->getAssignedAdvancedMDRecordIds())) {
            if (!in_array($a_record_id, self::getAvailableAdvancedMDRecordIds())) {
                throw new ilStudyProgrammeTypeException("AdvancedMDRecord with ID {$a_record_id} cannot be assigned to StudyProgramme types");
            }
            /** @var ilStudyProgrammeTypeHookPlugin $plugin */
            $disallowed = array();
            $titles = array();
            foreach ($this->getActivePlugins() as $plugin) {
                if (!$plugin->allowAssignAdvancedMDRecord($this->getId(), $a_record_id)) {
                    $disallowed[] = $plugin;
                    $titles[] = $plugin->getPluginName();
                }
            }
            if (count($disallowed)) {
                $msg = sprintf($this->lng->txt('prg_type_msg_assign_amd_prevented'), implode(', ', $titles));
                throw new ilStudyProgrammeTypePluginException($msg, $disallowed);
            }
            $record_ids = $this->getAssignedAdvancedMDRecordIds();
            $record_ids[] = $a_record_id;

            $exists = ilStudyProgrammeAdvancedMetadataRecord::where(array('type_id'=>$this->getId(), 'rec_id'=>$a_record_id))->first();

            if (!$exists) {
                $advanced_meta = new ilStudyProgrammeAdvancedMetadataRecord();
                $advanced_meta->setTypeId($this->getId());
                $advanced_meta->setRecId($a_record_id);
                $advanced_meta->create();
            }

            // We need to update each StudyProgramme from this type and map the selected records to object_id
            foreach ($this->getAssignedStudyProgrammeIds() as $prg_id) {
                ilAdvancedMDRecord::saveObjRecSelection($prg_id, 'prg_type', $record_ids);
            }
            $this->amd_records_assigned = null; // Force reload of assigned objects
        }
    }


    /**
     * Deassign a given AdvancedMD record from this type.
     *
     * @param int $a_record_id
     *
     * @throws ilStudyProgrammeTypePluginException
     */
    public function deassignAdvancedMdRecord($a_record_id)
    {
        $record_ids = $this->getAssignedAdvancedMDRecordIds();
        $key = array_search($a_record_id, $record_ids);
        if ($key !== false) {
            /** @var ilStudyProgrammeTypeHookPlugin $plugin */
            $disallowed = array();
            $titles = array();
            foreach ($this->getActivePlugins() as $plugin) {
                if (!$plugin->allowDeassignAdvancedMDRecord($this->getId(), $a_record_id)) {
                    $disallowed[] = $plugin;
                    $titles[] = $plugin->getPluginName();
                }
            }
            if (count($disallowed)) {
                $msg = sprintf($this->lng->txt('prg_type_msg_deassign_amd_prevented'), implode(', ', $titles));
                throw new ilStudyProgrammeTypePluginException($msg, $disallowed);
            }
            unset($record_ids[$key]);

            $records = ilStudyProgrammeAdvancedMetadataRecord::where(array('type_id'=>$this->getId(), 'rec_id'=>$a_record_id))->get();
            foreach ($records as $record) {
                $record->delete();
            }

            // We need to update each StudyProgramme from this type and map the selected records to object_id
            foreach ($this->getAssignedStudyProgrammeIds() as $prg_id) {
                ilAdvancedMDRecord::saveObjRecSelection($prg_id, 'prg_type', $record_ids);
            }
            $this->amd_records_assigned = null; // Force reload of assigned objects
        }
    }


    /**
     * Resize and store an icon file for this object
     *
     * @param array $file_data The array containing file information from the icon from PHPs $_FILES array
     *
     * @return bool
     */
    public function processAndStoreIconFile(array $file_data)
    {
        if (!$this->updateable()) {
            return false;
        }
        if (!count($file_data) || !$file_data['name']) {
            return false;
        }
        if (!$this->webdir->hasDir($this->getIconPath())) {
            $this->webdir->createDir($this->getIconPath());
        }

        $filename = $this->getIcon() ? $this->getIcon() : $file_data['name'];

        if ($this->webdir->has($this->getIconPath(true))) {
            $this->webdir->delete($this->getIconPath(true));
        }

        $stream = ILIAS\Filesystem\Stream\Streams::ofResource(fopen($file_data["tmp_name"], "r"));
        $this->webdir->writeStream($this->getIconPath(true), $stream);

        return true;
    }


    /**
     * Remove the icon file on disk
     */
    public function removeIconFile()
    {
        if (!$this->updateable()) {
            return;
        }

        if (
            !is_null($this->getIcon()) &&
            $this->getIcon() !== ""
        ) {
            $this->webdir->delete($this->getIconPath(true));
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
     *
     * @return null|string
     */
    protected function getTranslation($a_member, $a_lang_code)
    {
        $lang = ($a_lang_code) ? $a_lang_code : $this->user->getLanguage();

        $trans_obj = $this->loadTranslation($lang);
        if (!is_null($trans_obj)) {
            $translation = $trans_obj[$a_member];
            // If the translation does exist but is an empty string and there was no lang code given,
            // substitute default language anyway because an empty string provides no information
            if (!$a_lang_code && !$translation) {
                $trans_obj = $this->loadTranslation($this->getDefaultLang());

                return $trans_obj[$a_member];
            }

            return $translation;
        } else {
            // If no lang code was given and there was no translation found, return string in default language
            if (!$a_lang_code) {
                $trans_obj = $this->loadTranslation($this->getDefaultLang());

                return $trans_obj[$a_member];
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
     *
     * @throws ilStudyProgrammeTypePluginException
     */
    protected function setTranslation($a_member, $a_value, $a_lang_code)
    {
        $a_value = trim($a_value);
        // If the value is identical, quit early and do not execute plugin checks
        $existing_translation = $this->getTranslation($a_member, $a_lang_code);
        if ($existing_translation == $a_value) {
            return;
        }
        // #19 Title should be unique per language
        //        if ($a_value && $a_member == 'title') {
        //            if (ilStudyProgrammeTypeTranslation::exists($this->getId(), 'title', $a_lang_code, $a_value)) {
        //                throw new ilStudyProgrammeTypeException($this->lng->txt('prg_type_msg_title_already_exists'));
        //            }
        //        }
        $disallowed = array();
        $titles = array();
        /** @var ilStudyProgrammeTypeHookPlugin $plugin */
        foreach ($this->getActivePlugins() as $plugin) {
            $allowed = true;
            if ($a_member == 'title') {
                $allowed = $plugin->allowSetTitle($this->getId(), $a_lang_code, $a_value);
            } else {
                if ($a_member == 'description') {
                    $allowed = $plugin->allowSetDescription($this->getId(), $a_lang_code, $a_value);
                }
            }
            if (!$allowed) {
                $disallowed[] = $plugin;
                $titles[] = $plugin->getPluginName();
            }
        }
        if (count($disallowed)) {
            $msg = sprintf($this->lng->txt('prg_type_msg_setting_member_prevented'), $a_value, implode(', ', $titles));
            throw new ilStudyProgrammeTypePluginException($msg, $disallowed);
        }

        $trans_obj = ilStudyProgrammeTypeTranslation::where(array('prg_type_id'=>$this->getId(), 'member'=>$a_member, 'lang'=>$a_lang_code))->first();
        if (!$trans_obj) {
            $trans_obj = new ilStudyProgrammeTypeTranslation();
            $trans_obj->setPrgTypeId($this->getId());
            $trans_obj->setLang($a_lang_code);
            $trans_obj->setMember($a_member);
        }

        $trans_obj->setValue($a_value);

        $this->translations[$a_lang_code][$a_member] = $a_value;
        $this->changed_translations[$a_lang_code][] = $trans_obj;
    }


    /**
     * Get array of all acitve plugins for the ilStudyProgrammeTypeHook plugin slot
     *
     * @return array
     */
    protected function getActivePlugins()
    {
        if ($this->active_plugins === null) {
            $active_plugins = $this->pluginAdmin->getActivePluginsForSlot(IL_COMP_MODULE, 'StudyProgramme', 'prgtypehk');
            $this->active_plugins = array();
            foreach ($active_plugins as $pl_name) {
                /** @var ilStudyProgrammeTypeHookPlugin $plugin */
                $plugin = $this->pluginAdmin->getPluginObject(IL_COMP_MODULE, 'StudyProgramme', 'prgtypehk', $pl_name);
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
     *
     * @return ActiveRecordList|null
     */
    protected function loadTranslation($a_lang_code)
    {
        if (isset($this->translations[$a_lang_code])) {
            return $this->translations[$a_lang_code];
        } else {
            $trans_array = ilStudyProgrammeTypeTranslation::where(array('prg_type_id'=>$this->getId(), 'lang'=>$a_lang_code))->getArray('member', 'value');
            if (count($trans_array)) {
                $this->translations[$a_lang_code] = $trans_array;

                return $trans_array;
            }
        }

        return null;
    }

    /**
     * Helper function to check if this type can be updated
     *
     * @return bool
     */
    protected function updateable()
    {
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
    public function getAllTranslations()
    {
        $translations = ilStudyProgrammeTypeTranslation::where(array('prg_type_id'=>$this->getId()))->get();
        /** @var ilStudyProgrammeTypeTranslation $trans */
        foreach ($translations as $trans) {
            $this->translations[$trans->getLang()] = $trans->getArray('member', 'value');
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
     * ilStudyProgrammeType::processAndStoreIconFile() to store the file additionally on disk.
     *
     * If you want to delete the icon, set call ilStudyProgrammeType::removeIconFile() first and set an empty string here.
     *
     * @param string $icon
     *
     * @throws ilStudyProgrammeTypeException
     */
    public function setIcon($icon)
    {
        if ($icon and !preg_match('/\.(svg)$/', $icon)) {
            throw new ilStudyProgrammeTypeException('Icon must be set with file extension svg');
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
     *
     * @return string
     */
    public function getIconPath($append_filename = false)
    {
        $path = self::WEB_DATA_FOLDER . '/' . 'type_' . $this->getId() . '/';
        if ($append_filename) {
            $path .= $this->getIcon();
        }

        return $path;
    }

    /**
    * Return the path to the icon by studyprogramme obj id
    *
    * @param int 		$obj_id 	study prgramm obj id
    *
    * @return string 	icon path
    */
    public static function getIconPathByStudyProgrammObjId($obj_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $sql = "SELECT prgt.id, prgt.icon\n"
                . " FROM prg_type prgt\n"
                . " JOIN prg_settings prgs\n"
                    . " ON prgt.id = prgs.subtype_id\n"
                . " WHERE prgs.obj_id = " . $ilDB->quote($obj_id, "integer") . "\n";

        $res = $ilDB->query($sql);

        if ($ilDB->numRows($res) == 1) {
            $row = $ilDB->fetchAssoc($res);

            if ($row["icon"]) {
                $path = self::WEB_DATA_FOLDER . '/' . 'type_' . $row["id"] . '/';
                $path .= $row["icon"];

                return $path;
            }

            return null;
        }

        return null;
    }

    /**
     * @param string $default_lang
     *
     * @throws ilStudyProgrammeTypePluginException
     */
    public function setDefaultLang($default_lang)
    {
        // If the new default_lang is identical, quit early and do not execute plugin checks
        if ($this->default_lang == $default_lang) {
            return;
        }
        $disallowed = array();
        $titles = array();
        /**
         * @var ilStudyProgrammeTypeHookPlugin $plugin
         */
        foreach ($this->getActivePlugins() as $plugin) {
            if (!$plugin->allowSetDefaultLanguage($this->getId(), $default_lang)) {
                $disallowed[] = $plugin;
                $titles[] = $plugin->getPluginName();
            }
        }
        if (count($disallowed)) {
            $msg = sprintf($this->lng->txt('prg_type_msg_setting_default_lang_prevented'), $default_lang, implode(', ', $titles));
            throw new ilStudyProgrammeTypePluginException($msg, $disallowed);
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
