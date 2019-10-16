<?php

namespace ILIAS\UI\Implementation\Component\MainControls;

use ILIAS\UI\Component\Button\Close;
use ILIAS\UI\Component\MainControls;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class ModeInfo
 *
 * @package ILIAS\UI\Implementation\Component\MainControls
 */
class ModeInfo implements MainControls\ModeInfo
{

    use ComponentHelper;
    /**
     * @var bool
     */
    private $is_important = false;
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
     * ModeInfo constructor.
     *
     * @param string $title
     * @param Close  $close_button
     */
    public function __construct(string $title, Close $close_button)
    {
        $this->title = $title;
        $this->close_button = $close_button;
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
    public function withDescription(string $info_message) : MainControls\ModeInfo
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
    public function getCloseButton() : Close
    {
        return $this->close_button;
    }
}
