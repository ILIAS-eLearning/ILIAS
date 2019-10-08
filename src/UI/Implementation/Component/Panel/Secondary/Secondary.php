<?php

/* Copyright (c) 2019 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel\Secondary;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Panel\PanelViewControls;

/**
 * Class Secondary
 * @package ILIAS\UI\Implementation\Component\Standard
 */
abstract class Secondary implements C\Panel\Secondary\Secondary
{
    use ComponentHelper;
    use PanelViewControls;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var \ILIAS\UI\Component\Dropdown\Standard
     */
    protected $actions = null;


    /**
     * @var null|\ILIAS\UI\Component\Button\Shy
     */
    protected $footer_component = null;

    /**
     * Gets the secondary panel title
     *
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * Sets the action drop down to be displayed on the right of the title
     * @param C\Dropdown\Standard $actions
     * @return Secondary
     */
    public function withActions(C\Dropdown\Standard $actions) : Secondary
    {
        $clone = clone $this;
        $clone->actions = $actions;
        return $clone;
    }

    /**
     * Gets the action drop down to be displayed on the right of the title
     * @return C\Dropdown\Standard | null
     */
    public function getActions() : ?C\Dropdown\Standard
    {
        return $this->actions;
    }

    /**
     * @inheritdoc
     */
    public function withFooter(C\Button\Shy $component) : C\Panel\Secondary\Secondary
    {
        $clone = clone $this;
        $clone->footer_component = $component;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getFooter() : ?C\Button\Shy
    {
        return $this->footer_component;
    }
}
