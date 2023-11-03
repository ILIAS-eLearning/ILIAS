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
 * This class represents a background image property in a property form.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilBackgroundImageInputGUI extends ilFormPropertyGUI
{
    protected array $images;
    protected ilObjUser $user;
    protected string $value;

    public function __construct(string $a_title = "", string $a_postvar = "")
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        parent::__construct($a_title, $a_postvar);
        $this->setType("background_image");
    }

    public function setValue(string $a_value): void
    {
        $this->value = $a_value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setImages(array $a_images): void
    {
        $this->images = $a_images;
    }

    public function getImages(): array
    {
        return $this->images;
    }

    public function checkInput(): bool
    {
        $lng = $this->lng;

        $input = $this->getInput();

        $type = $input["type"] ?? "";
        $int_value = $input["int_value"] ?? "";
        $ext_value = $input["ext_value"] ?? "";

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

    public function getInput(): array
    {
        return $this->strArray($this->getPostVar());
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $tpl = new ilTemplate("tpl.prop_background_image.html", true, true, "Services/Style/Content");

        $tpl->setVariable("POSTVAR", $this->getPostVar());

        $int_options = array_merge(array("" => ""), $this->getImages());

        $value = trim($this->getValue());

        if (is_int(strpos($value, "/"))) {
            $current_type = "ext";
            $tpl->setVariable("EXTERNAL_SELECTED", 'checked="checked"');
            $tpl->setVariable("VAL_EXT", ilLegacyFormElementsUtil::prepareFormOutput($value));
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

    public function setValueByArray(array $a_values): void
    {
        if ($a_values[$this->getPostVar()]["type"] == "internal") {
            $this->setValue($a_values[$this->getPostVar()]["int_value"]);
        } else {
            $this->setValue($a_values[$this->getPostVar()]["ext_value"]);
        }
    }
}
