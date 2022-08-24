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
 * Color picker form for selecting color hexcodes using yui library (all/top/right/bottom/left)
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilTRBLColorPickerInputGUI extends ilTextInputGUI
{
    protected bool $acceptnamedcolors = false;
    protected string $defaultcolor = "";
    protected string $allvalue = "";
    protected string $leftvalue = "";
    protected string $rightvalue = "";
    protected string $bottomvalue = "";
    protected string $topvalue = "";

    /**
     * @var string[]
     */
    protected array $dirs = [];
    protected string $hex = "";

    public function __construct(string $a_title = "", string $a_postvar = "")
    {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->setType("trbl_color");
        $this->dirs = array("all", "top", "bottom", "left", "right");
    }

    public function setAllValue(string $a_allvalue): void
    {
        $a_allvalue = trim($a_allvalue);
        if ($this->getAcceptNamedColors() && substr($a_allvalue, 0, 1) == "!") {
            $this->allvalue = $a_allvalue;
        } else {
            $this->allvalue = ilColorPickerInputGUI::determineHexcode($a_allvalue);
        }
    }

    public function getAllValue(): string
    {
        return $this->allvalue;
    }

    public function setTopValue(string $a_topvalue): void
    {
        $a_topvalue = trim($a_topvalue);
        if ($this->getAcceptNamedColors() && substr($a_topvalue, 0, 1) == "!") {
            $this->topvalue = $a_topvalue;
        } else {
            $this->topvalue = ilColorPickerInputGUI::determineHexcode($a_topvalue);
        }
    }

    public function getTopValue(): string
    {
        return $this->topvalue;
    }

    public function setBottomValue(string $a_bottomvalue): void
    {
        $a_bottomvalue = trim($a_bottomvalue);
        if ($this->getAcceptNamedColors() && substr($a_bottomvalue, 0, 1) == "!") {
            $this->bottomvalue = $a_bottomvalue;
        } else {
            $this->bottomvalue = ilColorPickerInputGUI::determineHexcode($a_bottomvalue);
        }
    }

    public function getBottomValue(): string
    {
        return $this->bottomvalue;
    }

    public function setLeftValue(string $a_leftvalue): void
    {
        $a_leftvalue = trim($a_leftvalue);
        if ($this->getAcceptNamedColors() && substr($a_leftvalue, 0, 1) == "!") {
            $this->leftvalue = $a_leftvalue;
        } else {
            $this->leftvalue = ilColorPickerInputGUI::determineHexcode($a_leftvalue);
        }
    }

    public function getLeftValue(): string
    {
        return $this->leftvalue;
    }

    public function setRightValue(string $a_rightvalue): void
    {
        $a_rightvalue = trim($a_rightvalue);
        if ($this->getAcceptNamedColors() && substr($a_rightvalue, 0, 1) == "!") {
            $this->rightvalue = $a_rightvalue;
        } else {
            $this->rightvalue = ilColorPickerInputGUI::determineHexcode($a_rightvalue);
        }
    }

    public function getRightValue(): string
    {
        return $this->rightvalue;
    }

    public function setDefaultColor(string $a_defaultcolor): void
    {
        $this->defaultcolor = $a_defaultcolor;
    }

    public function getDefaultColor(): string
    {
        return $this->defaultcolor;
    }

    public function setAcceptNamedColors(bool $a_acceptnamedcolors): void
    {
        $this->acceptnamedcolors = $a_acceptnamedcolors;
    }

    public function getAcceptNamedColors(): bool
    {
        return $this->acceptnamedcolors;
    }

    public function checkInput(): bool
    {
        $input = $this->getInput();
        foreach ($this->dirs as $dir) {
            $value = $input[$dir]["value"];

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

    public function getInput(): array
    {
        return $this->arrayArray($this->getPostVar());
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $lng = $this->lng;

        $layout_tpl = new ilTemplate("tpl.prop_trbl_layout.html", true, true, "Services/Style/Content");

        $funcs = array(
            "all" => "getAllValue", "top" => "getTopValue",
            "bottom" => "getBottomValue", "left" => "getLeftValue",
            "right" => "getRightValue");

        foreach ($this->dirs as $dir) {
            $f = $funcs[$dir];
            $value = trim($this->$f());
            if (!$this->getAcceptNamedColors() || substr($value, 0, 1) != "!") {
                $value = strtoupper($value);
            }

            $tpl = new ilTemplate('tpl.prop_color.html', true, true, 'Services/Form');
            $tpl->setVariable('COLOR_ID', $this->getFieldId() . "_" . $dir);
            $ic = ilColorPickerInputGUI::determineHexcode($value);
            if ($ic == "") {
                $ic = "FFFFFF";
            }
            $tpl->setVariable('INIT_COLOR_SHORT', $ic);
            $tpl->setVariable('POST_VAR', $this->getPostVar());

            if ($this->getDisabled()) {
                $a_tpl->setVariable('COLOR_DISABLED', 'disabled="disabled"');
            }

            $tpl->setVariable("POST_VAR", $this->getPostVar() . "[" . $dir . "][value]");
            $tpl->setVariable("PROP_COLOR_ID", $this->getFieldId() . "_" . $dir);

            if (substr(trim((string) $this->getValue()), 0, 1) == "!" && $this->getAcceptNamedColors()) {
                $tpl->setVariable(
                    "PROPERTY_VALUE_COLOR",
                    ilLegacyFormElementsUtil::prepareFormOutput(trim($this->getValue()))
                );
            } else {
                $tpl->setVariable("PROPERTY_VALUE_COLOR", ilLegacyFormElementsUtil::prepareFormOutput($value));
                $tpl->setVariable('INIT_COLOR', '#' . $value);
            }

            $tpl->setVariable("TXT_PREFIX", $lng->txt("sty_$dir"));

            $layout_tpl->setVariable(strtoupper($dir), $tpl->get());
        }

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $layout_tpl->get());
        $a_tpl->parseCurrentBlock();
    }
}
