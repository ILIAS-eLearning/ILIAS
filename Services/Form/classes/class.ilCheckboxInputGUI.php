<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * This class represents a checkbox property in a property form.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilCheckboxInputGUI extends ilSubEnabledFormPropertyGUI implements ilToolbarItem
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    protected $value = "1";
    protected $checked;
    protected $optiontitle = "";
    protected $additional_attributes = '';
    
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
        $this->setType("checkbox");
    }

    /**
    * Set Value.
    *
    * @param	string	$a_value	Value
    */
    public function setValue($a_value)
    {
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
    * Set Checked.
    *
    * @param	boolean	$a_checked	Checked
    */
    public function setChecked($a_checked)
    {
        $this->checked = $a_checked;
    }

    /**
    * Get Checked.
    *
    * @return	boolean	Checked
    */
    public function getChecked()
    {
        return $this->checked;
    }

    /**
    * Set Option Title (optional).
    *
    * @param	string	$a_optiontitle	Option Title (optional)
    */
    public function setOptionTitle($a_optiontitle)
    {
        $this->optiontitle = $a_optiontitle;
    }

    /**
    * Get Option Title (optional).
    *
    * @return	string	Option Title (optional)
    */
    public function getOptionTitle()
    {
        return $this->optiontitle;
    }

    /**
    * Set value by array
    *
    * @param	object	$a_item		Item
    */
    public function setValueByArray($a_values)
    {
        $this->setChecked($a_values[$this->getPostVar()]);
        foreach ($this->getSubItems() as $item) {
            $item->setValueByArray($a_values);
        }
    }
    
    /**
    * Set addiotional attributes
    *
    * @param	string	$a_attrs	addition attribute string
    */
    public function setAdditionalAttributes($a_attrs)
    {
        $this->additional_attributes = $a_attrs;
    }
    
    /**
    * get addtional attributes
    *
    */
    public function getAdditionalAttributes()
    {
        return $this->additional_attributes;
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        $lng = $this->lng;
        
        $_POST[$this->getPostVar()] =
            ilUtil::stripSlashes($_POST[$this->getPostVar()]);
        
        // getRequired() is NOT processed here!

        $ok = $this->checkSubItemsInput();

        // only not ok, if checkbox not checked
        if (!$ok && $_POST[$this->getPostVar()] == "") {
            $ok = true;
        }

        return $ok;
    }
    
    /**
    * Sub form hidden on init?
    *
    */
    public function hideSubForm()
    {
        return !$this->getChecked();
    }

    /**
    * Render item
    */
    public function render($a_mode = '')
    {
        $tpl = new ilTemplate("tpl.prop_checkbox.html", true, true, "Services/Form");
        
        $tpl->setVariable("POST_VAR", $this->getPostVar());
        $tpl->setVariable("ID", $this->getFieldId());
        $tpl->setVariable("PROPERTY_VALUE", $this->getValue());
        $tpl->setVariable("OPTION_TITLE", $this->getOptionTitle());
        if (strlen($this->getAdditionalAttributes())) {
            $tpl->setVariable('PROP_CHECK_ATTRS', $this->getAdditionalAttributes());
        }
        if ($this->getChecked()) {
            $tpl->setVariable(
                "PROPERTY_CHECKED",
                'checked="checked"'
            );
        }
        if ($this->getDisabled()) {
            $tpl->setVariable(
                "DISABLED",
                'disabled="disabled"'
            );
        }
        
        if ($a_mode == "toolbar") {
            // block-inline hack, see: http://blog.mozilla.com/webdev/2009/02/20/cross-browser-inline-block/
            // -moz-inline-stack for FF2
            // zoom 1; *display:inline for IE6 & 7
            $tpl->setVariable("STYLE_PAR", 'display: -moz-inline-stack; display:inline-block; zoom: 1; *display:inline;');
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
    * serialize data
    */
    public function serializeData()
    {
        return serialize($this->getChecked());
    }
    
    /**
    * unserialize data
    */
    public function unserializeData($a_data)
    {
        $data = unserialize($a_data);

        if ($data) {
            $this->setValue($data);
            $this->setChecked(true);
        }
    }
    
    /**
     * Get HTML for toolbar
     */
    public function getToolbarHTML()
    {
        $html = $this->render('toolbar');
        return $html;
    }
}
