<?php

declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilADTLocalizedTextDBBridge
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilADTLocalizedTextDBBridge extends ilADTDBBridge
{
    public function getTable(): ?string
    {
        return 'adv_md_values_ltext';
    }

    /**
     * @inheritDoc
     */
    protected function isValidADT(ilADT $adt): bool
    {
        return $adt instanceof ilADTLocalizedText;
    }

    /**
     * @inheritDoc
     */
    public function readRecord(array $a_row): void
    {
        $active_languages = $this->getADT()->getCopyOfDefinition()->getActiveLanguages();
        $default_language = $this->getADT()->getCopyOfDefinition()->getDefaultLanguage();
        $language = $a_row[$this->getElementId() . '_language'];

        if (strcmp($language, $default_language) === 0) {
            $this->getADT()->setText($a_row[$this->getElementId() . '_translation']);
        } elseif (!strlen($default_language)) {
            $this->getADT()->setText($a_row[$this->getElementId() . '_translation']);
        }
        if (in_array($language, $active_languages)) {
            $this->getADT()->setTranslation(
                $language,
                $a_row[$this->getElementId() . '_translation']
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function prepareInsert(array &$a_fields): void
    {
        $a_fields[$this->getElementId()] = [ilDBConstants::T_TEXT, $this->getADT()->getText()];
    }

    /**
     *
     */
    public function afterInsert(): void
    {
        $this->afterUpdate();
    }

    public function getAdditionalPrimaryFields(): array
    {
        return [
            'value_index' => [ilDBConstants::T_TEXT, '']
        ];
    }

    /**
     *
     */
    public function afterUpdate(): void
    {
        if (!$this->getADT()->getCopyOfDefinition()->supportsTranslations()) {
            return;
        }
        $this->deleteTranslations();
        $this->insertTranslations();
    }

    /**
     * delete translations
     */
    protected function deleteTranslations(): void
    {
        $this->db->manipulate(
            $q =
            'delete from ' . $this->getTable() . ' ' .
            'where ' . $this->buildPrimaryWhere() . ' ' .
            'and value_index != ' . $this->db->quote('', ilDBConstants::T_TEXT)
        );
    }

    /**
     * Save all translations
     */
    protected function insertTranslations(): void
    {
        foreach ($this->getADT()->getTranslations() as $language => $value) {
            $fields = $this->getPrimary();
            $fields['value_index'] = [ilDBConstants::T_TEXT, $language];
            $fields['value'] = [ilDBConstants::T_TEXT, $value];
            $this->db->insert($this->getTable(), $fields);
        }
    }
}
