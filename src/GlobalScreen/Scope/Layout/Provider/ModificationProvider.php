<?php namespace ILIAS\GlobalScreen\Scope\Layout\Provider;

use ILIAS\GlobalScreen\Provider\Provider;
use ILIAS\GlobalScreen\Scope\Layout\Factory\Content;
use ILIAS\GlobalScreen\Scope\Layout\Factory\Logo;

/**
 * Interface FinalModificationProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ModificationProvider extends Provider
{

    /**
     * @return Content
     */
    public function getContentModifier() : ?Content;


    /**
     * @return Logo|null
     */
    public function getLogoModifier() : ?Logo;
}
