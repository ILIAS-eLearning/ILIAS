<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/AdvancedMetaData/classes/Types/class.ilAdvancedMDFieldDefinitionSelect.php";

/**
 * AMD field type select
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionSelectMulti extends ilAdvancedMDFieldDefinitionSelect
{
    const XML_SEPARATOR = "~|~";
    
    //
    // generic types
    //

    // search
    public function getSearchQueryParserValue(ilADTSearchBridge $search_bridge)
    {
        return $search_bridge->getADT()->getSelections()[0] ?? 0;
    }

    
    public function getType()
    {
        return self::TYPE_SELECT_MULTI;
    }
    
    

    protected function initADTDefinition()
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

    
    
    //
    // definition (NOT ADT-based)
    //

    /**
     * @param ilPropertyFormGUI $a_form
     * @param string            $language
     */
    public function importCustomDefinitionFormPostValues(ilPropertyFormGUI $a_form, string $language = '')
    {
        $this->importNewSelectOptions(false, $a_form, $language);
    }

    
    //
    // definition CRUD
    //
    

    
    //
    // import/export
    //
    
    public function getValueForXML(ilADT $element)
    {
        return self::XML_SEPARATOR .
            implode(self::XML_SEPARATOR, $element->getSelections()) .
            self::XML_SEPARATOR;
    }
    
    public function importValueFromXML($a_cdata)
    {
        $this->getADT()->setSelections(explode(self::XML_SEPARATOR, $a_cdata));
    }
    
    
    //
    // presentation
    //
    
    public function prepareElementForEditor(ilADTFormBridge $a_enum)
    {
        assert($a_enum instanceof ilADTMultiEnumFormBridge);
        
        $a_enum->setAutoSort(false);
    }
}
