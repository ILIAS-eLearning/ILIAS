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
 * Color picker form for selecting color hexcodes using yui library
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilColorPickerInputGUI extends ilTextInputGUI
{
    protected string $hex = "";
    protected bool $acceptnamedcolors = false;
    protected string $defaultcolor = "";

    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        parent::__construct($a_title, $a_postvar);
        $this->setType("color");
        $this->setDefaultColor("04427e");
    }

    public function checkInput(): bool
    {
        if ($this->getRequired() && !strlen($this->getInput())
        ) {
            $this->setAlert($this->lng->txt("msg_input_is_required"));
            return false;
        }

        return true;
    }

    public function getInput(): string
    {
        $value = trim($this->str($this->getPostVar()));
        if ($this->getAcceptNamedColors() && substr($value, 0, 1) == "!") {
            return $value;
        }
        return $this->determineHexcode($value);
    }

    public function setValueByArray(array $a_values): void
    {
        $this->setValue($a_values[$this->getPostVar()]);
    }

    public function setValue($a_value): void
    {
        $a_value = trim($a_value);
        if ($this->getAcceptNamedColors() && substr($a_value, 0, 1) == "!") {
            parent::setValue($a_value);
        } else {
            $this->hex = ilColorPickerInputGUI::determineHexcode($a_value);
            parent::setValue($this->getHexcode());
        }
    }

    public function setDefaultColor(string $a_defaultcolor): void
    {
        $this->defaultcolor = $a_defaultcolor;
    }

    public function getDefaultColor(): string
    {
        return $this->defaultcolor;
    }

    // Set Accept Named Colors (Leading '!').
    public function setAcceptNamedColors(bool $a_acceptnamedcolors): void
    {
        $this->acceptnamedcolors = $a_acceptnamedcolors;
    }

    public function getAcceptNamedColors(): bool
    {
        return $this->acceptnamedcolors;
    }

    public function getHexcode(): string
    {
        if (strpos($this->hex, '#') === 0) {
            return substr($this->hex, 1);
        }
        return $this->hex ?: $this->getDefaultColor();
    }

    public static function determineHexcode(string $a_value): string
    {
        $a_value = trim(strtolower($a_value));

        // remove leading #
        if (strpos($a_value, '#') === 0) {
            $a_value = substr($a_value, 1);
        }

        // handle standard color names (no leading (!))
        switch ($a_value) {
            // html4 colors
            case "black": $a_value = "000000";
                break;
            case "maroon": $a_value = "800000";
                break;
            case "green": $a_value = "008000";
                break;
            case "olive": $a_value = "808000";
                break;
            case "navy": $a_value = "000080";
                break;
            case "purple": $a_value = "800080";
                break;
            case "teal": $a_value = "008080";
                break;
            case "silver": $a_value = "C0C0C0";
                break;
            case "gray": $a_value = "808080";
                break;
            case "red": $a_value = "ff0000";
                break;
            case "lime": $a_value = "00ff00";
                break;
            case "yellow": $a_value = "ffff00";
                break;
            case "blue": $a_value = "0000ff";
                break;
            case "fuchsia": $a_value = "ff00ff";
                break;
            case "aqua": $a_value = "00ffff";
                break;
            case "white": $a_value = "ffffff";
                break;

                // other colors used by ILIAS, supported by modern browsers
            case "brown": $a_value = "a52a2a";
                break;
        }

        // handle rgb values
        if (substr($a_value, 0, 3) == "rgb") {
            $pos1 = strpos($a_value, "(");
            $pos2 = strpos($a_value, ")");
            $rgb = explode(",", substr($a_value, $pos1 + 1, $pos2 - $pos1 - 1));
            $r = str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
            $g = str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
            $b = str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);
            $a_value = $r . $g . $b;
        }

        $a_value = trim(strtolower($a_value));

        // expand three digit hex numbers
        if (preg_match("/^[a-f0-9]{3}/", $a_value) && strlen($a_value) == 3) {
            $a_value = "" . $a_value;
            $a_value = $a_value[0] . $a_value[0] . $a_value[1] . $a_value[1] . $a_value[2] . $a_value[2];
        }

        if (!preg_match("/^[a-f0-9]{6}/", $a_value)) {
            $a_value = "";
        }

        return strtoupper($a_value);
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $tpl = new ilTemplate('tpl.prop_color.html', true, true, 'Services/Form');
        $tpl->setVariable('COLOR_ID', $this->getFieldId());
        $ic = ilColorPickerInputGUI::determineHexcode($this->getHexcode());
        if ($ic == "") {
            $ic = "FFFFFF";
        }
        $tpl->setVariable('INIT_COLOR_SHORT', $ic);
        $tpl->setVariable('POST_VAR', $this->getPostVar());

        if ($this->getDisabled()) {
            $a_tpl->setVariable('COLOR_DISABLED', 'disabled="disabled"');
        }

        $tpl->setVariable("POST_VAR", $this->getPostVar());
        $tpl->setVariable("PROP_COLOR_ID", $this->getFieldId());

        if (substr(trim($this->getValue()), 0, 1) == "!" && $this->getAcceptNamedColors()) {
            $tpl->setVariable(
                "PROPERTY_VALUE_COLOR",
                ilLegacyFormElementsUtil::prepareFormOutput(trim($this->getValue()))
            );
        } else {
            $tpl->setVariable("PROPERTY_VALUE_COLOR", ilLegacyFormElementsUtil::prepareFormOutput($this->getHexcode()));
            $tpl->setVariable('INIT_COLOR', '#' . $this->getHexcode());
        }

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();
    }
}
