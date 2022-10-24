<?php

declare(strict_types=1);
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * AMD field type text
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionText extends ilAdvancedMDFieldDefinitionGroupBased
{
    public const XML_SEPARATOR_TRANSLATIONS = "~|~";
    public const XML_SEPARATOR_TRANSLATION = '~+~';

    protected int $max_length = 0;
    protected $multi = false;

    //
    // generic types
    //

    public function getType(): int
    {
        return self::TYPE_TEXT;
    }

    public function getADTGroup(): ilADTDefinition
    {
        return $this->getADTDefinition();
    }

    public function getTitles(): array
    {
        return [];
    }

    public function hasComplexOptions(): bool
    {
        return false;
    }

    /**
     * @return ilADTDefinition
     * @throws Exception
     */
    protected function initADTDefinition(): ilADTDefinition
    {
        $field_translations = ilAdvancedMDFieldTranslations::getInstanceByRecordId($this->getRecordId());

        $definition = ilADTFactory::getInstance()->getDefinitionInstanceByType(ilADTFactory::TYPE_LOCALIZED_TEXT);
        $definition->setMaxLength($this->getMaxLength() ?? 0);
        $definition->setActiveLanguages($field_translations->getActivatedLanguages($this->getFieldId(), true));
        $definition->setDefaultLanguage($field_translations->getDefaultLanguage());
        return $definition;
    }


    //
    // properties
    //

    /**
     * Set max length
     * @param int $a_value
     */
    public function setMaxLength($a_value)
    {
        if ($a_value !== null) {
            $a_value = (int) $a_value;
        }
        $this->max_length = (int) $a_value;
    }

    /**
     * Get max length
     * @return int
     */
    public function getMaxLength()
    {
        return $this->max_length;
    }

    /**
     * Set multi-line
     * @param string $a_value
     */
    public function setMulti($a_value)
    {
        $this->multi = (bool) $a_value;
    }

    /**
     * Is multi-line?
     * @return bool
     */
    public function isMulti()
    {
        return $this->multi;
    }


    //
    // definition (NOT ADT-based)
    //

    protected function importFieldDefinition(array $a_def): void
    {
        $this->setMaxLength($a_def["max"]);
        $this->setMulti($a_def["multi"]);
    }

    protected function getFieldDefinition(): array
    {
        return array(
            "max" => $this->getMaxLength(),
            "multi" => $this->isMulti()
        );
    }

    public function getFieldDefinitionForTableGUI(string $content_language): array
    {
        global $DIC;

        $lng = $DIC['lng'];

        $res = array();

        if ($this->getMaxLength() !== null) {
            $res[$lng->txt("md_adv_text_max_length")] = $this->getMaxLength();
        }
        if ($this->isMulti()) {
            $res[$lng->txt("md_adv_text_multi")] = $lng->txt("yes");
        }

        return $res;
    }

    /**
     * Add input elements to definition form
     * @param ilPropertyFormGUI $a_form
     * @param bool              $a_disabled
     * @param string            $language
     */
    protected function addCustomFieldToDefinitionForm(
        ilPropertyFormGUI $a_form,
        bool $a_disabled = false,
        string $language = ''
    ): void {
        global $DIC;

        $lng = $DIC['lng'];

        $max = new ilNumberInputGUI($lng->txt("md_adv_text_max_length"), "max");
        $max->setValue((string) $this->getMaxLength());
        $max->setSize(10);
        $max->setSuffix($lng->txt("characters"));
        $max->setMinValue(1);
        $max->setMaxValue(4000); // DB limit
        $a_form->addItem($max);

        $multi = new ilCheckboxInputGUI($lng->txt("md_adv_text_multi"), "multi");
        $multi->setValue("1");
        $multi->setChecked($this->isMulti());
        $a_form->addItem($multi);

        if ($a_disabled) {
            $max->setDisabled(true);
            $multi->setDisabled(true);
        }
    }

    /**
     * Import custom post values from definition form
     * @param ilPropertyFormGUI $a_form
     * @param string            $language
     */
    public function importCustomDefinitionFormPostValues(ilPropertyFormGUI $a_form, string $language = ''): void
    {
        $max = $a_form->getInput("max");
        $this->setMaxLength(($max !== "") ? $max : null);

        $this->setMulti($a_form->getInput("multi"));
    }

    //
    // import/export
    //

    protected function addPropertiesToXML(ilXmlWriter $a_writer): void
    {
        $a_writer->xmlElement('FieldValue', array("id" => "max"), $this->getMaxLength());
        $a_writer->xmlElement('FieldValue', array("id" => "multi"), $this->isMulti());
    }

    public function importXMLProperty(string $a_key, string $a_value): void
    {
        if ($a_key == "max") {
            $this->setMaxLength($a_value != "" ? $a_value : null);
        }
        if ($a_key == "multi") {
            $this->setMulti($a_value != "" ? $a_value : null);
        }
    }

    public function getValueForXML(ilADT $element): string
    {
        /**
         * @var $translations ilADTLocalizedText
         */
        $translations = $element->getTranslations();
        $serialized_values = [];
        foreach ($translations as $lang_key => $translation) {
            $serialized_values[] = $lang_key . self::XML_SEPARATOR_TRANSLATION . $translation;
        }
        return implode(self::XML_SEPARATOR_TRANSLATIONS, $serialized_values);
    }

    /**
     * @param string $a_cdata
     */
    public function importValueFromXML(string $a_cdata): void
    {
        // an import from release < 7
        if (strpos($a_cdata, self::XML_SEPARATOR_TRANSLATION) === false) {
            $this->getADT()->setText($a_cdata);
            return;
        }

        $translations = explode(self::XML_SEPARATOR_TRANSLATIONS, $a_cdata);
        foreach ($translations as $translation) {
            $parts = explode(self::XML_SEPARATOR_TRANSLATION, $translation);
            if ($parts === false) {
                continue;
            }
            $this->getADT()->setTranslation($parts[0], $parts[1]);
        }
    }

    public function importFromECS(string $a_ecs_type, $a_value, string $a_sub_id): bool
    {
        $value = '';
        switch ($a_ecs_type) {
            case ilECSUtils::TYPE_ARRAY:
                $value = implode(',', (array) $a_value);
                break;

            case ilECSUtils::TYPE_INT:
                $value = (int) $a_value;
                break;

            case ilECSUtils::TYPE_STRING:
                $value = (string) $a_value;
                break;

            case ilECSUtils::TYPE_TIMEPLACE:
                if ($a_value instanceof ilECSTimePlace) {
                    $value = $a_value->{'get' . ucfirst($a_sub_id)}();
                }
                break;
        }

        if (trim($value)) {
            $this->getADT()->setText($value);
            return true;
        }
        return false;
    }

    public function prepareElementForEditor(ilADTFormBridge $a_bridge): void
    {
        if (!$a_bridge instanceof ilADTLocalizedTextFormBridge) {
            $this->logger->warning('Passed ' . get_class($a_bridge));
            return;
        }
        $a_bridge->setMulti($this->isMulti());
    }

    public function getSearchQueryParserValue(ilADTSearchBridge $a_adt_search): string
    {
        return $a_adt_search->getADT()->getText();
    }

    protected function parseSearchObjects(array $a_records, array $a_object_types): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $res = array();

        $obj_ids = array();
        foreach ($a_records as $record) {
            if ($record["sub_type"] == "-") {
                // keep found information
                $obj_ids[$record["obj_id"]] = $record;
            }
        }

        $sql = "SELECT obj_id,type" .
            " FROM object_data" .
            " WHERE " . $ilDB->in("obj_id", array_keys($obj_ids), "", "integer") .
            " AND " . $ilDB->in("type", $a_object_types, "", "text");
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $row["found"] = array();
            foreach ($obj_ids[(int) $row["obj_id"]] as $field => $value) {
                if (substr($field, 0, 5) == "found") {
                    $row["found"][$field] = $value;
                }
            }
            $res[] = $row;
        }

        return $res;
    }

    public function searchObjects(
        ilADTSearchBridge $a_adt_search,
        ilQueryParser $a_parser,
        array $a_object_types,
        string $a_locate,
        string $a_search_type
    ): array {
        // :TODO: search type (like, fulltext)

        $condition = $a_adt_search->getSQLCondition(
            ilADTActiveRecordByType::SINGLE_COLUMN_NAME,
            ilADTTextSearchBridgeSingle::SQL_LIKE,
            $a_parser->getQuotedWords()
        );
        if ($condition) {
            $objects = ilADTActiveRecordByType::find(
                'adv_md_values',
                $this->getADT()->getType(),
                $this->getFieldId(),
                $condition,
                $a_locate
            );
            if (isset($objects) && count($objects)) {
                return $this->parseSearchObjects($objects, $a_object_types);
            }
            return [];
        }
        return [];
    }
}
