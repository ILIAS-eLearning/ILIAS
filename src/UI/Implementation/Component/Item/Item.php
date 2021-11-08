<?php declare(strict_types=1);

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Item;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Link\Link;
use ILIAS\UI\Component\Image\Image;

/**
 * Common interface to all items.
 */
abstract class Item implements C\Item\Item
{
    use ComponentHelper;

    /**
     * @var string|Shy|Link
     */
    protected $title;
    protected ?string $desc = null;
    protected array $props;
    protected ?C\Dropdown\Standard $actions = null;

    /**
     * @var null|string|Image
     */
    protected $lead = null;

    /**
     * Item constructor.
     * @param Shy|C\Link\Standard|string $title
     */
    public function __construct($title)
    {
        if (!$title instanceof Shy && !$title instanceof Link) {
            $this->checkStringArg("title", $title);
        }

        $this->title = $title;
        $this->props = [];
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
    public function withDescription(string $description) : C\Item\Item
    {
        $clone = clone $this;
        $clone->desc = $description;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getDescription() : ?string
    {
        return $this->desc;
    }

    /**
     * @inheritdoc
     */
    public function withProperties(array $properties) : C\Item\Item
    {
        $clone = clone $this;
        $clone->props = $properties;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getProperties() : array
    {
        return $this->props;
    }

    /**
     * @inheritdoc
     */
    public function withActions(C\Dropdown\Standard $actions) : C\Item\Item
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
