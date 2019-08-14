<?php

namespace ILIAS\GlobalScreen\Collector\Renderer;

use ILIAS\UI\Component\Button\Bulky;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\MainControls\Slate\Slate;

/**
 * Trait isSupportedTrait
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait isSupportedTrait
{

    /**
     * @param Component $component
     *
     * @return bool
     */
    private function isComponentSupportedForCombinedSlate(Component $component) : bool
    {
        return ($component instanceof Bulky || $component instanceof Slate);
    }
}
