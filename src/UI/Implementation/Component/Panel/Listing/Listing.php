<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel\Listing;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Listing
 * @package ILIAS\UI\Implementation\Component\Panel
 */
abstract class Listing implements C\Panel\Listing\Listing
{
    use ComponentHelper;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var \ILIAS\UI\Component\Dropdown\Standard
     */
    protected $actions = null;

    /**
     * @var \ILIAS\UI\Component\Item\Group[]
     */
    protected $item_groups = array();

    public function __construct($title, $item_groups)
    {
        $this->checkStringArg("title", $title);

        $this->title = $title;
        $this->item_groups = $item_groups;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getItemGroups()
    {
        return $this->item_groups;
    }

    /**
     * @inheritdoc
     */
    public function withActions(\ILIAS\UI\Component\Dropdown\Standard $actions)
    {
        $clone = clone $this;
        $clone->actions = $actions;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getActions()
    {
        return $this->actions;
    }
}
