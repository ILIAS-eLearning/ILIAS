<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAdvancedMDRecordTranslation
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDRecordTranslations
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
     * @var ilAdvancedMDRecordTranslation[]
     */
    private $translations = [];

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
        $this->read();
    }

    /**
     * @param int $record_id
     * @return ilAdvancedMDRecordTranslations
     */
    public static function getInstanceByRecordId(int $record_id)
    {
        if (!isset(self::$instances[$record_id])) {
            self::$instances[$record_id] = new self($record_id);
        }
        return self::$instances[$record_id];
    }

    /**
     * @return string
     */
    public function getDefaultLanguage() :string
    {
        return $this->default_language;
    }

    /**
     * @return int
     */
    public function getRecordId() : int
    {
        return $this->record_id;
    }

    /**
     * @param string $lang_key
     * @return bool
     */
    public function isConfigured(string $lang_key)
    {
        return isset($this->translations[$lang_key]);
    }

    /**
     * @param string $lang_key
     * @return ilAdvancedMDRecordTranslation|null
     */
    public function getTranslation(string $lang_key) :? ilAdvancedMDRecordTranslation
    {
        if (!$this->isConfigured($lang_key)) {
            return null;
        }
        return $this->translations[$lang_key];
   }

    /**
     * @return ilAdvancedMDRecordTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @return ilAdvancedMDRecordTranslation|null
     */
    public function getDefaultTranslation() : ?ilAdvancedMDRecordTranslation
    {
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLangKey() == $this->default_language) {
                return $translation;
            }
        }
        return null;
    }

    public function cloneRecord(int $new_record_id)
    {
        foreach ($this->getTranslations() as $recordTranslation) {
            $recordTranslation->setRecordId($new_record_id);
            $recordTranslation->insert();
        }
    }

    private function read()
    {
        $query = 'select * from ' . ilAdvancedMDRecordTranslation::TABLE_NAME . ' ' .
            'where record_id = ' . $this->db->quote($this->getRecordId(), ilDBConstants::T_INTEGER);
        $res = $this->db->query($query);

        $this->translations = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->translations[$row->lang_code] = new ilAdvancedMDRecordTranslation(
                (int) $row->record_id,
                (string) $row->title,
                (string) $row->description,
                (string) $row->lang_code
            );
        }

        $this->record = ilAdvancedMDRecord::_getInstanceByRecordId($this->record_id);
        $this->default_language = $this->record->getDefaultLanguage();
    }

    public function addTranslationEntry(string $language_code, bool $default = false)
    {
        $this->translations[$language_code] = new ilAdvancedMDRecordTranslation(
            $this->record_id,
            '',
            '',
            $language_code,
            $default
        );
        $this->translations[$language_code]->insert();
    }

    /**
     *
     */
    public function updateDefault(string $default)
    {
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLangKey() != $default) {
                $translation->setLangDefault(false);
                $translation->update();
            }
            if ($translation->getLangKey() == $default) {
                $translation->setLangDefault(true);
                $translation->update();
            }
        }
    }

    /**
     * @return string
     */
    public function getFormTranslationInfo(string $active_language) : string
    {
        if (count($this->translations) <= 1) {
            return '';
        }
        $txt = '';
        $txt = $this->lng->txt('md_adv_int_current'). ' ' . $this->lng->txt('meta_l_' . $active_language);
        $txt .= ', ';
        foreach ($this->translations as $translation) {
            if ($translation->getLangKey() == $this->default_language) {
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
    public function modifyTranslationInfoForTitle(ilPropertyFormGUI $form, ilTextInputGUI $title, string $active_language)
    {
        if (count($this->translations) <= 1) {
            return;
        }
        $default = $this->getDefaultTranslation();
        if ($default->getLangKey() != $active_language) {
            $title->setInfo($default->getLangKey() . ': ' . $default->getTitle());
        }
        if ($this->getTranslation($active_language) instanceof ilAdvancedMDRecordTranslation) {
            $title->setValue($this->getTranslation($active_language)->getTitle());
        }
    }

    /**
     * @param ilPropertyFormGUI $form
     * @param ilTextInputGUI    $title
     */
    public function modifyTranslationInfoForDescription(ilPropertyFormGUI $form, ilTextAreaInputGUI $description, string $active_language)
    {
        if (count($this->translations) <= 1) {
            return;
        }
        $default = $this->getDefaultTranslation();
        if ($default->getLangKey() != $active_language) {
            $description->setInfo($default->getLangKey() . ': ' . $default->getDescription());
        }
        if ($this->getTranslation($active_language) instanceof ilAdvancedMDRecordTranslation) {
            $description->setValue($this->getTranslation($active_language)->getDescription());
        }
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
    public function getTitleForLanguage(string $language)
    {
        if ($this->getTranslation($language) && strlen($this->getTranslation($language)->getTitle())) {
            return $this->getTranslation($language)->getTitle();
        }
        return $this->record->getTitle();
    }
    /**
     * @param string $language
     * @return string
     */
    public function getDescriptionForLanguage(string $language)
    {
        if ($this->getTranslation($language) && strlen($this->getTranslation($language)->getDescription())) {
            return $this->getTranslation($language)->getDescription();
        }
        return $this->record->getDescription();
    }

    /**
     * @param ilXmlWriter $writer
     * @return ilXmlWriter
     */
    public function toXML(ilXmlWriter $writer) : ilXmlWriter
    {
        if (!count($this->getTranslations())) {
            return $writer;
        }

        $writer->xmlStartTag(
            'RecordTranslations',
            [
                'defaultLanguage' => $this->getDefaultLanguage()
            ]
        );
        foreach ($this->getTranslations() as $translation) {
            $writer->xmlStartTag(
                'RecordTranslation',
                [
                    'language' => $translation->getLangKey()
                ]
            );
            $writer->xmlElement('RecordTranslationTitle', [], $translation->getTitle());
            $writer->xmlElement('RecordTranslationDescription', [], $translation->getDescription());
            $writer->xmlEndTag('RecordTranslation');
        }
        $writer->xmlEndTag('RecordTranslations');
        return $writer;
    }
}