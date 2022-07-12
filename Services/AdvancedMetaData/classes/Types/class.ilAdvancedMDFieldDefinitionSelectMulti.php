<?php declare(strict_types=1);
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * AMD field type select
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionSelectMulti extends ilAdvancedMDFieldDefinitionSelect
{
    protected const XML_SEPARATOR = "~|~";
    
    public function getSearchQueryParserValue(ilADTSearchBridge $a_adt_search) : string
    {
        return $a_adt_search->getADT()->getSelections()[0] ?? "";
    }

    public function getType() : int
    {
        return self::TYPE_SELECT_MULTI;
    }

    protected function initADTDefinition() : ilADTDefinition
    {
        $def = ilADTFactory::getInstance()->getDefinitionInstanceByType("MultiEnum");
        $def->setNumeric(false);

        $options = $this->getOptions();
        $translated_options = [];
        if (isset($this->getOptionTranslations()[$this->language])) {
            $translated_options = $this->getOptionTranslations()[$this->language];
        }
        $def->setOptions(array_replace($options, $translated_options));
        return $def;
    }

    public function importCustomDefinitionFormPostValues(ilPropertyFormGUI $a_form, string $language = '') : void
    {
        if (!$this->useDefaultLanguageMode($language)) {
            $this->importTranslatedFormPostValues($a_form, $language);
            return;
        }
        if (!strlen($language)) {
            $language = ilAdvancedMDRecord::_getInstanceByRecordId($this->getRecordId())->getDefaultLanguage();
        }

        $old = $this->getOptionTranslation($language);
        $new = $a_form->getInput("opts");

        $missing = array_diff_assoc($old, $new);

        if (sizeof($missing)) {
            $this->confirmed_objects = $this->buildConfirmedObjects($a_form);
            if (!is_array($this->confirmed_objects)) {
                $search = ilADTFactory::getInstance()->getSearchBridgeForDefinitionInstance(
                    $this->getADTDefinition(),
                    false,
                    false
                );
                foreach ($missing as $missing_idx => $missing_value) {
                    $in_use = $this->findBySingleValue($search, $missing_idx);
                    if (is_array($in_use)) {
                        foreach ($in_use as $item) {
                            $this->confirm_objects[$missing_idx][] = $item;
                            $this->confirm_objects_values[$missing_idx] = $old[$missing_idx];
                        }
                    }
                }
            }
        }

        $this->old_options = $old;
        $this->setOptionTranslationsForLanguage($new, $language);
    }

    public function getValueForXML(ilADT $element) : string
    {
        return self::XML_SEPARATOR .
            implode(self::XML_SEPARATOR, $element->getSelections()) .
            self::XML_SEPARATOR;
    }

    public function importValueFromXML(string $a_cdata) : void
    {
        $this->getADT()->setSelections(explode(self::XML_SEPARATOR, $a_cdata));
    }
    
    public function prepareElementForEditor(ilADTFormBridge $a_bridge) : void
    {
        assert($a_bridge instanceof ilADTMultiEnumFormBridge);

        $a_bridge->setAutoSort(false);
    }
}
