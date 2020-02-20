<?php

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Dropdown;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

/**
 * This implements commonalities between different types of Dropdowns.
 */
abstract class Dropdown implements C\Dropdown\Dropdown
{
    use ComponentHelper;
    use JavaScriptBindable;
    use Triggerer;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $aria_label;

    /**
     * @var array<\ILIAS\UI\Component\Button\Shy|\ILIAS\UI\Component\Divider\Horizontal>
     */
    protected $items;

    /**
     * Dropdown constructor.
     * @param array<\ILIAS\UI\Component\Button\Shy|\ILIAS\UI\Component\Divider\Horizontal> $items
     */
    public function __construct($items)
    {
        $this->items = $items;
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function getAriaLabel()
    {
        return $this->aria_label;
    }

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function withLabel($label)
    {
        $this->checkStringArg("label", $label);
        $clone = clone $this;
        $clone->label = $label;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withAriaLabel($label)
    {
        $this->checkStringArg("label", $label);
        $clone = clone $this;
        $clone->aria_label = $label;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withOnClick(Signal $signal)
    {
        return $this->withTriggeredSignal($signal, 'click');
    }

    /**
     * @inheritdoc
     */
    public function appendOnClick(Signal $signal)
    {
        return $this->appendTriggeredSignal($signal, 'click');
    }

    /**
     * @inheritdoc
     */
    public function withOnHover(Signal $signal)
    {
        return $this->withTriggeredSignal($signal, 'hover');
    }

    /**
     * @inheritdoc
     */
    public function appendOnHover(Signal $signal)
    {
        return $this->appendTriggeredSignal($signal, 'hover');
    }
}
