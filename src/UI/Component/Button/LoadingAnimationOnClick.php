<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button;

/**
 * Interface for buttons with loading animation on click
 *
 * @author	killing@leifos.de
 */
interface LoadingAnimationOnClick
{
    /**
     * If clicked the button will display a spinner
     * wheel to show that a request is being processed
     * in the background.
     *
     * @return static
     */
    public function withLoadingAnimationOnClick(bool $loading_animation_on_click);

    /**
     * Return whether loading animation has been activated
     */
    public function hasLoadingAnimationOnClick() : bool;
}
