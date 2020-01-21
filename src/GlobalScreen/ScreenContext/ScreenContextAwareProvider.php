<?php namespace ILIAS\GlobalScreen\ScreenContext;

use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;

/**
 * Interface ScreenContextAwareProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ScreenContextAwareProvider
{

    /**
     * @return ContextCollection
     */
    public function isInterestedInContexts() : ContextCollection;
}
