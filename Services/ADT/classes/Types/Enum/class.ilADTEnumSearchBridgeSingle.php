<?php

declare(strict_types=1);

class ilADTEnumSearchBridgeSingle extends ilADTSearchBridgeSingle
{
    public const ENUM_SEARCH_COLUMN = 'value_index';

    protected function isValidADTDefinition(ilADTDefinition $a_adt_def): bool
    {
        return ($a_adt_def instanceof ilADTEnumDefinition);
    }

    // table2gui / filter

    public function loadFilter(): void
    {
        $value = $this->readFilter();
        if ($value !== null) {
            $this->getADT()->setSelection($value);
        }
    }

    public function getSearchColumn(): string
    {
        return self::ENUM_SEARCH_COLUMN;
    }

    // form

    public function addToForm(): void
    {
        $def = $this->getADT()->getCopyOfDefinition();

        $options = $def->getOptions();
        asort($options); // ?

        $this->lng->loadLanguageModule("search");
        $options = array("" => $this->lng->txt("search_any")) + $options;

        $select = new ilSelectInputGUI($this->getTitle(), $this->getElementId());
        $select->setOptions($options);

        $select->setValue($this->getADT()->getSelection());

        $this->addToParentElement($select);
    }

    public function importFromPost(array $a_post = null): bool
    {
        $post = $this->extractPostValues($a_post);
        if (
            is_numeric($post) &&
            $this->shouldBeImportedFromPost($post)
        ) {
            if ($this->getForm() instanceof ilPropertyFormGUI) {
                $item = $this->getForm()->getItemByPostVar($this->getElementId());
                $item->setValue($post);
            } elseif (array_key_exists($this->getElementId(), $this->table_filter_fields)) {
                $this->table_filter_fields[$this->getElementId()]->setValue($post);
                $this->writeFilter($post);
            }

            $this->getADT()->setSelection($post);
        } else {
            $this->writeFilter();
            $this->getADT()->setSelection();
        }
        return true;
    }

    // db

    public function getSQLCondition(string $a_element_id, int $mode = self::SQL_LIKE, array $quotedWords = []): string
    {
        $search_column = $this->getSearchColumn();
        if (!$this->isNull() && $this->isValid()) {
            return $search_column . ' = ' . $this->db->quote($this->getADT()->getSelection(), ilDBConstants::T_TEXT);
        }
        return '';
    }

    public function isInCondition(ilADT $a_adt): bool
    {
        assert($a_adt instanceof ilADTEnum);

        return $this->getADT()->equals($a_adt);
    }

    //  import/export

    public function getSerializedValue(): string
    {
        if (!$this->isNull() && $this->isValid()) {
            return serialize(array($this->getADT()->getSelection()));
        }
        return '';
    }

    public function setSerializedValue(string $a_value): void
    {
        $a_value = unserialize($a_value);
        if (is_array($a_value)) {
            $this->getADT()->setSelection($a_value[0]);
        }
    }
}
