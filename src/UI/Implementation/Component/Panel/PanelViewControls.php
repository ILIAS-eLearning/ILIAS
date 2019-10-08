<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel;

/**
 * Trait for panels supporting view controls
 */
trait PanelViewControls
{
    /**
     * @var array
     */
    protected $view_controls;

    /**
     * @param array $view_controls
     * @return \ILIAS\UI\Component\Component
     */
    public function withViewControls(array $view_controls) : \ILIAS\UI\Component\Component
    {
        $clone = clone $this;
        $clone->view_controls = $view_controls;
        return $clone;
    }
    /**
     * @return array|null
     */
    public function getViewControls(): ?array
    {
        return $this->view_controls;
    }
}