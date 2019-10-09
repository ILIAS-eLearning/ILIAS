<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Component\Button\Engageable as EngageableInterface;

/**
 * Trait Engageable.
 * Makes a Button stateful.
 * By default, a button is NOT stateful.
 */
trait Engageable
{
    /**
     * @var	bool
     */
    protected $is_engageable = false;

    /**
     * @var	bool
     */
    protected $engaged = false;

    /**
     * @inheritdoc
     */
    public function isEngageable() : bool
    {
        return $this->is_engageable;
    }

    /**
     * @inheritdoc
     */
    public function withEngagedState(bool $state) : EngageableInterface
    {
        $clone = clone $this;
        $clone->is_engageable = true;
        $clone->engaged = $state;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function isEngaged() : bool
    {
        return $this->engaged;
    }
}
