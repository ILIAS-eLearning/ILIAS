<?php declare(strict_types=1);

/**
 * Class ilADTEnumSearchBridgeMulti
 */
class ilADTEnumSearchBridgeMulti extends ilADTSearchBridgeMulti
{
    public const ENUM_SEARCH_COLUMN = 'value_index';
    public const SEARCH_MODE_ALL = 1;
    public const SEARCH_MODE_ANY = 2;

    protected bool $multi_source;
    protected int $search_mode = self::SEARCH_MODE_ALL;

    public function setSearchMode(int $a_mode) : void
    {
        $this->search_mode = $a_mode;
    }

    public function getSearchColumn() : string
    {
        return self::ENUM_SEARCH_COLUMN;
    }

    protected function isValidADTDefinition(ilADTDefinition $a_adt_def) : bool
    {
        return ($a_adt_def instanceof ilADTEnumDefinition ||
            $a_adt_def instanceof ilADTMultiEnumDefinition);
    }

    protected function convertADTDefinitionToMulti(ilADTDefinition $a_adt_def) : ilADTDefinition
    {
        if ($a_adt_def->getType() == "Enum") {
            $this->multi_source = false;
            $def = ilADTFactory::getInstance()->getDefinitionInstanceByType("MultiEnum");
            $def->setNumeric($a_adt_def->isNumeric());
            $def->setOptions($a_adt_def->getOptions());
            return $def;
        } else {
            $this->multi_source = true;
            return $a_adt_def;
        }
    }

    public function loadFilter() : void
    {
        $value = $this->readFilter();
        if ($value !== null) {
            $this->getADT()->setSelections($value);
        }
    }

    // form

    public function addToForm() : void
    {
        $def = $this->getADT()->getCopyOfDefinition();

        $options = $def->getOptions();
        asort($options); // ?

        $cbox = new ilCheckboxGroupInputGUI($this->getTitle(), $this->getElementId());
        $cbox->setValue($this->getADT()->getSelections());

        foreach ($options as $value => $caption) {
            $option = new ilCheckboxOption($caption, $value);
            $cbox->addOption($option);
        }

        $this->addToParentElement($cbox);
    }

    public function importFromPost(array $a_post = null) : bool
    {
        $post = $this->extractPostValues($a_post);

        if ($post && $this->shouldBeImportedFromPost($post)) {
            if ($this->getForm() instanceof ilPropertyFormGUI) {
                $item = $this->getForm()->getItemByPostVar($this->getElementId());
                $item->setValue($post);
            } elseif (array_key_exists($this->getElementId(), $this->table_filter_fields)) {
                $this->table_filter_fields[$this->getElementId()]->setValue($post);
                $this->writeFilter($post);
            }

            if (is_array($post)) {
                $this->getADT()->setSelections($post);
            }
        } else {
            $this->getADT()->setSelections();
        }
        return true;
    }

    // db

    public function getSQLCondition(string $a_element_id, int $mode = self::SQL_LIKE, array $quotedWords = []) : string
    {
        if (!$this->isNull() && $this->isValid()) {
            return $this->db->in(
                $this->getSearchColumn(),
                $this->getADT()->getSelections(),
                false,
                ilDBConstants::T_INTEGER
            );
        }
        return '';
    }

    public function isInCondition(ilADT $a_adt) : bool
    {
        assert($a_adt instanceof ilADTMultiEnum);

        $current = $this->getADT()->getSelections();
        if (is_array($current) &&
            count($current)) {
            // #16827 / #17087
            if ($this->search_mode == self::SEARCH_MODE_ANY) {
                foreach ((array) $a_adt->getSelections() as $value) {
                    if (in_array($value, $current)) {
                        return true;
                    }
                }
            } else {
                // #18028
                return !(bool) count(array_diff($current, (array) $a_adt->getSelections()));
            }
        }
        return false;
    }

    //  import/export

    public function getSerializedValue() : string
    {
        if (!$this->isNull() && $this->isValid()) {
            return serialize($this->getADT()->getSelections());
        }
        return '';
    }

    public function setSerializedValue(string $a_value) : void
    {
        $a_value = unserialize($a_value);
        if (is_array($a_value)) {
            $this->getADT()->setSelections($a_value);
        }
    }
}
