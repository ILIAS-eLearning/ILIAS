<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls;

use ILIAS\UI\Component\Button\Button;
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
     * @var string
     */
    private $title = '';
    /**
     * @var string
     */
    private $description = '';
    /**
     * @var
     */
    private $close_button;


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
    public function withDescription(string $info_message) : \ILIAS\UI\Component\MainControls\HeadInfo
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
    public function withCloseButton(Button $button)
    {
        $clone = clone $this;
        $clone->close_button = $button;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getCloseButton() : Button
    {
        return $this->close_button;
    }
}
