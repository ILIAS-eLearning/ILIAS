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
 * Button GUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @deprecated use KS Buttons instead
 */
abstract class ilButtonBase implements ilToolbarItem
{
    protected ilLanguage $lng;
    protected int $type = 0; // [int]
    protected ?string $id = ""; // [string]
    protected string $caption = ""; // [string]
    protected bool $caption_is_lng_id = false; // [bool]
    protected bool $primary = false; // [bool]
    protected bool $omit_prevent_double_submission = false; // [bool]
    protected string $onclick = ""; // [string]
    protected int $acc_key = 0;
    protected bool $disabled = false; // [bool]
    protected array $css = array(); // [array]
    protected bool $apply_default_css = true;

    public const TYPE_SUBMIT = 1;
    public const TYPE_LINK = 2;
    public const TYPE_SPLIT = 3;
    public const TYPE_BUTTON = 4;

    protected function __construct(int $a_type)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->setType($a_type);
    }

    public function __clone()
    {
        $this->setId(null);
    }

    abstract public static function getInstance(): self;

    //
    // properties
    //

    protected function setType(int $a_value): void
    {
        $this->type = $a_value;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setId(?string $a_value): void
    {
        $this->id = $a_value;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setCaption(string $a_value, bool $a_is_lng_id = true): void
    {
        $this->caption = $a_value;
        $this->caption_is_lng_id = $a_is_lng_id;
    }

    public function getCaption(bool $a_translate = true): string
    {
        $lng = $this->lng;

        $caption = $this->caption;

        if ($this->caption_is_lng_id &&
            $a_translate) {
            $caption = $lng->txt($caption);
        }

        return $caption;
    }

    public function setPrimary(bool $a_value): void
    {
        $this->primary = $a_value;
    }

    public function isPrimary(): bool
    {
        return $this->primary;
    }

    /**
     * Toggle double submission prevention status
     */
    public function setOmitPreventDoubleSubmission(bool $a_value): void
    {
        $this->omit_prevent_double_submission = $a_value;
    }

    public function getOmitPreventDoubleSubmission(): bool
    {
        return $this->omit_prevent_double_submission;
    }

    public function setOnClick(string $a_value): void
    {
        $this->onclick = trim($a_value);
    }

    public function getOnClick(): string
    {
        return $this->onclick;
    }

    public function setDisabled(bool $a_value): void
    {
        $this->disabled = $a_value;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function addCSSClass(string $a_value): void
    {
        $this->css[] = $a_value;
    }

    public function getCSSClasses(): array
    {
        return $this->css;
    }


    //
    // render
    //

    protected function gatherCssClasses(): string
    {
        $css = array_unique($this->getCSSClasses());

        if ($this->isPrimary()) {
            $css[] = "btn-primary";
        }
        if ($this->getOmitPreventDoubleSubmission()) {
            $css[] = "omitPreventDoubleSubmission";
        }

        return implode(" ", $css);
    }

    protected function renderAttributesHelper(array $a_attr): string
    {
        $res = array();

        foreach ($a_attr as $id => $value) {
            if (trim($value)) {
                $res[] = strtolower(trim($id)) . '="' . $value . '"';
            }
        }

        if (count($res)) {
            return " " . implode(" ", $res);
        }
        return "";
    }

    /**
     * Render current HTML attributes
     */
    protected function renderAttributes(array $a_additional_attr = null): string
    {
        $attr = array();
        $attr["id"] = $this->getId();
        $attr["class"] = $this->gatherCssClasses();
        $attr["onclick"] = $this->getOnClick();

        if ($this->isDisabled()) {
            $attr["disabled"] = "disabled";
        }

        if (count($a_additional_attr)) {
            $attr = array_merge($attr, $a_additional_attr);
        }

        return $this->renderAttributesHelper($attr);
    }

    protected function prepareRender(): void
    {
        if ($this->applyDefaultCss()) {
            $this->addCSSClass("btn");
            $this->addCSSClass("btn-default");
        }
    }

    public function applyDefaultCss(?bool $apply_default_css = null): ?bool
    {
        if (null === $apply_default_css) {
            return $this->apply_default_css;
        }

        $this->apply_default_css = $apply_default_css;
        return false;
    }

    abstract public function render(): string;

    public function getToolbarHTML(): string
    {
        return $this->render();
    }
}
