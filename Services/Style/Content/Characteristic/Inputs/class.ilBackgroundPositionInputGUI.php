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
 * This class represents a background position in a property form.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilBackgroundPositionInputGUI extends ilFormPropertyGUI
{
    /**
     * @var string[]
     */
    protected array $dirs = [];
    protected string $verticalvalue = "";
    protected string $horizontalvalue = "";
    protected ilObjUser $user;
    protected string $value = "";
    
    public function __construct(string $a_title = "", string $a_postvar = "")
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        parent::__construct($a_title, $a_postvar);
        $this->setType("border_width");
        $this->dirs = array("horizontal", "vertical");
    }

    public function setHorizontalValue(string $a_horizontalvalue) : void
    {
        $this->horizontalvalue = $a_horizontalvalue;
    }

    public function getHorizontalValue() : string
    {
        return $this->horizontalvalue;
    }

    public function setVerticalValue(string $a_verticalvalue) : void
    {
        $this->verticalvalue = $a_verticalvalue;
    }

    public function getVerticalValue() : string
    {
        return $this->verticalvalue;
    }

    public function getValue() : string
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
    
    public function setValue(string $a_val) : void
    {
        $a_val = trim($a_val);
        $a_val_arr = explode(" ", $a_val);
        $hor = trim($a_val_arr[0]);
        $ver = trim($a_val_arr[1] ?? "");
        if ($hor == "center" && $ver == "") {
            $ver = "center";
        }
        $this->setHorizontalValue($hor);
        $this->setVerticalValue($ver);
    }
    
    public function checkInput() : bool
    {
        $lng = $this->lng;

        $input = $this->getInput();
        
        foreach ($this->dirs as $dir) {
            $type = $input[$dir]["type"];
            $num_value = $input[$dir]["num_value"];
            $num_unit = $input[$dir]["num_unit"];
            $pre_value = $input[$dir]["pre_value"];
                
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

    public function getInput() : array
    {
        return $this->arrayArray($this->getPostVar());
    }

    public function insert(ilTemplate $a_tpl) : void
    {
        $lng = $this->lng;
        
        $layout_tpl = new ilTemplate("tpl.prop_hv_layout.html", true, true, "Services/Style/Content");
        
        foreach ($this->dirs as $dir) {
            $value = "";
            $current_unit = "";
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

    public function setValueByArray(array $a_values) : void
    {
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
