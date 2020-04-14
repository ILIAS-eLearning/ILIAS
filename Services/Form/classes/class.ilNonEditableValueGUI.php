<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Form/interfaces/interface.ilMultiValuesItem.php';

/**
* This class represents a non editable value in a property form.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilNonEditableValueGUI extends ilSubEnabledFormPropertyGUI implements ilTableFilterItem, ilMultiValuesItem
{
    protected $type;
    protected $value;
    protected $title;
    protected $info;
    protected $section_icon;
    protected $disable_escaping;
    
    /**
    * Constructor
    *
    * @param
    */
    public function __construct($a_title = "", $a_id = "", $a_disable_escaping = false)
    {
        parent::__construct($a_title, $a_id);
        $this->setTitle($a_title);
        $this->setType("non_editable_value");
        $this->disable_escaping = (bool) $a_disable_escaping;
    }
    
    public function checkInput()
    {
        if (!is_array($_POST[$this->getPostVar()])) {
            $_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
        }
        return $this->checkSubItemsInput();
    }

    /**
    * Set Type.
    *
    * @param	string	$a_type	Type
    */
    public function setType($a_type)
    {
        $this->type = $a_type;
    }

    /**
    * Get Type.
    *
    * @return	string	Type
    */
    public function getType()
    {
        return $this->type;
    }
    
    /**
    * Set Title.
    *
    * @param	string	$a_title	Title
    */
    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }

    /**
    * Get Title.
    *
    * @return	string	Title
    */
    public function getTitle()
    {
        return $this->title;
    }

    /**
    * Set Information Text.
    *
    * @param	string	$a_info	Information Text
    */
    public function setInfo($a_info)
    {
        $this->info = $a_info;
    }

    /**
    * Get Information Text.
    *
    * @return	string	Information Text
    */
    public function getInfo()
    {
        return $this->info;
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
    * render
    */
    public function render()
    {
        $tpl = new ilTemplate("tpl.non_editable_value.html", true, true, "Services/Form");
        if ($this->getPostVar() != "") {
            $postvar = $this->getPostVar();
            if ($this->getMulti() && substr($postvar, -2) != "[]") {
                $postvar .= "[]";
            }
            
            $tpl->setCurrentBlock("hidden");
            $tpl->setVariable('NON_EDITABLE_ID', $postvar);
            $tpl->setVariable('MULTI_HIDDEN_ID', $this->getFieldId());
            $tpl->setVariable("HVALUE", ilUtil::prepareFormOutput($this->getValue()));
            $tpl->parseCurrentBlock();
        }
        $value = $this->getValue();
        if (!$this->disable_escaping) {
            $value = ilUtil::prepareFormOutput($value);
        }
        $tpl->setVariable("VALUE", $value);
        $tpl->setVariable("ID", $this->getFieldId());
        $tpl->parseCurrentBlock();
        
        if ($this->getMulti() && $postvar != "" && !$this->getDisabled()) {
            $tpl->setVariable("MULTI_ICONS", $this->getMultiIconsHTML());
        }

        
        return $tpl->get();
    }
    
    /**
    * Insert property html
    *
    */
    public function insert($a_tpl)
    {
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $this->render());
        $a_tpl->parseCurrentBlock();
    }
    
    /**
    * Set value by array
    *
    * @param	array	$a_values	value array
    */
    public function setValueByArray($a_values)
    {
        if ($this->getPostVar() && isset($a_values[$this->getPostVar()])) {
            $this->setValue($a_values[$this->getPostVar()]);
        }
        foreach ($this->getSubItems() as $item) {
            $item->setValueByArray($a_values);
        }
    }
    
    /**
    * Get HTML for table filter
    */
    public function getTableFilterHTML()
    {
        $html = $this->render();
        return $html;
    }
}
