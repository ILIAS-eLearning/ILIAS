<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory;

use Closure;
use ILIAS\UI\Component\Component;

/**
 * Interface hasContent
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface hasContent
{

    /**
     * @param Closure $content_wrapper a closure which returns a UI-Component
     *                                 This wins over a withContent
     *
     * @return hasContent
     */
    public function withContentWrapper(Closure $content_wrapper) : hasContent;


    /**
     * @param \ILIAS\UI\Component\Component $ui_component
     *
     * @return hasContent
     * @deprecated Use withContentWrapper instead
     */
    public function withContent(Component $ui_component) : hasContent;


    /**
     * @return Component
     */
    public function getContent() : Component;
}
