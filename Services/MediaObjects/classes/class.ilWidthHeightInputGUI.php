<?php

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
 * This class represents a width/height item in a property form.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilWidthHeightInputGUI extends ilFormPropertyGUI
{
    protected bool $constrainproportions;
    protected ?int $height = null;
    protected ?int $width = null;
    protected array $dirs = [];
    protected ilObjUser $user;
    protected \ilGlobalTemplateInterface $main_tpl;

    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        parent::__construct($a_title, $a_postvar);
        $this->setType("width_height");
        $this->main_tpl = $DIC->ui()->mainTemplate();
    }

    public function setWidth(?int $a_width): void
    {
        $this->width = $a_width;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setHeight(?int $a_height): void
    {
        $this->height = $a_height;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setConstrainProportions(bool $a_constrainproportions): void
    {
        $this->constrainproportions = $a_constrainproportions;
    }

    public function getConstrainProportions(): bool
    {
        return $this->constrainproportions;
    }

    public function checkInput(): bool
    {
        $i = $this->getInput();
        $this->setWidth($i["width"] ? (int) $i["width"] : null);
        $this->setHeight($i["height"] ? (int) $i["height"] : null);
        $this->setConstrainProportions((bool) $i["constr_prop"]);

        return true;
    }

    public function getInput(): array
    {
        $val = $this->strArray($this->getPostVar());
        return [
            "width" => (string) ($val["width"] ?? ""),
            "height" => (string) ($val["height"] ?? ""),
            "constr_prop" => (bool) ($val["constr_prop"] ?? false)
        ];
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $lng = $this->lng;

        $tpl = new ilTemplate("tpl.prop_width_height.html", true, true, "Services/MediaObjects");

        $tpl->setVariable("VAL_WIDTH", strtolower(trim($this->getWidth())));
        $tpl->setVariable("VAL_HEIGHT", strtolower(trim($this->getHeight())));
        if ($this->getConstrainProportions()) {
            $tpl->setVariable("CHECKED", 'checked="checked"');
        }

        $tpl->setVariable("POST_VAR", $this->getPostVar());
        $tpl->setVariable("TXT_CONSTR_PROP", $lng->txt("cont_constrain_proportions"));
        $wh_ratio = 0;
        if ((int) $this->getHeight() > 0) {
            $wh_ratio = (int) $this->getWidth() / (int) $this->getHeight();
        }
        $ratio = str_replace(",", ".", round($wh_ratio, 6));

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();

        $this->main_tpl
            ->addJavaScript("./Services/MediaObjects/js/ServiceMediaObjectPropWidthHeight.js");
        $this->main_tpl->addOnLoadCode(
            'prop_width_height["prop_' . $this->getPostVar() . '"] = ' . $ratio . ';'
        );
    }

    public function setValueByArray(array $a_values): void
    {
        $w = $a_values[$this->getPostVar()]["width"] ?? false;
        $h = $a_values[$this->getPostVar()]["height"] ?? false;
        $this->setWidth($w ? (int) $w : null);
        $this->setHeight($h ? (int) $h : null);
        $this->setConstrainProportions($a_values[$this->getPostVar()]["constr_prop"] ?? false);
    }
}
