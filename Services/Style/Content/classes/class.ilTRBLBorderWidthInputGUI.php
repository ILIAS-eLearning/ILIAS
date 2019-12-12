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
* This class represents a border width with all/top/right/bottom/left in a property form.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilTRBLBorderWidthInputGUI extends ilFormPropertyGUI
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
        $this->dirs = array("all", "top", "bottom", "left", "right");
    }

    /**
    * Set All Value.
    *
    * @param	string	$a_allvalue	All Value
    */
    public function setAllValue($a_allvalue)
    {
        $this->allvalue = $a_allvalue;
    }

    /**
    * Get All Value.
    *
    * @return	string	All Value
    */
    public function getAllValue()
    {
        return $this->allvalue;
    }

    /**
    * Set Top Value.
    *
    * @param	string	$a_topvalue	Top Value
    */
    public function setTopValue($a_topvalue)
    {
        $this->topvalue = $a_topvalue;
    }

    /**
    * Get Top Value.
    *
    * @return	string	Top Value
    */
    public function getTopValue()
    {
        return $this->topvalue;
    }

    /**
    * Set Bottom Value.
    *
    * @param	string	$a_bottomvalue	Bottom Value
    */
    public function setBottomValue($a_bottomvalue)
    {
        $this->bottomvalue = $a_bottomvalue;
    }

    /**
    * Get Bottom Value.
    *
    * @return	string	Bottom Value
    */
    public function getBottomValue()
    {
        return $this->bottomvalue;
    }

    /**
    * Set Left Value.
    *
    * @param	string	$a_leftvalue	Left Value
    */
    public function setLeftValue($a_leftvalue)
    {
        $this->leftvalue = $a_leftvalue;
    }

    /**
    * Get Left Value.
    *
    * @return	string	Left Value
    */
    public function getLeftValue()
    {
        return $this->leftvalue;
    }

    /**
    * Set Right Value.
    *
    * @param	string	$a_rightvalue	Right Value
    */
    public function setRightValue($a_rightvalue)
    {
        $this->rightvalue = $a_rightvalue;
    }

    /**
    * Get Right Value.
    *
    * @return	string	Right Value
    */
    public function getRightValue()
    {
        return $this->rightvalue;
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
            
            if (!is_numeric($num_value) && $num_value != "") {
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
                    case "all": $this->setAllValue($value); break;
                    case "top": $this->setTopValue($value); break;
                    case "bottom": $this->setBottomValue($value); break;
                    case "left": $this->setLeftValue($value); break;
                    case "right": $this->setRightValue($value); break;
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
        
        $layout_tpl = new ilTemplate("tpl.prop_trbl_layout.html", true, true, "Services/Style/Content");
        
        foreach ($this->dirs as $dir) {
            $tpl = new ilTemplate("tpl.prop_trbl_border_width.html", true, true, "Services/Style/Content");
            $unit_options = ilObjStyleSheet::_getStyleParameterNumericUnits();
            $pre_options = ilObjStyleSheet::_getStyleParameterValues("border-width");
            
            switch ($dir) {
                case "all": $value = strtolower(trim($this->getAllValue())); break;
                case "top": $value = strtolower(trim($this->getTopValue())); break;
                case "bottom": $value = strtolower(trim($this->getBottomValue())); break;
                case "left": $value = strtolower(trim($this->getLeftValue())); break;
                case "right": $value = strtolower(trim($this->getRightValue())); break;
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
        
        if ($a_values[$this->getPostVar()]["all"]["type"] == "predefined") {
            $this->setAllValue($a_values[$this->getPostVar()]["all"]["pre_value"]);
        } else {
            $this->setAllValue($a_values[$this->getPostVar()]["all"]["num_value"] .
                $a_values[$this->getPostVar()]["all"]["num_unit"]);
        }
        if ($a_values[$this->getPostVar()]["bottom"]["type"] == "predefined") {
            $this->setBottomValue($a_values[$this->getPostVar()]["bottom"]["pre_value"]);
        } else {
            $this->setBottomValue($a_values[$this->getPostVar()]["bottom"]["num_value"] .
                $a_values[$this->getPostVar()]["bottom"]["num_unit"]);
        }
        if ($a_values[$this->getPostVar()]["top"]["type"] == "predefined") {
            $this->setTopValue($a_values[$this->getPostVar()]["top"]["pre_value"]);
        } else {
            $this->setTopValue($a_values[$this->getPostVar()]["top"]["num_value"] .
                $a_values[$this->getPostVar()]["top"]["num_unit"]);
        }
        if ($a_values[$this->getPostVar()]["left"]["type"] == "predefined") {
            $this->setLeftValue($a_values[$this->getPostVar()]["left"]["pre_value"]);
        } else {
            $this->setLeftValue($a_values[$this->getPostVar()]["left"]["num_value"] .
                $a_values[$this->getPostVar()]["left"]["num_unit"]);
        }
        if ($a_values[$this->getPostVar()]["right"]["type"] == "predefined") {
            $this->setRightValue($a_values[$this->getPostVar()]["right"]["pre_value"]);
        } else {
            $this->setRightValue($a_values[$this->getPostVar()]["right"]["num_value"] .
                $a_values[$this->getPostVar()]["right"]["num_unit"]);
        }
    }
}
