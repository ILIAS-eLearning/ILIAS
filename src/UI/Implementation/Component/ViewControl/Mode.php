<?php

/* Copyright (c) 2016 Jesús López <lopez@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\ViewControl;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

class Mode implements C\ViewControl\Mode
{
    use ComponentHelper;

    /**
     * @var array
     */
    protected $labeled_actions;

    /**
     * @var	string
     */
    protected $aria_label;

    /**
     * @var string
     */
    protected $active;

    public function __construct($labelled_actions, $aria_label)
    {
        $this->labeled_actions = $this->toArray($labelled_actions);
        $this->checkStringArg("string", $aria_label);
        $this->aria_label = $aria_label;
    }

    public function withActive($label)
    {
        $this->checkStringArg("label", $label);
        $clone = clone $this;
        $clone->active = $label;
        return $clone;
    }

    public function getActive()
    {
        return $this->active;
    }

    public function getLabelledActions()
    {
        return $this->labeled_actions;
    }

    public function getAriaLabel()
    {
        return $this->aria_label;
    }
}
