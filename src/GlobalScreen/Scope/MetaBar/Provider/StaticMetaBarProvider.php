<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Provider;

use ILIAS\GlobalScreen\Provider\StaticProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isItem;

/**
 * Class StaticMetaBarProvider
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface StaticMetaBarProvider extends StaticProvider, MetaBarProviderInterface
{
    /**
     * @return isItem[]
     */
    public function getMetaBarItems() : array;
}
