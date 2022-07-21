<?php declare(strict_types=1);

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

/**
 * AMD field type integer
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionInteger extends ilAdvancedMDFieldDefinition
{
    protected ?int $min = null;
    protected ?int $max = null;
    protected ?string $suffix = null;

    protected $suffix_translations = [];

    //
    // generic types
    //

    public function getType() : int
    {
        return self::TYPE_INTEGER;
    }

    public function isFilterSupported() : bool
    {
        return false;
    }

    /**
     * @return array<string, string>
     */
    public function getSuffixTranslations() : array
    {
        return $this->suffix_translations;
    }

    public function setSuffixTranslation(string $language, string $suffix) : void
    {
        $this->suffix_translations[$language] = $suffix;
    }

    public function setSuffixTranslations(array $suffix_translations) : void
    {
        $this->suffix_translations = $suffix_translations;
    }

    protected function initADTDefinition() : ilADTDefinition
    {
        $def = ilADTFactory::getInstance()->getDefinitionInstanceByType('Integer');
        $def->setMin((int) $this->getMin());
        $def->setMax((int) $this->getMax());
        $def->setSuffix((string) ($this->getSuffixTranslations()[$this->language] ?? $this->getSuffix()));
        return $def;
    }

    public function setMin(?int $a_value) : void
    {
        if ($a_value !== null) {
            $a_value = $a_value;
        }
        $this->min = $a_value;
    }

    public function getMin() : ?int
    {
        return $this->min;
    }

    public function setMax(?int $a_value) : void
    {
        if ($a_value !== null) {
            $a_value = $a_value;
        }
        $this->max = $a_value;
    }

    public function getMax() : ?int
    {
        return $this->max;
    }

    public function setSuffix(?string $a_value) : void
    {
        if ($a_value !== null) {
            $a_value = trim($a_value);
        }
        $this->suffix = $a_value;
    }

    public function getSuffix() : ?string
    {
        return $this->suffix;
    }

    protected function importFieldDefinition(array $a_def) : void
    {
        $this->setMin($a_def["min"]);
        $this->setMax($a_def["max"]);
        $this->setSuffix($a_def["suffix"]);
        $this->setSuffixTranslations($a_def['suffix_translations'] ?? []);
    }

    protected function getFieldDefinition() : array
    {
        return array(
            "min" => $this->getMin(),
            "max" => $this->getMax(),
            "suffix" => $this->getSuffix(),
            'suffix_translations' => $this->getSuffixTranslations()
        );
    }

    public function getFieldDefinitionForTableGUI(string $content_language) : array
    {
        $res = [];

        if ($this->getMin() !== null) {
            $res[$this->lng->txt("md_adv_number_min")] = $this->getMin();
        }
        if ($this->getMax() !== null) {
            $res[$this->lng->txt("md_adv_number_max")] = $this->getMax();
        }
        if ($this->getSuffix()) {
            if ($this->useDefaultLanguageMode($content_language)) {
                $suffix = $this->getSuffix();
            } else {
                $suffix = $this->getSuffixTranslations()[$content_language] ?? '';
            }
            $res[$this->lng->txt("md_adv_number_suffix")] = $suffix;
        }
        return $res;
    }

    protected function addCustomFieldToDefinitionForm(
        ilPropertyFormGUI $a_form,
        bool $a_disabled = false,
        string $language = ''
    ) : void {
        $min = new ilNumberInputGUI($this->lng->txt("md_adv_number_min"), "min");
        $min->setValue((string) $this->getMin());
        $min->setSize(10);
        $a_form->addItem($min);

        $max = new ilNumberInputGUI($this->lng->txt("md_adv_number_max"), "max");
        $max->setValue((string) $this->getMax());
        $max->setSize(10);
        $a_form->addItem($max);

        $suffix = new ilTextInputGUI($this->lng->txt("md_adv_number_suffix"), "suffix");
        if ($this->useDefaultLanguageMode($language)) {
            $suffix->setValue($this->getSuffix());
        } else {
            $default_language = ilAdvancedMDRecord::_getInstanceByRecordId(
                $this->record_id
            )->getDefaultLanguage();
            $suffix->setInfo($default_language . ': ' . $this->getSuffix());
            $suffix->setValue($this->getSuffixTranslations()[$language] ?? '');
        }
        $suffix->setSize(10);
        $a_form->addItem($suffix);

        if ($a_disabled) {
            $min->setDisabled(true);
            $max->setDisabled(true);
            $suffix->setDisabled(true);
        }
    }

    public function importCustomDefinitionFormPostValues(ilPropertyFormGUI $a_form, string $language = '') : void
    {
        $min = $a_form->getInput("min");
        $this->setMin(($min !== "") ? (int) $min : null);

        $max = $a_form->getInput("max");
        $this->setMax(($max !== "") ? (int) $max : null);

        if ($this->useDefaultLanguageMode($language)) {
            $suffix = $a_form->getInput("suffix");
            $this->setSuffix(($suffix !== "") ? $suffix : null);
        } else {
            $suffix = $a_form->getInput('suffix');
            $this->setSuffixTranslation($language, $suffix);
        }
    }

    protected function addPropertiesToXML(ilXmlWriter $a_writer) : void
    {
        $a_writer->xmlElement('FieldValue', array("id" => "min"), $this->getMin());
        $a_writer->xmlElement('FieldValue', array("id" => "max"), $this->getMax());
        $a_writer->xmlElement('FieldValue', array("id" => "suffix"), $this->getSuffix());

        foreach ($this->getSuffixTranslations() as $lang_key => $suffix) {
            $a_writer->xmlElement('FieldValue', ['id' => 'suffix_' . $lang_key], $suffix);
        }
    }

    public function importXMLProperty(string $a_key, string $a_value) : void
    {
        if ($a_key == "min") {
            $this->setMin($a_value != "" ? (int) $a_value : null);
            return;
        }
        if ($a_key == "max") {
            $this->setMax($a_value != "" ? (int) $a_value : null);
            return;
        }
        if ($a_key == "suffix") {
            $this->setSuffix($a_value != "" ? $a_value : null);
            return;
        }

        $parts = explode('_', $a_key);
        if (isset($parts[0]) && $parts[0] == 'suffix') {
            $this->setSuffixTranslation($parts[1], $a_value);
        }
    }

    public function getValueForXML(ilADT $element) : string
    {
        return $element->getNumber();
    }

    public function importValueFromXML(string $a_cdata) : void
    {
        $this->getADT()->setNumber($a_cdata);
    }
}
