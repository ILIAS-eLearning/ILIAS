<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/interfaces/interface.ilTableFilterItem.php");
include_once("./Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php");
include_once 'Services/UIComponent/Toolbar/interfaces/interface.ilToolbarItem.php';
include_once 'Services/Form/interfaces/interface.ilMultiValuesItem.php';

/**
* This class represents a text property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilTextInputGUI extends ilSubEnabledFormPropertyGUI implements ilTableFilterItem, ilToolbarItem, ilMultiValuesItem
{
    protected $value;
    protected $maxlength = 200;
    protected $size = 40;
    protected $validationRegexp;
    protected $validationFailureMessage = '';
    protected $suffix;
    protected $style_css;
    protected $css_class;
    protected $ajax_datasource;
    protected $ajax_datasource_delimiter;
    protected $ajax_datasource_commit = false;
    protected $ajax_datasource_commit_url;
    protected $submit_form_on_enter = false;

    /**
     * @var bool Flag whether the html autocomplete attribute should be set to "off" or not
     */
    protected $autocomplete_disabled = false;

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
        $this->setInputType("text");
        $this->setType("text");
        $this->validationRegexp = "";
    }

    /**
    * Set Value.
    *
    * @param	string	$a_value	Value
    */
    public function setValue($a_value)
    {
        if ($this->getMulti() && is_array($a_value)) {
            $this->setMultiValues($a_value);
            $a_value = array_shift($a_value);
        }
        $this->value = $a_value;
    }

    /**
    * Get Value.
    *
    * @return	string	Value
    */
    public function getValue()
    {
        return $this->value;
    }
    

    /**
     * Set message string for validation failure
     * @return
     * @param string $a_msg
     */
    public function setValidationFailureMessage($a_msg)
    {
        $this->validationFailureMessage = $a_msg;
    }
    
    public function getValidationFailureMessage()
    {
        return $this->validationFailureMessage;
    }

    /**
    * Set validation regexp.
    *
    * @param	string	$a_value	regexp
    */
    public function setValidationRegexp($a_value)
    {
        $this->validationRegexp = $a_value;
    }

    /**
    * Get validation regexp.
    *
    * @return	string	regexp
    */
    public function getValidationRegexp()
    {
        return $this->validationRegexp;
    }

    /**
    * Set Max Length.
    *
    * @param	int	$a_maxlength	Max Length
    */
    public function setMaxLength($a_maxlength)
    {
        $this->maxlength = $a_maxlength;
    }

    /**
    * Get Max Length.
    *
    * @return	int	Max Length
    */
    public function getMaxLength()
    {
        return $this->maxlength;
    }

    /**
    * Set Size.
    *
    * @param	int	$a_size	Size
    */
    public function setSize($a_size)
    {
        $this->size = $a_size;
    }

    /**
    * Set inline style.
    *
    * @param	string	$a_style	style
    */
    public function setInlineStyle($a_style)
    {
        $this->style_css = $a_style;
    }
    
    /**
    * Get inline style.
    *
    * @return	string	style
    */
    public function getInlineStyle()
    {
        return $this->style_css;
    }
    
    public function setCssClass($a_class)
    {
        $this->css_class = $a_class;
    }
    
    public function getCssClass()
    {
        return $this->css_class;
    }
    
    
    /**
    * Set value by array
    *
    * @param	array	$a_values	value array
    */
    public function setValueByArray($a_values)
    {
        $this->setValue($a_values[$this->getPostVar()]);
    }

    /**
    * Get Size.
    *
    * @return	int	Size
    */
    public function getSize()
    {
        return $this->size;
    }
    
    /**
    * Set suffix.
    *
    * @param	string	$a_value	suffix
    */
    public function setSuffix($a_value)
    {
        $this->suffix = $a_value;
    }

    /**
    * Get suffix.
    *
    * @return	string	suffix
    */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * set input type
     *
     * @access public
     * @param string input type password | text
     *
     */
    public function setInputType($a_type)
    {
        $this->input_type = $a_type;
    }
    
    /**
     * get input type
     *
     * @access public
     */
    public function getInputType()
    {
        return $this->input_type;
    }
    
    /**
     * Set submit form on enter
     *
     * @param	boolean
     */
    public function setSubmitFormOnEnter($a_val)
    {
        $this->submit_form_on_enter = $a_val;
    }
    
    /**
     * Get submit form on enter
     *
     * @return	boolean
     */
    public function getSubmitFormOnEnter()
    {
        return $this->submit_form_on_enter;
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        $lng = $this->lng;
        
        if (!$this->getMulti()) {
            //$_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
            $_POST[$this->getPostVar()] = $this->stripSlashesAddSpaceFallback($_POST[$this->getPostVar()]);
            if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "") {
                $this->setAlert($lng->txt("msg_input_is_required"));

                return false;
            } elseif (strlen($this->getValidationRegexp())) {
                if (!preg_match($this->getValidationRegexp(), $_POST[$this->getPostVar()])) {
                    $this->setAlert(
                        $this->getValidationFailureMessage() ?
                        $this->getValidationFailureMessage() :
                        $lng->txt('msg_wrong_format')
                    );
                    return false;
                }
            }
        } else {
            // #17296
            if (!is_array($_POST[$this->getPostVar()])) {
                $_POST[$this->getPostVar()] = array();
            }
            foreach ($_POST[$this->getPostVar()] as $idx => $value) {
                //$_POST[$this->getPostVar()][$idx] = ilUtil::stripSlashes($value);
                $_POST[$this->getPostVar()][$idx] = $this->stripSlashesAddSpaceFallback($value);
            }
            $_POST[$this->getPostVar()] = array_unique($_POST[$this->getPostVar()]);
            
            if ($this->getRequired() && !trim(implode("", $_POST[$this->getPostVar()]))) {
                $this->setAlert($lng->txt("msg_input_is_required"));

                return false;
            } elseif (strlen($this->getValidationRegexp())) {
                $reg_valid = true;
                foreach ($_POST[$this->getPostVar()] as $value) {
                    if (!preg_match($this->getValidationRegexp(), $value)) {
                        $reg_valid = false;
                        break;
                    }
                }
                if (!$reg_valid) {
                    $this->setAlert(
                        $this->getValidationFailureMessage() ?
                        $this->getValidationFailureMessage() :
                        $lng->txt('msg_wrong_format')
                    );
                    return false;
                }
            }
        }
        
        return $this->checkSubItemsInput();
    }

    /**
     * get datasource link for js autocomplete
     * @return	String	link to data generation script
     */
    public function getDataSource()
    {
        return $this->ajax_datasource;
    }

    /**
     * set datasource link for js autocomplete
     * @param	String	link to data generation script
     */
    public function setDataSource($href, $a_delimiter = null)
    {
        $this->ajax_datasource = $href;
        $this->ajax_datasource_delimiter = $a_delimiter;
    }
    
    public function setDataSourceSubmitOnSelection($a_stat)
    {
        $this->ajax_datasource_commit = $a_stat;
    }
    
    public function getDataSourceSubmitOnSelection()
    {
        return $this->ajax_datasource_commit;
    }
    
    public function setDataSourceSubmitUrl($a_url)
    {
        $this->ajax_datasource_commit_url = $a_url;
    }
    public function getDataSourceSubmitUrl()
    {
        return $this->ajax_datasource_commit_url;
    }
    
    
    public function setMultiValues(array $a_values)
    {
        foreach ($a_values as $idx => $value) {
            $a_values[$idx] = trim($value);
            if ($a_values[$idx] == "") {
                unset($a_values[$idx]);
            }
        }
        parent::setMultiValues($a_values);
    }
    
    /**
    * Render item
    */
    public function render($a_mode = "")
    {
        /**
         * @var $lng ilLanguage
         */
        $lng = $this->lng;
        
        $tpl = new ilTemplate("tpl.prop_textinput.html", true, true, "Services/Form");
        if (strlen($this->getValue())) {
            $tpl->setCurrentBlock("prop_text_propval");
            $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($this->getValue()));
            $tpl->parseCurrentBlock();
        }
        if (strlen($this->getInlineStyle())) {
            $tpl->setCurrentBlock("stylecss");
            $tpl->setVariable("CSS_STYLE", ilUtil::prepareFormOutput($this->getInlineStyle()));
            $tpl->parseCurrentBlock();
        }
        if (strlen($this->getCssClass())) {
            $tpl->setCurrentBlock("classcss");
            $tpl->setVariable('CLASS_CSS', ilUtil::prepareFormOutput($this->getCssClass()));
            $tpl->parseCurrentBlock();
        }
        if ($this->getSubmitFormOnEnter()) {
            $tpl->touchBlock("submit_form_on_enter");
        }

        switch ($this->getInputType()) {
            case 'password':
                $tpl->setVariable('PROP_INPUT_TYPE', 'password');
                break;
            case 'hidden':
                $tpl->setVariable('PROP_INPUT_TYPE', 'hidden');
                break;
            case 'text':
            default:
                $tpl->setVariable('PROP_INPUT_TYPE', 'text');
        }
        $tpl->setVariable("ID", $this->getFieldId());
        $tpl->setVariable("SIZE", $this->getSize());
        if ($this->getMaxLength() != null) {
            $tpl->setVariable("MAXLENGTH", $this->getMaxLength());
        }
        if (strlen($this->getSuffix())) {
            $tpl->setVariable("INPUT_SUFFIX", $this->getSuffix());
        }
        
        $postvar = $this->getPostVar();
        if ($this->getMulti() && substr($postvar, -2) != "[]") {
            $postvar .= "[]";
        }
        
        if ($this->getDisabled()) {
            if ($this->getMulti()) {
                $value = $this->getMultiValues();
                $hidden = "";
                if (is_array($value)) {
                    foreach ($value as $item) {
                        $hidden .= $this->getHiddenTag($postvar, $item);
                    }
                }
            } else {
                $hidden = $this->getHiddenTag($postvar, $this->getValue());
            }
            if ($hidden) {
                $tpl->setVariable("HIDDEN_INPUT", $hidden);
            }
            $tpl->setVariable("DISABLED", " disabled=\"disabled\"");
        } else {
            $tpl->setVariable("POST_VAR", $postvar);
        }

        // use autocomplete feature?
        if ($this->getDataSource()) {
            include_once "Services/jQuery/classes/class.iljQueryUtil.php";
            iljQueryUtil::initjQuery();
            iljQueryUtil::initjQueryUI();
            
            if ($this->getMulti()) {
                $tpl->setCurrentBlock("ac_multi");
                $tpl->setVariable('MURL_AUTOCOMPLETE', $this->getDataSource());
                $tpl->setVariable('ID_AUTOCOMPLETE', $this->getFieldId());
                $tpl->parseCurrentBlock();
                
                // set to fields that start with autocomplete selector
                $sel_auto = '[id^="' . $this->getFieldId() . '"]';
            } else {
                // use id for autocomplete selector
                $sel_auto = "#" . $this->getFieldId();
            }

            $tpl->setCurrentBlock("autocomplete_bl");
            if (!$this->ajax_datasource_delimiter and !$this->getDataSourceSubmitOnSelection()) {
                $tpl->setVariable('SEL_AUTOCOMPLETE', $sel_auto);
                $tpl->setVariable('URL_AUTOCOMPLETE', $this->getDataSource());
            } elseif ($this->getDataSourceSubmitOnSelection()) {
                $tpl->setVariable('SEL_AUTOCOMPLETE_AUTOSUBMIT', $sel_auto);
                $tpl->setVariable('URL_AUTOCOMPLETE_AUTOSUBMIT_REQ', $this->getDataSource());
                $tpl->setVariable('URL_AUTOCOMPLETE_AUTOSUBMIT_RESP', $this->getDataSourceSubmitUrl());
            } else {
                $tpl->setVariable('AUTOCOMPLETE_DELIMITER', $this->ajax_datasource_delimiter);
                $tpl->setVariable('SEL_AUTOCOMPLETE_DELIMITER', $sel_auto);
                $tpl->setVariable('URL_AUTOCOMPLETE_DELIMITER', $this->getDataSource());
            }
            $tpl->parseCurrentBlock();

            $tpl->setVariable('MORE_TXT', $lng->txt('autocomplete_more'));
        }
        
        if ($a_mode == "toolbar") {
            // block-inline hack, see: http://blog.mozilla.com/webdev/2009/02/20/cross-browser-inline-block/
            // -moz-inline-stack for FF2
            // zoom 1; *display:inline for IE6 & 7
            $tpl->setVariable("STYLE_PAR", 'display: -moz-inline-stack; display:inline-block; zoom: 1; *display:inline;');
        } else {
            $tpl->setVariable("STYLE_PAR", '');
        }

        if ($this->isHtmlAutoCompleteDisabled()) {
            $tpl->setVariable("AUTOCOMPLETE", "autocomplete=\"off\"");
        }
        
        if ($this->getRequired()) {
            $tpl->setVariable("REQUIRED", "required=\"required\"");
        }
        
        // multi icons
        if ($this->getMulti() && !$a_mode && !$this->getDisabled()) {
            $tpl->touchBlock("inline_in_bl");
            $tpl->setVariable("MULTI_ICONS", $this->getMultiIconsHTML());
        }

        $tpl->setVariable("ARIA_LABEL", ilUtil::prepareFormOutput($this->getTitle()));

        return $tpl->get();
    }
    
    /**
    * Insert property html
    *
    * @return	int	Size
    */
    public function insert($a_tpl)
    {
        $html = $this->render();

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $html);
        $a_tpl->parseCurrentBlock();
    }
    
    /**
    * Get HTML for table filter
    */
    public function getTableFilterHTML()
    {
        $html = $this->render();
        return $html;
    }

    /**
    * Get HTML for toolbar
    */
    public function getToolbarHTML()
    {
        $html = $this->render("toolbar");
        return $html;
    }

    /**
     * @param boolean $autocomplete
     */
    public function setDisableHtmlAutoComplete($autocomplete)
    {
        $this->autocomplete_disabled = $autocomplete;
    }

    /**
     * @return boolean
     */
    public function isHtmlAutoCompleteDisabled()
    {
        return $this->autocomplete_disabled;
    }
}
