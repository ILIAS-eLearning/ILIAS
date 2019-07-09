<?php namespace ILIAS\GlobalScreen\Scope\Layout\Collector;

use ILIAS\GlobalScreen\Scope\Layout\Factory\Content;
use ILIAS\GlobalScreen\Scope\Layout\Factory\Logo;
use ILIAS\GlobalScreen\Scope\Layout\ModifierHandler;
use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\UI\Component\Layout\Page\Page;

/**
 * Class MainLayoutCollector
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MainLayoutCollector
{

    /**
     * @var ModificationProvider[]
     */
    private $providers = [];


    /**
     * MainLayoutCollector constructor.
     *
     * @param ModificationProvider[] $providers
     */
    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }


    /**
     * @return Page
     */
    public function getFinalPage() : Page
    {
        $modifiers = new ModifierHandler();

        foreach ($this->providers as $provider) {
            if ($provider->getContentModifier() instanceof Content && $provider->getContentModifier()->hasValidModification()) {
                $modifiers->modifyContentWithClosure($provider->getContentModifier()->getModification());
            }
            if ($provider->getLogoModifier() instanceof Logo && $provider->getLogoModifier()->hasValidModification()) {
                $modifiers->modifyLogoWithClosure($provider->getLogoModifier()->getModification());
            }
        }

        return $modifiers->getPageWithPagePartProviders();
    }
}
