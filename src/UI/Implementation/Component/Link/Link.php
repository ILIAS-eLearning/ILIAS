<?php declare(strict_types=1);

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Link;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * This implements commonalities between Links
 */
abstract class Link implements C\Link\Link
{
    use ComponentHelper;

    protected string $action;
    protected ?bool $open_in_new_viewport = null;

    public function __construct(string $action)
    {
        $this->action = $action;
    }

    /**
     * @inheritdoc
     */
    public function getAction() : string
    {
        return $this->action;
    }

    /**
     * @inheritdoc
     */
    public function withOpenInNewViewport(bool $open_in_new_viewport) : C\Link\Link
    {
        $clone = clone $this;
        $clone->open_in_new_viewport = $open_in_new_viewport;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getOpenInNewViewport() : ?bool
    {
        return $this->open_in_new_viewport;
    }
}
