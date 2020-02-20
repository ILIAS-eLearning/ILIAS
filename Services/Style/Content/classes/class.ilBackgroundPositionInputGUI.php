<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
* This class represents a background position in a property form.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilBackgroundPositionInputGUI extends ilFormPropertyGUI
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
        $this->setType("border_width");
        $this->dirs = array("horizontal", "vertical");
    }

    /**
    * Set Horizontal Value.
    *
    * @param	string	$a_horizontalvalue	Horizontal Value
    */
    public function setHorizontalValue($a_horizontalvalue)
    {
        $this->horizontalvalue = $a_horizontalvalue;
    }

    /**
    * Get Horizontal Value.
    *
    * @return	string	Horizontal Value
    */
    public function getHorizontalValue()
    {
        return $this->horizontalvalue;
    }

    /**
    * Set Vertical Value.
    *
    * @param	string	$a_verticalvalue	Vertical Value
    */
    public function setVerticalValue($a_verticalvalue)
    {
        $this->verticalvalue = $a_verticalvalue;
    }

    /**
    * Get Vertical Value.
    *
    * @return	string	Vertical Value
    */
    public function getVerticalValue()
    {
        return $this->verticalvalue;
    }

    /**
    * Get value
    */
    public function getValue()
    {
        if ($this->getHorizontalValue() != "") {
            if ($this->getVerticalValue() != "") {
                return $this->getHorizontalValue() . " " . $this->getVerticalValue();
            } else {
                return $this->getHorizontalValue();
            }
        } else {
            if ($this->getVerticalValue() != "") {
                return "left " . $this->getVerticalValue();
            }
        }
        return "";
    }
    
    /**
    * Set value
    */
    public function setValue($a_val)
    {
        $a_val = trim($a_val);
        $a_val_arr = explode(" ", $a_val);
        $hor = trim($a_val_arr[0]);
        $ver = trim($a_val_arr[1]);
        if ($hor == "center" && $ver == "") {
            $ver = "center";
        }
        $this->setHorizontalValue($hor);
        $this->setVerticalValue($ver);
    }
    
    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        $lng = $this->lng;
        
        foreach ($this->dirs as $dir) {
            $type = $_POST[$this->getPostVar()][$dir]["type"] =
                ilUtil::stripSlashes($_POST[$this->getPostVar()][$dir]["type"]);
            $num_value = $_POST[$this->getPostVar()][$dir]["num_value"] =
                trim(ilUtil::stripSlashes($_POST[$this->getPostVar()][$dir]["num_value"]));
            $num_unit = $_POST[$this->getPostVar()][$dir]["num_unit"] =
                trim(ilUtil::stripSlashes($_POST[$this->getPostVar()][$dir]["num_unit"]));
            $pre_value = $_POST[$this->getPostVar()][$dir]["pre_calue"] =
                ilUtil::stripSlashes($_POST[$this->getPostVar()][$dir]["pre_value"]);
                
            /*
            if ($this->getRequired() && trim($num_value) == "")
            {
                $this->setAlert($lng->txt("msg_input_is_required"));

                return false;
            }*/
            
            if (!is_numeric($num_value) && trim($num_value) != "") {
                $this->setAlert($lng->txt("sty_msg_input_must_be_numeric"));
                return false;
            }
            
            $value = "";
            if ($type == "numeric") {
                if ($num_value != "") {
                    $value = $num_value . $num_unit;
                }
            } else {
                $value = $pre_value;
            }
            
            if (trim($value) != "") {
                switch ($dir) {
                    case "horizontal": $this->setHorizontalValue($value); break;
                    case "vertical": $this->setVerticalValue($value); break;
                }
            }
        }
        
        return true;
    }

    /**
    * Insert property html
    */
    public function insert(&$a_tpl)
    {
        $lng = $this->lng;
        
        $layout_tpl = new ilTemplate("tpl.prop_hv_layout.html", true, true, "Services/Style/Content");
        
        foreach ($this->dirs as $dir) {
            $tpl = new ilTemplate("tpl.prop_background_position.html", true, true, "Services/Style/Content");
            $unit_options = ilObjStyleSheet::_getStyleParameterNumericUnits();
            $pre_options = ilObjStyleSheet::_getStyleParameterValues("background-position");
            $pre_options = $pre_options[$dir];
            switch ($dir) {
                case "horizontal": $value = strtolower(trim($this->getHorizontalValue())); break;
                case "vertical": $value = strtolower(trim($this->getVerticalValue())); break;
            }
    
            if (in_array($value, $pre_options)) {
                $current_type = "pre";
                $tpl->setVariable("PREDEFINED_SELECTED", 'checked="checked"');
            } else {
                $current_type = "unit";
                $tpl->setVariable("NUMERIC_SELECTED", 'checked="checked"');

                $current_unit = "";
                foreach ($unit_options as $u) {
                    if (substr($value, strlen($value) - strlen($u)) == $u) {
                        $current_unit = $u;
                    }
                }
                $disp_val = substr($value, 0, strlen($value) - strlen($current_unit));
                if ($current_unit == "") {
                    $current_unit = "px";
                }
                $tpl->setVariable("VAL_NUM", $disp_val);
            }
            
            foreach ($unit_options as $option) {
                $tpl->setCurrentBlock("unit_option");
                $tpl->setVariable("VAL_UNIT", $option);
                $tpl->setVariable("TXT_UNIT", $option);
                if ($current_type == "unit" && $current_unit == $option) {
                    $tpl->setVariable("UNIT_SELECTED", 'selected="selected"');
                }
                $tpl->parseCurrentBlock();
            }
            
            foreach ($pre_options as $option) {
                $tpl->setCurrentBlock("pre_option");
                $tpl->setVariable("VAL_PRE", $option);
                $tpl->setVariable("TXT_PRE", $option);
                if ($current_type == "pre" && $value == $option) {
                    $tpl->setVariable("PRE_SELECTED", 'selected="selected"');
                }
                $tpl->parseCurrentBlock();
            }

            $tpl->setVariable("POSTVAR", $this->getPostVar());
            $tpl->setVariable("TXT_DIR", $lng->txt("sty_$dir"));
            $tpl->setVariable("DIR", $dir);
            
            $layout_tpl->setVariable(strtoupper($dir), $tpl->get());
        }
        $layout_tpl->setVariable("COLSPAN", "2");
        
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $layout_tpl->get());
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
        
        if ($a_values[$this->getPostVar()]["horizontal"]["type"] == "predefined") {
            $this->setHorizontalValue($a_values[$this->getPostVar()]["horizontal"]["pre_value"]);
        } else {
            $this->setHorizontalValue($a_values[$this->getPostVar()]["horizontal"]["num_value"] .
                $a_values[$this->getPostVar()]["horizontal"]["num_unit"]);
        }
        if ($a_values[$this->getPostVar()]["vertical"]["type"] == "predefined") {
            $this->setVerticalValue($a_values[$this->getPostVar()]["vertical"]["pre_value"]);
        } else {
            $this->setVerticalValue($a_values[$this->getPostVar()]["vertical"]["num_value"] .
                $a_values[$this->getPostVar()]["vertical"]["num_unit"]);
        }
    }
}
