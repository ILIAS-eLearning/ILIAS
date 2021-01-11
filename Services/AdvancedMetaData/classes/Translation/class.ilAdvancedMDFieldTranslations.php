<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAdvancedMDFieldTranslations
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldTranslations
{
    /**
     * @var null | array
     */
    private static $instances = null;

    /**
     * @var int
     */
    private $record_id;

    /**
     * @var ilAdvancedMDRecord
     */
    private $record;

    /**
     * @var ilAdvancedMDFieldDefinition[]
     */
    private $definitions;

    /**
     * @var array
     */
    private $translations = [];

    /**
     * @var array
     */
    private $record_translations = [];


    /**
     * @var string
     */
    private $default_language = '';


    /**
     * @var ilDBInterface
     */
    private $db;

    /**
     * @var ilLanguage
     */
    private $lng;


    private function __construct(int $record_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('meta');

        $this->record_id = $record_id;
        $this->record_translations = ilAdvancedMDRecordTranslations::getInstanceByRecordId($this->record_id);
        $this->read();
    }

    /**
     * @param int $record_id
     * @return ilAdvancedMDFieldTranslations
     */
    public static function getInstanceByRecordId(int $record_id)
    {
        if (!isset(self::$instances[$record_id])) {
            self::$instances[$record_id] = new self($record_id);
        }
        return self::$instances[$record_id];
    }

    /**
     * @return int
     */
    public function getRecordId() : int
    {
        return $this->record_id;
    }


    /**
     * @return string
     */
    public function getDefaultLanguage() :string
    {
        return $this->default_language;
    }

    public function getActivatedLanguages(int $field_id, bool $with_default = true)
    {
        $activated = [];
        foreach ($this->getTranslations($field_id) as $language => $translation)
        {
            if ($language == self::getDefaultLanguage() && !$with_default) {
                continue;
            }
            $activated[] = $language;
        }
        return $activated;
    }


    /**
     * @param int    $field_id
     * @param string $lang_key
     * @return bool
     */
    public function isConfigured(int $field_id, string $lang_key)
    {
        if (!$this->record_translations->isConfigured($lang_key)) {
            return false;
        }
        return isset($this->translations[$field_id][$lang_key]);
    }


    /**
     * @param string $lang_key
     * @return ilAdvancedMDRecordTranslation|null
     */
    public function getTranslation(int $field_id, string $lang_key) :? ilAdvancedMDFieldTranslation
    {
        if (!$this->isConfigured($field_id, $lang_key)) {
            return null;
        }
        return $this->translations[$field_id][$lang_key];
    }

    /**
     * @return ilAdvancedMDFieldTranslation[]
     */
    public function getTranslations(int $field_id)
    {
        if (isset($this->translations[$field_id])) {
            return $this->translations[$field_id];
        }
        return [];
    }

    /**
     * @return ilAdvancedMDRecordTranslation|null
     */
    public function getDefaultTranslation(int $field_id) : ?ilAdvancedMDFieldTranslation
    {
        foreach ($this->getTranslations($field_id) as $translation) {
            if ($translation->getLangKey() == $this->default_language) {
                return $translation;
            }
        }
        return null;
    }

    public function read()
    {
        $query = 'select fi.field_id tfield, de.field_id ofield, fi.title, fi.description, ri.lang_code ' .
            'from adv_md_record_int ri join adv_mdf_definition de on ri.record_id = de.record_id ' .
            'left join adv_md_field_int fi on (ri.lang_code = fi.lang_code and de.field_id = fi.field_id) ' .
            'where ri.record_id = ' . $this->db->quote($this->getRecordId(), ilDBConstants::T_INTEGER);
        $res = $this->db->query($query);

        $this->record = ilAdvancedMDRecord::_getInstanceByRecordId($this->record_id);
        $this->default_language = $this->record->getDefaultLanguage();

        $this->translations = [];
        $this->definitions = ilAdvancedMDFieldDefinition::getInstancesByRecordId($this->record_id, false);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->translations[$row->ofield][$row->lang_code] = new ilAdvancedMDFieldTranslation(
                (int) $row->ofield,
                (string) $row->title,
                (string) $row->description,
                (string) $row->lang_code
            );
            if (
                $row->lang_code == $this->default_language &&
                $row->tfield == null
            ) {
                $this->translations[$row->ofield][$row->lang_code]->setTitle($this->definitions[$row->ofield]->getTitle());
                $this->translations[$row->ofield][$row->lang_code]->setDescription((string) $this->definitions[$row->ofield]->getDescription());
            }
        }
    }

    /**
     * @return string
     */
    public function getFormTranslationInfo(int $field_id, string $active_language) : string
    {
        if (count($this->getTranslations($field_id)) <= 1) {
            return '';
        }

        $txt = '';
        $txt = $this->lng->txt('md_adv_int_current'). ' ' . $this->lng->txt('meta_l_' . $active_language);
        $txt .= ', ';
        foreach ($this->getTranslations($field_id) as $translation) {
            if ($translation->getLangKey() == $this->getDefaultLanguage()) {
                $txt .= ($this->lng->txt('md_adv_int_default') . ' ' . $this->lng->txt('meta_l_' . $translation->getLangKey()));
                break;
            }
        }
        return $txt;
    }

    /**
     * @param ilPropertyFormGUI $form
     * @param ilTextInputGUI    $title
     */
    public function modifyTranslationInfoForTitle(int $field_id, ilPropertyFormGUI $form, ilTextInputGUI $title, string $active_language)
    {
        if (!strlen($active_language)) {
            return;
        }
        $show_info = ($active_language !== $this->getDefaultLanguage() && $this->getDefaultLanguage());
        $default = $this->getDefaultTranslation($field_id);
        if ($default instanceof ilAdvancedMDFieldTranslation && $show_info) {
            $title->setInfo($default->getLangKey() . ': ' . $default->getTitle());
        }
        if ($this->getTranslation($field_id, $active_language) instanceof ilAdvancedMDFieldTranslation) {
            $title->setValue($this->getTranslation($field_id, $active_language)->getTitle());
        }
    }

    /**
     * @param ilPropertyFormGUI $form
     * @param ilTextInputGUI    $title
     */
    public function modifyTranslationInfoForDescription(int $field_id, ilPropertyFormGUI $form, ilTextAreaInputGUI $description, string $active_language)
    {
        if (!strlen($active_language)) {
            return;
        }
        $show_info = ($active_language !== $this->getDefaultLanguage() && $this->getDefaultLanguage());
        $default = $this->getDefaultTranslation($field_id);
        if ($default instanceof ilAdvancedMDFieldTranslation && $show_info) {
            $description->setInfo($default->getLangKey() . ': ' . $default->getDescription());
        }
        if ($this->getTranslation($field_id, $active_language) instanceof ilAdvancedMDFieldTranslation) {
            $description->setValue($this->getTranslation($field_id, $active_language)->getDescription());
        }
    }

    /**
     * @param int               $field_id
     * @param string            $active_language
     * @param ilPropertyFormGUI $form
     */
    public function updateFromForm(int $field_id, string $active_language, ilPropertyFormGUI $form)
    {
        $translation = $this->getTranslation($field_id, $active_language);
        if (!$translation instanceof ilAdvancedMDFieldTranslation) {
            return;
        }
        $translation->setTitle($form->getInput('title'));
        $translation->setDescription($form->getInput('description'));
        $translation->update();
        return;
    }

    /**
     * @param ilPropertyFormGUI $form
     * @param string            $active_language
     * @return bool
     */
    public function updateTranslations(string $active_language, string $title, string $description)
    {
        $translation = $this->getTranslation($active_language);
        if (!$translation instanceof ilAdvancedMDRecordTranslation) {
            return false;
        }
        $translation->setTitle($title);
        $translation->setDescription($description);
        $translation->update();
    }

    /**
     * @param string $language
     * @return string
     */
    public function getTitleForLanguage(int $field_id, string $language) : string
    {
        if ($this->getTranslation($field_id, $language) && strlen($this->getTranslation($field_id, $language)->getTitle())) {
            return $this->getTranslation($field_id, $language)->getTitle();
        }
        if (
            $this->getTranslation($field_id, $this->getDefaultLanguage()) &&
            strlen(($this->getTranslation($field_id, $this->getDefaultLanguage())->getTitle()))
        ) {
            return $this->getTranslation($field_id, $this->getDefaultLanguage())->getTitle();
        }
        if ($this->definitions[$field_id] instanceof ilAdvancedMDFieldDefinition) {
            return $this->definitions[$field_id]->getTitle();
        }
        return '';
    }
    /**
     * @param string $language
     * @return string
     */
    public function getDescriptionForLanguage(int $field_id, string $language)
    {
        if ($this->getTranslation($field_id, $language) && strlen($this->getTranslation($field_id, $language)->getDescription())) {
            return $this->getTranslation($field_id, $language)->getDescription();
        }
        if ($this->getTranslation($field_id, $this->getDefaultLanguage()) &&
            strlen($this->getTranslation($field_id, $this->getDefaultLanguage())->getDescription())
        ) {
            return $this->getTranslation($field_id, $this->getDefaultLanguage())->getDescription();
        }
        if ($this->definitions[$field_id] instanceof ilAdvancedMDFieldDefinition) {
            return $this->definitions[$field_id]->getDescription();
        }
        return '';
    }
}