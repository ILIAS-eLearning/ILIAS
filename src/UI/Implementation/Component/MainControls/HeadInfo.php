<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls;

use ILIAS\Data\URI;
use ILIAS\UI\Component\MainControls;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class HeadInfo
 *
 * @package ILIAS\UI\Implementation\Component\MainControls
 */
class HeadInfo implements MainControls\HeadInfo
{

    use ComponentHelper;
    /**
     * @var bool
     */
    private $is_interruptive = false;
    /**
     * @var URI
     */
    private $close_action;
    /**
     * @var string
     */
    private $title = '';
    /**
     * @var string
     */
    private $description = '';


    /**
     * HeadInfo constructor.
     *
     * @param string $title
     */
    public function __construct(string $title)
    {
        $this->title = $title;
    }


    /**
     * @inheritDoc
     */
    public function getTitle() : string
    {
        return $this->title;
    }


    /**
     * @inheritDoc
     */
    public function withDescription(string $info_message) : MainControls\HeadInfo
    {
        $clone = clone $this;
        $clone->description = $info_message;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getDescription() : string
    {
        return $this->description;
    }


    /**
     * @inheritDoc
     */
    public function withCloseAction(URI $uri) : \ILIAS\UI\Component\MainControls\HeadInfo
    {
        $clone = clone $this;
        $clone->close_action = $uri;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getCloseAction() : ?URI
    {
        return $this->close_action;
    }


    /**
     * @inheritDoc
     */
    public function withInterruptive(bool $is_interruptive) : \ILIAS\UI\Component\MainControls\HeadInfo
    {
        $clone = clone $this;
        $clone->is_interruptive = $is_interruptive;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function isInterruptive() : bool
    {
        return $this->is_interruptive;
    }
}
