<?php namespace ILIAS\GlobalScreen\Scope\Layout;

/**
 * Interface FinalPageHandler
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface FinalPageHandler
{

    /**
     * @param ModifierServices $modifier_services
     */
    public function handle(ModifierServices $modifier_services) : void;
}
