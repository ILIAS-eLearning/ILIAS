<?php

namespace ILIAS\GlobalScreen\Collector\Renderer;

use ILIAS\UI\Component\Button\Bulky as BulkyButton;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Divider\Horizontal;
use ILIAS\UI\Component\Link\Bulky as BulkyLink;
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
    protected function isComponentSupportedForCombinedSlate(Component $component) : bool
    {
        return ($component instanceof BulkyButton || $component instanceof Slate || $component instanceof BulkyLink || $component instanceof Horizontal);
    }


    /**
     * @param Component $component
     *
     * @return bool
     */
    protected function isSupportedForMetaBar(Component $component) : bool
    {
        return ($component instanceof BulkyButton || $component instanceof Slate);
    }
}
