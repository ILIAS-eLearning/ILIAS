<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 */
abstract class ilMultipleTextsInputGUI extends ilIdentifiedMultiValuesInputGUI
{
    /**
     * @var bool
     */
    protected $editElementOccuranceEnabled = false;
    
    /**
     * @var bool
     */
    protected $editElementOrderEnabled = false;
    
    /**
     * Constructor
     *
     * @param	string	$a_title	Title
     * @param	string	$a_postvar	Post Variable
     */
    public function __construct($a_title = "", $a_postvar = "")
    {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->validationRegexp = "";
    }
    
    /**
     * @return	boolean $editElementOccuranceEnabled
     */
    public function isEditElementOccuranceEnabled() : bool
    {
        return $this->editElementOccuranceEnabled;
    }
    
    /**
     * @param	boolean	$editElementOccuranceEnabled
     */
    public function setEditElementOccuranceEnabled($editElementOccuranceEnabled) : void
    {
        $this->editElementOccuranceEnabled = $editElementOccuranceEnabled;
    }
    
    /**
     * @return boolean
     */
    public function isEditElementOrderEnabled() : bool
    {
        return $this->editElementOrderEnabled;
    }
    
    /**
     * @param boolean $editElementOrderEnabled
     */
    public function setEditElementOrderEnabled($editElementOrderEnabled) : void
    {
        $this->editElementOrderEnabled = $editElementOrderEnabled;
    }
    
    /**
     * Check input, strip slashes etc. set alert, if input is not ok.
     *fetchImageTitle
     * @return	boolean		Input ok, true/false
     */
    public function onCheckInput() : bool
    {
        $lng = $this->lng;
        
        $submittedElements = $_POST[$this->getPostVar()];
        
        if (!is_array($submittedElements) && $this->getRequired()) {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }
        
        foreach ($submittedElements as $submittedValue) {
            $submittedContentText = $this->fetchContentTextFromValue($submittedValue);
            
            if ($this->getRequired() && trim($submittedContentText) == "") {
                $this->setAlert($lng->txt('msg_input_is_required'));
                return false;
            }
            
            if (strlen($this->getValidationRegexp())) {
                if (!preg_match($this->getValidationRegexp(), $submittedContentText)) {
                    $this->setAlert($lng->txt('msg_wrong_format'));
                    return false;
                }
            }
        }

        return $this->checkSubItemsInput();
    }
    
    /**
     * @param string $mode
     * @return string
     */
    public function render(string $a_mode = "") : string
    {
        $tpl = new ilTemplate("tpl.prop_multi_text_inp.html", true, true, "Services/Form");
        $i = 0;
        foreach ($this->getIdentifiedMultiValues() as $identifier => $value) {
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
    
    /**
     * @param $value
     * @return bool
     */
    protected function valueHasContentText($value) : bool
    {
        if ($value === null || is_array($value) || is_object($value)) {
            return false;
        }
        
        return (bool) strlen($value);
    }
    
    /**
     * @param $value
     * @return string
     */
    protected function fetchContentTextFromValue($value) : ?string
    {
        if ($this->valueHasContentText($value)) {
            return $value;
        }
        
        return null;
    }
}
