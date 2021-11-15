<?php declare(strict_types=1);

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel\Listing;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Item\Group;

/**
 * Class Listing
 * @package ILIAS\UI\Implementation\Component\Panel
 */
abstract class Listing implements C\Panel\Listing\Listing
{
    use ComponentHelper;

    protected string $title;
    protected ?C\Dropdown\Standard $actions = null;

    /**
     * @var Group[]
     */
    protected array $item_groups = array();

    public function __construct(string $title, array $item_groups)
    {
        $this->title = $title;
        $this->item_groups = $item_groups;
    }

    /**
     * @inheritdoc
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getItemGroups() : array
    {
        return $this->item_groups;
    }

    /**
     * @inheritdoc
     */
    public function withActions(C\Dropdown\Standard $actions) : C\Panel\Listing\Listing
    {
        $clone = clone $this;
        $clone->actions = $actions;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getActions() : ?C\Dropdown\Standard
    {
        return $this->actions;
    }
}
