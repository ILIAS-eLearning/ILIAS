<?php

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

declare(strict_types=1);

/**
 * AMD field type select
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionSelectMulti extends ilAdvancedMDFieldDefinitionSelect
{
    protected const XML_SEPARATOR = "~|~";

    public function getSearchQueryParserValue(ilADTSearchBridge $a_adt_search): string
    {
        return (string) $a_adt_search->getADT()->getSelections()[0];
    }

    public function getType(): int
    {
        return self::TYPE_SELECT_MULTI;
    }

    protected function initADTDefinition(): ilADTDefinition
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

    public function importCustomDefinitionFormPostValues(ilPropertyFormGUI $a_form, string $language = ''): void
    {
        $this->importNewSelectOptions(false, $a_form, $language);
    }

    public function getValueForXML(ilADT $element): string
    {
        return self::XML_SEPARATOR .
            implode(self::XML_SEPARATOR, (array) $element->getSelections()) .
            self::XML_SEPARATOR;
    }

    public function importValueFromXML(string $a_cdata): void
    {
        $values = [];
        foreach (explode(self::XML_SEPARATOR, $a_cdata) as $value) {
            $value = $this->translateLegacyImportValueFromXML($value);
            $values[] = $value;
        }
        $this->getADT()->setSelections($values);
    }

    public function prepareElementForEditor(ilADTFormBridge $a_bridge): void
    {
        assert($a_bridge instanceof ilADTMultiEnumFormBridge);

        $a_bridge->setAutoSort(false);
    }
}
