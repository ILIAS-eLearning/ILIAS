<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Divider;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Horizontal Divider
 */
class Horizontal implements C\Divider\Horizontal
{
    use ComponentHelper;

    /**
     * @var string
     */
    protected $label;

    public function __construct()
    {
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function withLabel($label)
    {
        $this->checkStringArg("label", $label);
        $clone = clone $this;
        $clone->label = $label;
        return $clone;
    }
}
