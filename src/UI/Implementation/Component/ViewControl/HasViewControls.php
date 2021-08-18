<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\ViewControl;

use ILIAS\UI\Component\ViewControl\HasViewControls as HasViewControlsInterface;

/**
 * Trait for panels supporting view controls
 */
trait HasViewControls
{
    /**
     * @var array
     */
    protected $view_controls;

    /**
     * @inheritDoc
     */
    public function withViewControls(array $view_controls) : HasViewControlsInterface
    {
        /**
         * @var $clone HasViewControlsInterface
         */
        $clone = clone $this;
        $clone->view_controls = $view_controls;
        return $clone;
    }
    /**
     * @inheritDoc
     */
    public function getViewControls() : ?array
    {
        return $this->view_controls;
    }
}
