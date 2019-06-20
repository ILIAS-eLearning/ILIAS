<?php namespace ILIAS\GlobalScreen\Scope\Layout\Provider;

use ILIAS\GlobalScreen\Provider\Provider;
use ILIAS\GlobalScreen\Scope\Layout\ModifierServices;

/**
 * Interface FinalModificationProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface FinalModificationProvider extends Provider
{

    /**
     * @param ModifierServices $modifier_services
     */
    public function modifyGlobalLayout(ModifierServices $modifier_services) : void;
}
