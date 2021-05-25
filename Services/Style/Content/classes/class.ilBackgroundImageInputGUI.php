<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * This class represents a background image property in a property form.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilBackgroundImageInputGUI extends ilFormPropertyGUI
{
    /**
     * @var ilObjUser
     */
    protected $user;

    protected $value;
    
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
        $this->user = $DIC->user();
        parent::__construct($a_title, $a_postvar);
        $this->setType("background_image");
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
    * Set Images.
    *
    * @param	array	$a_images	Images
    */
    public function setImages($a_images)
    {
        $this->images = $a_images;
    }

    /**
    * Get Images.
    *
    * @return	array	Images
    */
    public function getImages()
    {
        return $this->images;
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        $lng = $this->lng;
        
        $type = $_POST[$this->getPostVar()]["type"] =
            ilUtil::stripSlashes($_POST[$this->getPostVar()]["type"]);
        $int_value = $_POST[$this->getPostVar()]["int_value"] =
            ilUtil::stripSlashes($_POST[$this->getPostVar()]["int_value"]);
        $ext_value = $_POST[$this->getPostVar()]["ext_value"] =
            ilUtil::stripSlashes($_POST[$this->getPostVar()]["ext_value"]);
            
        if ($this->getRequired() && $type == "ext" && trim($ext_value) == "") {
            $this->setAlert($lng->txt("msg_input_is_required"));

            return false;
        }

        if ($type == "external") {
            $this->setValue($ext_value);
        } else {
            $this->setValue($int_value);
        }
        
        return true;
    }

    /**
    * Insert property html
    */
    public function insert(&$a_tpl)
    {
        $tpl = new ilTemplate("tpl.prop_background_image.html", true, true, "Services/Style/Content");

        $tpl->setVariable("POSTVAR", $this->getPostVar());
        
        $int_options = array_merge(array("" => ""), $this->getImages());
        
        $value = trim($this->getValue());

        if (is_int(strpos($value, "/"))) {
            $current_type = "ext";
            $tpl->setVariable("EXTERNAL_SELECTED", 'checked="checked"');
            $tpl->setVariable("VAL_EXT", ilUtil::prepareFormOutput($value));
        } else {
            $current_type = "int";
            $tpl->setVariable("INTERNAL_SELECTED", 'checked="checked"');
        }
        
        foreach ($int_options as $option) {
            $tpl->setCurrentBlock("int_option");
            $tpl->setVariable("VAL_INT", $option);
            $tpl->setVariable("TXT_INT", $option);

            if ($current_type == "int" && $value == $option) {
                $tpl->setVariable("INT_SELECTED", 'selected="selected"');
            }
            $tpl->parseCurrentBlock();
        }

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();
    }

    /**
    * Set value by array
    *
    * @param	array	$a_values	value array
    */
    public function setValueByArray($a_values)
    {
        $ilUser = $this->user;
        
        if ($a_values[$this->getPostVar()]["type"] == "internal") {
            $this->setValue($a_values[$this->getPostVar()]["int_value"]);
        } else {
            $this->setValue($a_values[$this->getPostVar()]["ext_value"]);
        }
    }
}
