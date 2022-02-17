<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDclMultiTextInputGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilDclMultiTextInputGUI extends ilMultipleTextsInputGUI
{
    public function setValues($values)
    {
        $this->setIdentifiedMultiValues($values);
    }


    public function getValues()
    {
        $this->getIdentifiedMultiValues();
    }


    public function setValue($a_value) : void
    {
        $this->setIdentifiedMultiValues($a_value);
    }


    public function getValue()
    {
        $this->getIdentifiedMultiValues();
    }


    public function setMultiValues(array $a_values) : void
    {
        $this->setIdentifiedMultiValues($a_values);
    }


    public function getMultiValues() : array
    {
        $this->getIdentifiedMultiValues();
    }

    //	protected function getMultiValuePostVar($identifier)
    //	{
    //		return $this->getPostVar();
    //	}
    public function render(string $a_mode = "") : string
    {
        $tpl = new ilTemplate("tpl.prop_multi_text_inp.html", true, true, "Services/Form");
        $i = 0;
        foreach ($this->getIdentifiedMultiValues() as $identifier => $value) {
            if (is_array($value)) {
                $value = array_shift($value);
            }
            if (strlen($value)) {
                $tpl->setCurrentBlock("prop_text_propval");
                $tpl->setVariable("PROPERTY_VALUE", ilLegacyFormElementsUtil::prepareFormOutput($value));
                $tpl->parseCurrentBlock();
            }
            if ($this->isEditElementOrderEnabled()) {
                $tpl->setCurrentBlock("move");
                $tpl->setVariable("ID_UP", $this->getMultiValuePosIndexedSubFieldId($identifier, 'up', $i));
                $tpl->setVariable("ID_DOWN", $this->getMultiValuePosIndexedSubFieldId($identifier, 'down', $i));
                $tpl->setVariable("CMD_UP", $this->buildMultiValueSubmitVar($identifier, $i, 'up'));
                $tpl->setVariable("CMD_DOWN", $this->buildMultiValueSubmitVar($identifier, $i, 'down'));
                $tpl->setVariable("ID", $this->getMultiValuePosIndexedFieldId($identifier, $i));
                $tpl->setVariable("UP_BUTTON", ilGlyphGUI::get(ilGlyphGUI::UP));
                $tpl->setVariable("DOWN_BUTTON", ilGlyphGUI::get(ilGlyphGUI::DOWN));
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock("row");
            $tpl->setVariable("POST_VAR", $this->getMultiValuePostVarPosIndexed($identifier, $i));
            $tpl->setVariable("ID", $this->getMultiValuePosIndexedFieldId($identifier, $i));
            $tpl->setVariable("SIZE", $this->getSize());
            $tpl->setVariable("MAXLENGTH", $this->getMaxLength());

            if ($this->getDisabled()) {
                $tpl->setVariable(
                    "DISABLED",
                    " disabled=\"disabled\""
                );
            } elseif ($this->isEditElementOccuranceEnabled()) {
                $tpl->setVariable("ID_ADD", $this->getMultiValuePosIndexedSubFieldId($identifier, 'add', $i));
                $tpl->setVariable("ID_REMOVE", $this->getMultiValuePosIndexedSubFieldId($identifier, 'remove', $i));
                $tpl->setVariable("CMD_ADD", $this->buildMultiValueSubmitVar($identifier, $i, 'add'));
                $tpl->setVariable("CMD_REMOVE", $this->buildMultiValueSubmitVar($identifier, $i, 'remove'));
                $tpl->setVariable("ADD_BUTTON", ilGlyphGUI::get(ilGlyphGUI::ADD));
                $tpl->setVariable("REMOVE_BUTTON", ilGlyphGUI::get(ilGlyphGUI::REMOVE));
            }

            $tpl->parseCurrentBlock();
            $i++;
        }
        $tpl->setVariable("ELEMENT_ID", $this->getFieldId());

        if (!$this->getDisabled()) {
            $globalTpl = $GLOBALS['DIC'] ? $GLOBALS['DIC']['tpl'] : $GLOBALS['tpl'];
            $globalTpl->addJavascript("./Services/Form/js/ServiceFormWizardInput.js");
            $globalTpl->addJavascript("./Services/Form/js/ServiceFormIdentifiedWizardInputExtend.js");

            $globalTpl->addJavascript("./Services/Form/js/ServiceFormMultiTextInputInit.js");
        }

        return $tpl->get();
    }
}
