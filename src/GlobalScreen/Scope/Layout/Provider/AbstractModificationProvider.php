<?php namespace ILIAS\GlobalScreen\Scope\Layout\Provider;

use ILIAS\GlobalScreen\Provider\AbstractProvider;
use ILIAS\GlobalScreen\Scope\Layout\Factory\Content;
use ILIAS\GlobalScreen\Scope\Layout\Factory\Logo;

/**
 * Class AbstractModificationProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractModificationProvider extends AbstractProvider implements ModificationProvider
{

    /**
     * @inheritDoc
     */
    public function getContentModifier() : ?Content
    {
        return null;
    }


    /**
     * @inheritDoc
     */
    public function getLogoModifier() : ?Logo
    {
        return null;
    }
}
