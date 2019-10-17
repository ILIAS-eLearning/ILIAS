<?php

/* Copyright (c) 2018 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Card;

use ILIAS\UI\Component\Card as C;
use ILIAS\UI\Component\Icon\Icon;
use ILIAS\UI\Component\Chart\ProgressMeter\ProgressMeter;
use ILIAS\UI\Component\Dropdown\Dropdown;

class RepositoryObject extends Card implements C\RepositoryObject
{

    /**
     * @var Icon
     */
    protected $object_icon;

    /**
     * @var ProgressMeter
     */
    protected $progress;

    /**
     * @var bool
     */
    protected $certificate;

    /**
     * @var Dropdown
     */
    protected $actions;

    /**
     * @param Icon $icon
     * @return RepositoryObject
     */
    public function withObjectIcon(Icon $icon)
    {
        $icon = $icon->withSize("medium");		// ensure same size
        $clone = clone $this;
        $clone->object_icon = $icon;
        return $clone;
    }

    public function getObjectIcon()
    {
        return $this->object_icon;
    }

    /**
     * @param ProgressMeter $a_progressmeter
     * @return RepositoryObject
     */
    public function withProgress(ProgressMeter $a_progressmeter)
    {
        $clone = clone $this;
        $clone->progress = $a_progressmeter;
        return $clone;
    }

    /**
     * Get the progressmeter
     * @return ProgressMeter
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * @param $a_certificate bool
     * @return RepositoryObject
     */
    public function withCertificateIcon($a_certificate) : RepositoryObject
    {
        $clone = clone $this;
        $clone->certificate = $a_certificate;
        return $clone;
    }

    /**
     * Get the certificated icon
     * @return bool
     */
    public function getCertificateIcon()
    {
        return $this->certificate;
    }

    /**
     * @param \ILIAS\UI\Component\Dropdown\Dropdown $dropdown
     * @return RepositoryObject
     */
    public function withActions($dropdown) : RepositoryObject
    {
        $clone = clone $this;
        $clone->actions = $dropdown;
        return $clone;
    }

    /**
     * Get dropdown with different actions.
     * @return Dropdown
     */
    public function getActions()
    {
        return $this->actions;
    }
}
