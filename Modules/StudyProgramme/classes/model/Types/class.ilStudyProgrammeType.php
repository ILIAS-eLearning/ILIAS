<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\Filesystem\Filesystem;

/**
 * Class ilStudyProgrammeType
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilStudyProgrammeType
{
    /**
     * Folder in ILIAS webdir to store the icons
     */
    private const WEB_DATA_FOLDER = 'prg_data';

    public const DATE_TIME_FORMAT = 'Y-m-d H:i:s';
    public const DATE_FORMAT = 'Y-m-d';

    protected string $default_lang = '';
    protected int $owner;
    protected DateTime $create_date;
    protected DateTime $last_update;
    protected string $icon;
    protected ilLog $log;
    protected ilObjUser $user;
    protected array $active_plugins;
    protected ilLanguage $lng;
    protected array $translations;
    protected array $changed_translations = array();

    protected int $id = 0;
    protected ilStudyProgrammeTypeRepository $type_repo;
    protected Filesystem $webdir;

    protected ilComponentFactory $component_factory;


    public function __construct(
        int $id,
        ilStudyProgrammeTypeRepository $type_repo,
        ILIAS\Filesystem\Filesystem $webdir,
        ilLanguage $lng,
        ilObjUser $user,
        ilComponentFactory $component_factory
    ) {
        $this->id = $id;
        $this->type_repo = $type_repo;
        $this->webdir = $webdir;
        $this->lng = $lng;
        $this->user = $user;
        $this->component_factory = $component_factory;
    }

    /**
     * Get the title of an StudyProgramme type. If no language code is given, a translation in the user-language is
     * returned. If no such translation exists, the translation of the default language is substituted.
     * If a language code is provided, returns title for the given language or null.
     */
    public function getTitle(string $a_lang_code = '') : string
    {
        return (string) $this->getTranslation('title', $a_lang_code);
    }

    /**
     * Set title of StudyProgramme type.
     * If no lang code is given, sets title for default language.
     */
    public function setTitle(string $title, string $lang_code = '') : void
    {
        $lang = ($lang_code) ?: $this->getDefaultLang();
        $this->setTranslation('title', $title, $lang);
    }

    /**
     * Get the description of an StudyProgramme type. If no language code is given, a translation in the user-language is
     * returned. If no such translation exists, the description of the default language is substituted.
     * If a language code is provided, returns description for the given language or null.
     */
    public function getDescription(string $lang_code = '') : string
    {
        return (string) $this->getTranslation('description', $lang_code);
    }

    /**
     * Set description of StudyProgramme type.
     * If no lang code is given, sets description for default language.
     */
    public function setDescription(string $description, string $lang_code = '') : void
    {
        $lang = ($lang_code) ?: $this->getDefaultLang();
        $this->setTranslation('description', $description, $lang);
    }

    /**
     * Update the Icons of assigned objects.
     */
    public function updateAssignedStudyProgrammesIcons() : void
    {
        $obj_ids = $this->type_repo->getStudyProgrammeIdsByTypeId($this->getId());

        foreach ($obj_ids as $id) {
            $ref_id = ilObject::_getAllReferences($id);
            $osp = ilObjStudyProgramme::getInstanceByRefId(array_pop($ref_id));
            $osp->updateCustomIcon();
        }
    }

    /**
     * Assign a given AdvancedMDRecord to this type.
     * If the AMDRecord is already assigned, nothing is done. If the AMDRecord cannot be assigned to StudyProgrammes/Types,
     * an Exception is thrown. Otherwise the AMDRecord is assigned (relation gets stored in DB).
     *
     * @throws ilStudyProgrammeTypePluginException
     * @throws ilStudyProgrammeTypeException
     */
    public function assignAdvancedMDRecord(int $record_id) : void
    {
        $assigned_amd_records = $this->type_repo->getAssignedAMDRecordIdsByType($this->getId());
        if (!in_array($record_id, $assigned_amd_records)) {
            if (!in_array($record_id, $this->type_repo->getAllAMDRecordIds())) {
                throw new ilStudyProgrammeTypeException(
                    "AdvancedMDRecord with ID $record_id cannot be assigned to StudyProgramme types"
                );
            }
            /** @var ilStudyProgrammeTypeHookPlugin $plugin */
            $disallowed = array();
            $titles = array();
            foreach ($this->getActivePlugins() as $plugin) {
                if (!$plugin->allowAssignAdvancedMDRecord($this->getId(), $record_id)) {
                    $disallowed[] = $plugin;
                    $titles[] = $plugin->getPluginName();
                }
            }
            if (count($disallowed)) {
                $msg = sprintf($this->lng->txt('prg_type_msg_assign_amd_prevented'), implode(', ', $titles));
                throw new ilStudyProgrammeTypePluginException($msg, $disallowed);
            }
            $record_ids = $assigned_amd_records;
            $record_ids[] = $record_id;

            $amd_records = $this->type_repo->getAMDRecordsByTypeIdAndRecordId($this->getId(), $record_id);
            $exists = array_shift($amd_records);

            if (!$exists) {
                $advanced_meta = $this->type_repo->createAMDRecord();
                $advanced_meta->setTypeId($this->getId());
                $advanced_meta->setRecId($record_id);
                $this->type_repo->updateAMDRecord($advanced_meta);
            }

            // We need to update each StudyProgramme from this type and map the selected records to object_id
            foreach ($this->type_repo->getAssignedAMDRecordIdsByType($this->getId()) as $prg_id) {
                ilAdvancedMDRecord::saveObjRecSelection($prg_id, 'prg_type', $record_ids);
            }
        }
    }


    /**
     * Deassign a given AdvancedMD record from this type.
     *
     * @throws ilStudyProgrammeTypePluginException
     */
    public function deassignAdvancedMdRecord(int $record_id) : void
    {
        $record_ids = $this->type_repo->getAssignedAMDRecordIdsByType($this->getId());
        $key = array_search($record_id, $record_ids);
        if ($key !== false) {
            /** @var ilStudyProgrammeTypeHookPlugin $plugin */
            $disallowed = array();
            $titles = array();
            foreach ($this->getActivePlugins() as $plugin) {
                if (!$plugin->allowDeassignAdvancedMDRecord($this->getId(), $record_id)) {
                    $disallowed[] = $plugin;
                    $titles[] = $plugin->getPluginName();
                }
            }
            if (count($disallowed)) {
                $msg = sprintf($this->lng->txt('prg_type_msg_deassign_amd_prevented'), implode(', ', $titles));
                throw new ilStudyProgrammeTypePluginException($msg, $disallowed);
            }
            unset($record_ids[$key]);

            foreach ($this->type_repo->getAMDRecordsByTypeIdAndRecordId($this->getId(), $record_id) as $amd_record) {
                $this->type_repo->deleteAMDRecord($amd_record);
            }

            // We need to update each StudyProgramme from this type and map the selected records to object_id
            foreach ($this->type_repo->getStudyProgrammeIdsByTypeId($this->getId()) as $prg_id) {
                ilAdvancedMDRecord::saveObjRecSelection($prg_id, 'prg_type', $record_ids);
            }
        }
    }

    /**
     * Resize and store an icon file for this object
     *
     * @param array $file_data The array containing file information from the icon from PHPs $_FILES array
     */
    public function processAndStoreIconFile(array $file_data) : bool
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

        if ($this->webdir->has($this->getIconPath(true))) {
            $this->webdir->delete($this->getIconPath(true));
        }

        $stream = ILIAS\Filesystem\Stream\Streams::ofResource(fopen($file_data["tmp_name"], 'rb'));
        $this->webdir->writeStream($this->getIconPath(true), $stream);

        return true;
    }

    /**
     * Remove the icon file on disk
     */
    public function removeIconFile() : void
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
     */
    protected function getTranslation(string $member, string $lang_code) : ?string
    {
        $lang = ($lang_code) ?: $this->user->getLanguage();

        $trans_obj = $this->loadTranslation($lang);
        if (!is_null($trans_obj)) {
            $translation = $trans_obj[$member] ?? null;
            // If the translation does exist but is an empty string and there was no lang code given,
            // substitute default language anyway because an empty string provides no information
            if (!$lang_code && !$translation) {
                $trans_obj = $this->loadTranslation($this->getDefaultLang());

                return $trans_obj[$member] ?? null;
            }

            return $translation;
        }

        // If no lang code was given and there was no translation found, return string in default language
        if (!$lang_code) {
            $trans_obj = $this->loadTranslation($this->getDefaultLang());

            return $trans_obj[$member] ?? null;
        }

        return null;
    }

    protected function loadTranslation(string $lang_code) : ?array
    {
        if (isset($this->translations[$lang_code])) {
            return $this->translations[$lang_code];
        } else {
            $trans_array = $this->type_repo->getTranslationsByTypeAndLang($this->getId(), $lang_code);

            //ilStudyProgrammeTypeTranslation::where(array('prg_type_id'=>$this->getId(), 'lang'=>$a_lang_code))->getArray('member', 'value');
            if (count($trans_array)) {
                $this->translations[$lang_code] = $trans_array;

                return $trans_array;
            }
        }

        return null;
    }

    /**
     * Helper function to check if this type can be updated
     */
    protected function updateable() : bool
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

    public function setTranslations(array $translations) : void
    {
        $this->translations = $translations;
    }

    protected function setTranslation(string $member, string $value, string $lang_code) : void
    {
        $value = trim($value);
        // If the value is identical, quit early and do not execute plugin checks
        $existing_translation = $this->getTranslation($member, $lang_code);
        if ($existing_translation == $value) {
            return;
        }
        // #19 Title should be unique per language
        //        if ($value && $member == 'title') {
        //            if (ilStudyProgrammeTypeTranslation::exists($this->getId(), 'title', $lang_code, $value)) {
        //                throw new ilStudyProgrammeTypeException($this->lng->txt('prg_type_msg_title_already_exists'));
        //            }
        //        }
        $disallowed = array();
        $titles = array();
        /** @var ilStudyProgrammeTypeHookPlugin $plugin */
        foreach ($this->getActivePlugins() as $plugin) {
            $allowed = true;
            if ($member === 'title') {
                $allowed = $plugin->allowSetTitle($this->getId(), $lang_code, $value);
            } elseif ($member === 'description') {
                $allowed = $plugin->allowSetDescription($this->getId(), $lang_code, $value);
            }
            if (!$allowed) {
                $disallowed[] = $plugin;
                $titles[] = $plugin->getPluginName();
            }
        }
        if (count($disallowed)) {
            $msg = sprintf($this->lng->txt('prg_type_msg_setting_member_prevented'), $value, implode(', ', $titles));
            throw new ilStudyProgrammeTypePluginException($msg, $disallowed);
        }
        $trans_obj = $this->type_repo->getTranslationByTypeIdMemberLang(
            $this->getId(),
            $member,
            $lang_code
        );
        if (!$trans_obj) {
            $trans_obj = $this->type_repo->createTypeTranslation();
            $trans_obj->setPrgTypeId($this->getId());
            $trans_obj->setLang($lang_code);
            $trans_obj->setMember($member);
        }
        $trans_obj->setValue($value);
        $this->type_repo->updateTypeTranslation($trans_obj);
        $this->translations[$lang_code][$member] = $value;
        $this->changed_translations[$lang_code][] = $trans_obj;
    }


    public function getTranslations() : ?array
    {
        return $this->translations;
    }

    public function setOwner(int $owner) : void
    {
        $this->owner = $owner;
    }

    public function getOwner() : int
    {
        return $this->owner;
    }

    public function setLastUpdate(DateTime $last_update) : void
    {
        $this->last_update = $last_update;
    }

    public function getLastUpdate() : DateTime
    {
        return $this->last_update;
    }

    public function getId() : int
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
     * @throws ilStudyProgrammeTypeException
     */
    public function setIcon(string $icon) : void
    {
        if ($icon && !preg_match('/\.(svg)$/', $icon)) {
            throw new ilStudyProgrammeTypeException('Icon must be set with file extension svg');
        }
        $this->icon = $icon;
    }

    public function getIcon() : string
    {
        return $this->icon;
    }

    /**
     * Return the path to the icon
     *
     * @param bool $append_filename If true, append filename of icon
     */
    public function getIconPath(bool $append_filename = false) : string
    {
        $path = self::WEB_DATA_FOLDER . '/' . 'type_' . $this->getId() . '/';
        if ($append_filename) {
            $path .= $this->getIcon();
        }

        return $path;
    }

    public function setDefaultLang(string $default_lang) : void
    {
        $this->default_lang = $default_lang;
    }

    public function getDefaultLang() : string
    {
        return $this->default_lang;
    }

    public function setCreateDate(DateTime $create_date) : void
    {
        $this->create_date = $create_date;
    }

    public function getCreateDate() : DateTime
    {
        return $this->create_date;
    }

    public function getRepository() : ilStudyProgrammeTypeRepository
    {
        return $this->type_repo;
    }

    protected function getActivePlugins() : Iterator
    {
        return $this->component_factory->getActivePluginsInSlot("prgtypehk");
    }

    public function changedTranslations() : array
    {
        return $this->changed_translations;
    }
}
