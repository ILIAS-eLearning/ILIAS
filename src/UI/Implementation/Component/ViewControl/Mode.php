<?php declare(strict_types=1);

/* Copyright (c) 2016 Jesús López <lopez@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\ViewControl;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

class Mode implements C\ViewControl\Mode
{
    use ComponentHelper;

    protected array $labeled_actions;
    protected string $aria_label;
    protected ?string $active = null;

    public function __construct($labelled_actions, string $aria_label)
    {
        $this->labeled_actions = $this->toArray($labelled_actions);
        $this->aria_label = $aria_label;
    }

    public function withActive(string $label) : C\ViewControl\Mode
    {
        $clone = clone $this;
        $clone->active = $label;
        return $clone;
    }

    public function getActive() : ?string
    {
        return $this->active;
    }

    public function getLabelledActions() : array
    {
        return $this->labeled_actions;
    }

    public function getAriaLabel() : string
    {
        return $this->aria_label;
    }
}
