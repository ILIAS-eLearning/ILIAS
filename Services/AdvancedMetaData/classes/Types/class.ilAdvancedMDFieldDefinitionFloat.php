<?php

declare(strict_types=1);
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * AMD field type float (based on integer)
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionFloat extends ilAdvancedMDFieldDefinitionInteger
{
    protected int $decimals;

    public function getType(): int
    {
        return self::TYPE_FLOAT;
    }

    protected function init(): void
    {
        parent::init();
        $this->setDecimals(2);
    }

    public function isFilterSupported(): bool
    {
        return false;
    }

    protected function initADTDefinition(): ilADTDefinition
    {
        $def = ilADTFactory::getInstance()->getDefinitionInstanceByType("Float");

        $def->setMin($this->getMin());
        $def->setMax($this->getMax());
        $def->setDecimals($this->getDecimals());
        $def->setSuffix($this->getSuffixTranslations()[$this->language] ?? $this->getSuffix());
        return $def;
    }

    /**
     * Set decimals
     * @param int $a_value
     */
    public function setDecimals($a_value)
    {
        $this->decimals = max(1, abs((int) $a_value));
    }

    /**
     * Get decimals
     * @return int
     */
    public function getDecimals()
    {
        return $this->decimals;
    }


    //
    // definition (NOT ADT-based)
    //

    protected function importFieldDefinition(array $a_def): void
    {
        parent::importFieldDefinition($a_def);
        $this->setDecimals($a_def["decimals"]);
    }

    protected function getFieldDefinition(): array
    {
        $def = parent::getFieldDefinition();
        $def["decimals"] = $this->getDecimals();
        return $def;
    }

    public function getFieldDefinitionForTableGUI(string $content_language): array
    {
        global $DIC;

        $lng = $DIC['lng'];

        $res = parent::getFieldDefinitionForTableGUI($content_language);
        $res[$lng->txt("md_adv_number_decimals")] = $this->getDecimals();
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

        // #32
        parent::addCustomFieldToDefinitionForm($a_form, $a_disabled, $language);

        $decimals = new ilNumberInputGUI($lng->txt("md_adv_number_decimals"), "dec");
        $decimals->setRequired(true);
        $decimals->setValue((string) $this->getDecimals());
        $decimals->setSize(5);
        $a_form->addItem($decimals);

        if ($a_disabled) {
            $decimals->setDisabled(true);
        }
    }

    /**
     * Import custom  post values from definition form
     */
    public function importCustomDefinitionFormPostValues(ilPropertyFormGUI $a_form, string $language = ''): void
    {
        parent::importCustomDefinitionFormPostValues($a_form, $language);
        $this->setDecimals((int) $a_form->getInput("dec"));
    }


    //
    // export/import
    //

    protected function addPropertiesToXML(ilXmlWriter $a_writer): void
    {
        parent::addPropertiesToXML($a_writer);
        $a_writer->xmlElement('FieldValue', array("id" => "decimals"), $this->getDecimals());
    }

    public function importXMLProperty(string $a_key, string $a_value): void
    {
        if ($a_key == "decimals") {
            $this->setDecimals($a_value != "" ? $a_value : null);
        }

        parent::importXMLProperty($a_key, $a_value);
    }
}
