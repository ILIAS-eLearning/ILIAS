<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/classes/class.ilIdentifiedMultiValuesInputGUI.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Services/Form
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
    public function isEditElementOccuranceEnabled()
    {
        return $this->editElementOccuranceEnabled;
    }
    
    /**
     * @param	boolean	$editElementOccuranceEnabled
     */
    public function setEditElementOccuranceEnabled($editElementOccuranceEnabled)
    {
        $this->editElementOccuranceEnabled = $editElementOccuranceEnabled;
    }
    
    /**
     * @return boolean
     */
    public function isEditElementOrderEnabled()
    {
        return $this->editElementOrderEnabled;
    }
    
    /**
     * @param boolean $editElementOrderEnabled
     */
    public function setEditElementOrderEnabled($editElementOrderEnabled)
    {
        $this->editElementOrderEnabled = $editElementOrderEnabled;
    }
    
    /**
     * Check input, strip slashes etc. set alert, if input is not ok.
     *fetchImageTitle
     * @return	boolean		Input ok, true/false
     */
    public function onCheckInput()
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
    public function render($a_mode = "")
    {
        $tpl = new ilTemplate("tpl.prop_multi_text_inp.html", true, true, "Services/Form");
        $i = 0;
        foreach ($this->getIdentifiedMultiValues() as $identifier => $value) {
            if (strlen($value)) {
                $tpl->setCurrentBlock("prop_text_propval");
                $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value));
                $tpl->parseCurrentBlock();
            }
            if ($this->isEditElementOrderEnabled()) {
                $tpl->setCurrentBlock("move");
                $tpl->setVariable("ID_UP", $this->getMultiValuePosIndexedSubFieldId($identifier, 'up', $i));
                $tpl->setVariable("ID_DOWN", $this->getMultiValuePosIndexedSubFieldId($identifier, 'down', $i));
                $tpl->setVariable("CMD_UP", $this->buildMultiValueSubmitVar($identifier, $i, 'up'));
                $tpl->setVariable("CMD_DOWN", $this->buildMultiValueSubmitVar($identifier, $i, 'down'));
                $tpl->setVariable("ID", $this->getMultiValuePosIndexedFieldId($identifier, $i));
                include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
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
                include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
                $tpl->setVariable("ADD_BUTTON", ilGlyphGUI::get(ilGlyphGUI::ADD));
                $tpl->setVariable("REMOVE_BUTTON", ilGlyphGUI::get(ilGlyphGUI::REMOVE));
            }
            
            $tpl->parseCurrentBlock();
            $i++;
        }
        $tpl->setVariable("ELEMENT_ID", $this->getFieldId());
        
        if (!$this->getDisabled()) {
            $tpl->setCurrentBlock('js_engine_initialisation');
            $tpl->touchBlock('js_engine_initialisation');
            $tpl->parseCurrentBlock();
            
            $globalTpl = $GLOBALS['DIC'] ? $GLOBALS['DIC']['tpl'] : $GLOBALS['tpl'];
            $globalTpl->addJavascript("./Services/Form/js/ServiceFormWizardInput.js");
            $globalTpl->addJavascript("./Services/Form/js/ServiceFormIdentifiedWizardInputExtend.js");
        }
        
        return $tpl->get();
    }
    
    /**
     * @param $value
     * @return bool
     */
    protected function valueHasContentText($value)
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
    protected function fetchContentTextFromValue($value)
    {
        if ($this->valueHasContentText($value)) {
            return $value;
        }
        
        return null;
    }
}
