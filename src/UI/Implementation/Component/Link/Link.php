<?php

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

    /**
     * @var string
     */
    protected $action;

    /**
     * @var bool
     */
    protected $open_in_new_viewport;

    public function __construct($action)
    {
        $this->checkStringArg("action", $action);
        $this->action = $action;
    }

    /**
     * @inheritdoc
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @inheritdoc
     */
    public function withOpenInNewViewport($open_in_new_viewport)
    {
        $clone = clone $this;
        $clone->open_in_new_viewport = (bool) $open_in_new_viewport;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getOpenInNewViewport()
    {
        return $this->open_in_new_viewport;
    }
}
