<?php declare(strict_types=1);

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

/**
 * Trait Engageable.
 * Makes a Button stateful.
 * By default, a button is NOT stateful.
 */
trait Engageable
{
    protected bool $is_engageable = false;
    protected bool $engaged = false;

    /**
     * @inheritdoc
     */
    public function isEngageable() : bool
    {
        return $this->is_engageable;
    }

    /**
     * @inheritdoc
     * @return static
     */
    public function withEngagedState(bool $state)
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
