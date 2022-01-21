<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

/**
 * Implements LoadingAnimationOnClick interface
 * @author killing@leifos.de
 */
trait LoadingAnimationOnClick
{
    protected bool $loading_animation_on_click = false;

    /**
     * @inheritdoc
     * @return static
     */
    public function withLoadingAnimationOnClick(bool $loading_animation_on_click = true)
    {
        $clone = clone $this;
        $clone->loading_animation_on_click = $loading_animation_on_click;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function hasLoadingAnimationOnClick() : bool
    {
        return $this->loading_animation_on_click;
    }
}
