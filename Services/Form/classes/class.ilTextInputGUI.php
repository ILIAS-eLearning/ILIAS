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
 * This class represents a text property in a property form.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilTextInputGUI extends ilSubEnabledFormPropertyGUI implements ilTableFilterItem, ilToolbarItem, ilMultiValuesItem
{
    /**
     * @var string|array
     */
    protected $value = null;
    protected int $maxlength = 200;
    protected int $size = 40;
    protected string $validationRegexp = "";
    protected string $validationFailureMessage = '';
    protected string $suffix = "";
    protected string $style_css = "";
    protected string $css_class = "";
    protected string $ajax_datasource = "";
    protected ?string $ajax_datasource_delimiter = null;
    protected bool $ajax_datasource_commit = false;
    protected string $ajax_datasource_commit_url = "";
    protected bool $submit_form_on_enter = false;
    // Flag whether the html autocomplete attribute should be set to "off" or not
    protected bool $autocomplete_disabled = false;
    protected string $input_type = "";

    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;
        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->setInputType("text");
        $this->setType("text");
        $this->validationRegexp = "";
    }

    /**
     * @param string|array $a_value
     */
    public function setValue($a_value) : void
    {
        if ($this->getMulti() && is_array($a_value)) {
            $this->setMultiValues($a_value);
            $a_value = array_shift($a_value);
        }
        $this->value = $a_value;
    }

    /**
     * @return array|string
     */
    public function getValue()
    {
        return $this->value;
    }

    public function setValidationFailureMessage(string $a_msg) : void
    {
        $this->validationFailureMessage = $a_msg;
    }
    
    public function getValidationFailureMessage() : string
    {
        return $this->validationFailureMessage;
    }

    public function setValidationRegexp(string $a_value) : void
    {
        $this->validationRegexp = $a_value;
    }

    public function getValidationRegexp() : string
    {
        return $this->validationRegexp;
    }

    public function setMaxLength(int $a_maxlength) : void
    {
        $this->maxlength = $a_maxlength;
    }

    public function getMaxLength() : int
    {
        return $this->maxlength;
    }

    public function setSize(int $a_size) : void
    {
        $this->size = $a_size;
    }

    public function setInlineStyle(string $a_style) : void
    {
        $this->style_css = $a_style;
    }
    
    public function getInlineStyle() : string
    {
        return $this->style_css;
    }
    
    public function setCssClass(string $a_class) : void
    {
        $this->css_class = $a_class;
    }
    
    public function getCssClass() : string
    {
        return $this->css_class;
    }

    public function setValueByArray(array $a_values) : void
    {
        $this->setValue($a_values[$this->getPostVar()] ?? "");
    }

    public function getSize() : int
    {
        return $this->size;
    }

    public function setSuffix(string $a_value) : void
    {
        $this->suffix = $a_value;
    }

    public function getSuffix() : string
    {
        return $this->suffix;
    }

    /**
     * set input type
     * @param string $a_type input type password | text
     */
    public function setInputType(string $a_type) : void
    {
        $this->input_type = $a_type;
    }

    public function getInputType() : string
    {
        return $this->input_type;
    }

    public function setSubmitFormOnEnter(bool $a_val) : void
    {
        $this->submit_form_on_enter = $a_val;
    }
    
    public function getSubmitFormOnEnter() : bool
    {
        return $this->submit_form_on_enter;
    }

    public function checkInput() : bool
    {
        $lng = $this->lng;
        
        if (!$this->getMulti()) {
            if ($this->getRequired() && trim($this->str($this->getPostVar())) == "") {
                $this->setAlert($lng->txt("msg_input_is_required"));
                return false;
            } elseif (strlen($this->getValidationRegexp())) {
                if (!preg_match($this->getValidationRegexp(), $this->str($this->getPostVar()))) {
                    $this->setAlert(
                        $this->getValidationFailureMessage() ?:
                        $lng->txt('msg_wrong_format')
                    );
                    return false;
                }
            }
        } else {
            if ($this->getRequired() &&
                !trim(implode("", $this->strArray($this->getPostVar())))) {
                $this->setAlert($lng->txt("msg_input_is_required"));
                return false;
            } elseif (strlen($this->getValidationRegexp())) {
                $reg_valid = true;
                foreach ($this->strArray($this->getPostVar()) as $value) {
                    if (!preg_match($this->getValidationRegexp(), $value)) {
                        $reg_valid = false;
                        break;
                    }
                }
                if (!$reg_valid) {
                    $this->setAlert(
                        $this->getValidationFailureMessage() ?:
                        $lng->txt('msg_wrong_format')
                    );
                    return false;
                }
            }
        }
        
        return $this->checkSubItemsInput();
    }

    /**
     * @return string|string[]
     */
    public function getInput()
    {
        if (!$this->getMulti()) {
            return $this->str($this->getPostVar());
        }
        return $this->strArray($this->getPostVar());
    }

    public function getDataSource() : string
    {
        return $this->ajax_datasource;
    }

    public function setDataSource(
        string $href,
        string $a_delimiter = null
    ) : void {
        $this->ajax_datasource = $href;
        $this->ajax_datasource_delimiter = $a_delimiter;
    }
    
    public function setDataSourceSubmitOnSelection(bool $a_stat) : void
    {
        $this->ajax_datasource_commit = $a_stat;
    }
    
    public function getDataSourceSubmitOnSelection() : bool
    {
        return $this->ajax_datasource_commit;
    }
    
    public function setDataSourceSubmitUrl(string $a_url) : void
    {
        $this->ajax_datasource_commit_url = $a_url;
    }

    public function getDataSourceSubmitUrl() : string
    {
        return $this->ajax_datasource_commit_url;
    }
    
    
    public function setMultiValues(array $a_values) : void
    {
        foreach ($a_values as $idx => $value) {
            $a_values[$idx] = trim($value);
            if ($a_values[$idx] == "") {
                unset($a_values[$idx]);
            }
        }
        parent::setMultiValues($a_values);
    }
    
    public function render(string $a_mode = "") : string
    {
        $lng = $this->lng;
        
        $tpl = new ilTemplate("tpl.prop_textinput.html", true, true, "Services/Form");
        if (strlen((string) $this->getValue())) {
            $tpl->setCurrentBlock("prop_text_propval");
            $tpl->setVariable("PROPERTY_VALUE", ilLegacyFormElementsUtil::prepareFormOutput((string) $this->getValue()));
            $tpl->parseCurrentBlock();
        }
        if (strlen($this->getInlineStyle())) {
            $tpl->setCurrentBlock("stylecss");
            $tpl->setVariable("CSS_STYLE", ilLegacyFormElementsUtil::prepareFormOutput((string) $this->getInlineStyle()));
            $tpl->parseCurrentBlock();
        }
        if (strlen($this->getCssClass())) {
            $tpl->setCurrentBlock("classcss");
            $tpl->setVariable('CLASS_CSS', ilLegacyFormElementsUtil::prepareFormOutput((string) $this->getCssClass()));
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

        $tpl->setVariable("POST_VAR", $postvar);
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
        }

        // use autocomplete feature?
        if ($this->getDataSource()) {
            iljQueryUtil::initjQuery();
            iljQueryUtil::initjQueryUI();
            
            $jstpl = new ilTemplate("tpl.prop_text_autocomplete.js", true, true, "Services/Form");

            if ($this->getMulti()) {
                $jstpl->setCurrentBlock("ac_multi");
                $jstpl->setVariable('MURL_AUTOCOMPLETE', $this->getDataSource());
                $jstpl->setVariable('ID_AUTOCOMPLETE', $this->getFieldId());
                $jstpl->parseCurrentBlock();
                
                // set to fields that start with autocomplete selector
                $sel_auto = '[id^="' . $this->getFieldId() . '"]';
            } else {
                // use id for autocomplete selector
                $sel_auto = "#" . $this->getFieldId();
            }

            $jstpl->setCurrentBlock("autocomplete_bl");
            if (!$this->ajax_datasource_delimiter and !$this->getDataSourceSubmitOnSelection()) {
                $jstpl->setVariable('SEL_AUTOCOMPLETE', $sel_auto);
                $jstpl->setVariable('URL_AUTOCOMPLETE', $this->getDataSource());
            } elseif ($this->getDataSourceSubmitOnSelection()) {
                $jstpl->setVariable('SEL_AUTOCOMPLETE_AUTOSUBMIT', $sel_auto);
                $jstpl->setVariable('URL_AUTOCOMPLETE_AUTOSUBMIT_REQ', $this->getDataSource());
                $jstpl->setVariable('URL_AUTOCOMPLETE_AUTOSUBMIT_RESP', $this->getDataSourceSubmitUrl());
            } else {
                $jstpl->setVariable('AUTOCOMPLETE_DELIMITER', $this->ajax_datasource_delimiter);
                $jstpl->setVariable('SEL_AUTOCOMPLETE_DELIMITER', $sel_auto);
                $jstpl->setVariable('URL_AUTOCOMPLETE_DELIMITER', $this->getDataSource());
            }
            $jstpl->parseCurrentBlock();

            $jstpl->setVariable('MORE_TXT', $lng->txt('autocomplete_more'));
            $this->global_tpl->addOnloadCode($jstpl->get());
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

        $tpl->setVariable("ARIA_LABEL", ilLegacyFormElementsUtil::prepareFormOutput($this->getTitle()));

        return $tpl->get();
    }
    
    public function insert(ilTemplate $a_tpl) : void
    {
        $html = $this->render();

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $html);
        $a_tpl->parseCurrentBlock();
    }
    
    public function getTableFilterHTML() : string
    {
        $html = $this->render();
        return $html;
    }

    public function getToolbarHTML() : string
    {
        $html = $this->render("toolbar");
        return $html;
    }

    public function setDisableHtmlAutoComplete(bool $autocomplete) : void
    {
        $this->autocomplete_disabled = $autocomplete;
    }

    public function isHtmlAutoCompleteDisabled() : bool
    {
        return $this->autocomplete_disabled;
    }
}
