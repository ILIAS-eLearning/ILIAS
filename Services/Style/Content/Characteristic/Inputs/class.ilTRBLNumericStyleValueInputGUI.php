<?php

declare(strict_types=1);

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
 * This class represents a numeric style property with all/top/right/bottom/left in a property form.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilTRBLNumericStyleValueInputGUI extends ilFormPropertyGUI
{
    protected string $rightvalue = "";
    protected string $leftvalue = "";
    protected string $bottomvalue = "";
    protected string $topvalue = "";
    protected string $allvalue = "";

    /**
     * @var string[]
     */
    protected array $dirs;
    protected ilObjUser $user;
    protected string $value;
    protected bool $allowpercentage = true;

    public function __construct(string $a_title = "", string $a_postvar = "")
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        parent::__construct($a_title, $a_postvar);
        $this->setType("style_numeric");
        $this->dirs = array("all", "top", "bottom", "left", "right");
    }

    public function setAllValue(string $a_allvalue): void
    {
        $this->allvalue = $a_allvalue;
    }

    public function getAllValue(): string
    {
        return $this->allvalue;
    }

    public function setTopValue(string $a_topvalue): void
    {
        $this->topvalue = $a_topvalue;
    }

    public function getTopValue(): string
    {
        return $this->topvalue;
    }

    public function setBottomValue(string $a_bottomvalue): void
    {
        $this->bottomvalue = $a_bottomvalue;
    }

    public function getBottomValue(): string
    {
        return $this->bottomvalue;
    }

    public function setLeftValue(string $a_leftvalue): void
    {
        $this->leftvalue = $a_leftvalue;
    }

    public function getLeftValue(): string
    {
        return $this->leftvalue;
    }

    public function setRightValue(string $a_rightvalue): void
    {
        $this->rightvalue = $a_rightvalue;
    }

    public function getRightValue(): string
    {
        return $this->rightvalue;
    }

    public function setAllowPercentage(bool $a_allowpercentage): void
    {
        $this->allowpercentage = $a_allowpercentage;
    }

    public function getAllowPercentage(): bool
    {
        return $this->allowpercentage;
    }

    public function checkInput(): bool
    {
        $lng = $this->lng;

        $input = $this->getInput();

        foreach ($this->dirs as $dir) {
            $num_value = $input[$dir]["num_value"];
            $num_unit = $input[$dir]["num_unit"];

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

            if (trim($num_value) != "") {
                switch ($dir) {
                    case "all": $this->setAllValue($num_value . $num_unit); break;
                    case "top": $this->setTopValue($num_value . $num_unit); break;
                    case "bottom": $this->setBottomValue($num_value . $num_unit); break;
                    case "left": $this->setLeftValue($num_value . $num_unit); break;
                    case "right": $this->setRightValue($num_value . $num_unit); break;
                }
            }
        }

        return true;
    }

    public function getInput(): array
    {
        return $this->arrayArray($this->getPostVar());
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $lng = $this->lng;

        $value = "";
        $layout_tpl = new ilTemplate("tpl.prop_trbl_layout.html", true, true, "Services/Style/Content");

        foreach ($this->dirs as $dir) {
            $tpl = new ilTemplate("tpl.prop_trbl_style_numeric.html", true, true, "Services/Style/Content");
            $unit_options = ilObjStyleSheet::_getStyleParameterNumericUnits(!$this->getAllowPercentage());

            switch ($dir) {
                case "all": $value = strtolower(trim($this->getAllValue())); break;
                case "top": $value = strtolower(trim($this->getTopValue())); break;
                case "bottom": $value = strtolower(trim($this->getBottomValue())); break;
                case "left": $value = strtolower(trim($this->getLeftValue())); break;
                case "right": $value = strtolower(trim($this->getRightValue())); break;
            }

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

            foreach ($unit_options as $option) {
                $tpl->setCurrentBlock("unit_option");
                $tpl->setVariable("VAL_UNIT", $option);
                $tpl->setVariable("TXT_UNIT", $option);
                if ($current_unit == $option) {
                    $tpl->setVariable("UNIT_SELECTED", 'selected="selected"');
                }
                $tpl->parseCurrentBlock();
            }

            $tpl->setVariable("POSTVAR", $this->getPostVar());
            $tpl->setVariable("VAL_NUM", $disp_val);
            $tpl->setVariable("TXT_DIR", $lng->txt("sty_$dir"));
            $tpl->setVariable("DIR", $dir);

            $layout_tpl->setVariable(strtoupper($dir), $tpl->get());
        }

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $layout_tpl->get());
        $a_tpl->parseCurrentBlock();
    }

    public function setValueByArray(array $a_values): void
    {
        $this->setAllValue($a_values[$this->getPostVar()]["all"]["num_value"] .
            $a_values[$this->getPostVar()]["all"]["num_unit"]);
        $this->setBottomValue($a_values[$this->getPostVar()]["bottom"]["num_value"] .
            $a_values[$this->getPostVar()]["bottom"]["num_unit"]);
        $this->setTopValue($a_values[$this->getPostVar()]["top"]["num_value"] .
            $a_values[$this->getPostVar()]["top"]["num_unit"]);
        $this->setLeftValue($a_values[$this->getPostVar()]["left"]["num_value"] .
            $a_values[$this->getPostVar()]["left"]["num_unit"]);
        $this->setRightValue($a_values[$this->getPostVar()]["right"]["num_value"] .
            $a_values[$this->getPostVar()]["right"]["num_unit"]);
    }
}
