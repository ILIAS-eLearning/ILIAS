<?php namespace ILIAS\GlobalScreen\Scope\Layout\Collector;

use ILIAS\GlobalScreen\Scope\Layout\Provider\FinalModificationProvider;
use ILIAS\UI\Component\Layout\Page\Page;

/**
 * Class MainLayoutCollector
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MainLayoutCollector
{

    /**
     * @var FinalModificationProvider[]
     */
    private $providers = [];


    /**
     * MainLayoutCollector constructor.
     *
     * @param FinalModificationProvider[] $providers
     */
    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }


    public function getFinalPage() : Page
    {
        global $DIC;

        $modifiers = $DIC->globalScreen()->layout()->modifiers();

        foreach ($this->providers as $provider) {
            $modifiers->modifyContentWithInstance($provider->getContentModifier());
            $modifiers->modifyLogoWithInstance($provider->getLogoModifier());
        }

        return $modifiers->getPageWithPagePartProviders();
    }
}
