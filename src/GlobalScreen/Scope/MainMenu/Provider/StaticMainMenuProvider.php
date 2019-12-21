<?php

namespace ILIAS\GlobalScreen\Scope\MainMenu\Provider;

use ILIAS\GlobalScreen\Provider\StaticProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformationCollection;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopParentItem;

/**
 * Interface StaticMainMenuProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface StaticMainMenuProvider extends StaticProvider, MainMenuProviderInterface
{

    /**
     * @return TopParentItem[] These are Slates which will be
     * available for configuration.
     */
    public function getStaticTopItems() : array;


    /**
     * @return isItem[] These are Entries which will be available for
     * configuration.
     */
    public function getStaticSubItems() : array;


    /**
     * @return TypeInformationCollection
     */
    public function provideTypeInformation() : TypeInformationCollection;
}
