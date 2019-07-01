<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory;

use ILIAS\UI\Component\Component;

/**
 * Interface hasContent
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface hasContent
{

    /**
     * @param \ILIAS\UI\Component\Component $ui_component
     *
     * @return hasContent
     */
    public function withContent(Component $ui_component) : hasContent;


    /**
     * @return Component
     */
    public function getContent() : Component;
}
