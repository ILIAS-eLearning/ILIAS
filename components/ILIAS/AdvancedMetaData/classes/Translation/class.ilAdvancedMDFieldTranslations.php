<?php

declare(strict_types=1);
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAdvancedMDFieldTranslations
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldTranslations
{
    /**
     * @var array<int, self>
     */
    private static array $instances = [];

    private int $record_id;
    private ilAdvancedMDRecord $record;

    /**
     * @var array<int, ilAdvancedMDFieldDefinition>
     */
    private array $definitions;

    /**
     * @var array
     */
    private array $translations = [];

    private ilAdvancedMDRecordTranslations $record_translations;
    private string $default_language = '';

    /**
     * @var ilDBInterface
     */
    private ilDBInterface $db;

    /**
     * @var ilLanguage
     */
    private ilLanguage $lng;

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

    public static function getInstanceByRecordId(int $record_id): ilAdvancedMDFieldTranslations
    {
        if (!isset(self::$instances[$record_id])) {
            self::$instances[$record_id] = new self($record_id);
        }
        return self::$instances[$record_id];
    }

    public function getRecordId(): int
    {
        return $this->record_id;
    }

    public function getDefaultLanguage(): string
    {
        return $this->default_language;
    }

    /**
     * @param int  $field_id
     * @param bool $with_default
     * @return array<int, string>
     */
    public function getActivatedLanguages(int $field_id, bool $with_default = true): array
    {
        $activated = [];
        foreach ($this->getTranslations($field_id) as $language => $translation) {
            if ($language == self::getDefaultLanguage() && !$with_default) {
                continue;
            }
            $activated[] = $language;
        }
        return $activated;
    }

    public function isConfigured(int $field_id, string $lang_key): bool
    {
        if (!$this->record_translations->isConfigured($lang_key)) {
            return false;
        }
        return isset($this->translations[$field_id][$lang_key]);
    }

    public function getTranslation(int $field_id, string $lang_key): ?ilAdvancedMDFieldTranslation
    {
        if (!$this->isConfigured($field_id, $lang_key)) {
            return null;
        }
        return $this->translations[$field_id][$lang_key];
    }

    /**
     * @return array<string, ilAdvancedMDFieldTranslation>
     */
    public function getTranslations(int $field_id): array
    {
        if (isset($this->translations[$field_id])) {
            return $this->translations[$field_id];
        }
        return [];
    }

    /**
     * @return ilAdvancedMDRecordTranslation | null
     */
    public function getDefaultTranslation(int $field_id): ?ilAdvancedMDFieldTranslation
    {
        foreach ($this->getTranslations($field_id) as $translation) {
            if ($translation->getLangKey() == $this->default_language) {
                return $translation;
            }
        }
        return null;
    }

    public function read(): void
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
            if ((string) $row->lang_code == $this->default_language && $row->tfield == null) {
                $this->translations[(int) $row->ofield][(string) $row->lang_code]->setTitle($this->definitions[(int) $row->ofield]->getTitle());
                $this->translations[(int) $row->ofield][(string) $row->lang_code]->setDescription(
                    $this->definitions[(int) $row->ofield]->getDescription()
                );
            }
        }
    }

    public function getFormTranslationInfo(int $field_id, string $active_language): string
    {
        if (count($this->getTranslations($field_id)) <= 1) {
            return '';
        }

        $txt = '';
        $txt = $this->lng->txt('md_adv_int_current') . ' ' . $this->lng->txt('meta_l_' . $active_language);
        $txt .= ', ';
        foreach ($this->getTranslations($field_id) as $translation) {
            if ($translation->getLangKey() == $this->getDefaultLanguage()) {
                $txt .= ($this->lng->txt('md_adv_int_default') . ' ' . $this->lng->txt('meta_l_' . $translation->getLangKey()));
                break;
            }
        }
        return $txt;
    }

    public function modifyTranslationInfoForTitle(
        int $field_id,
        ilPropertyFormGUI $form,
        ilTextInputGUI $title,
        string $active_language
    ): void {
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

    public function modifyTranslationInfoForDescription(
        int $field_id,
        ilPropertyFormGUI $form,
        ilTextAreaInputGUI $description,
        string $active_language
    ): void {
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

    public function updateFromForm(int $field_id, string $active_language, ilPropertyFormGUI $form): void
    {
        $translation = $this->getTranslation($field_id, $active_language);
        if (!$translation instanceof ilAdvancedMDFieldTranslation) {
            return;
        }
        $translation->setTitle($form->getInput('title'));
        $translation->setDescription($form->getInput('description'));
        $translation->update();
    }

    public function getTitleForLanguage(int $field_id, string $language): string
    {
        if ($this->getTranslation($field_id, $language) && strlen($this->getTranslation(
            $field_id,
            $language
        )->getTitle())) {
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

    public function getDescriptionForLanguage(int $field_id, string $language): string
    {
        if ($this->getTranslation($field_id, $language) && strlen($this->getTranslation(
            $field_id,
            $language
        )->getDescription())) {
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
