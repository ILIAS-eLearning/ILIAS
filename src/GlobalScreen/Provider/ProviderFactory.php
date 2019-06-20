<?php namespace ILIAS\GlobalScreen\Provider;

use ILIAS\GlobalScreen\Scope\Layout\Provider\FinalModificationProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\ItemInformation;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\Tool\Provider\DynamicToolProvider;

/**
 * Class ProviderFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ProviderFactory implements ProviderFactoryInterface
{

    /**
     * @var FinalModificationProvider[]
     */
    private $final_modification_providers = [];
    /**
     * @var StaticMainMenuProvider[]
     */
    private $main_bar_providers = [];
    /**
     * @var StaticMetaBarProvider[]
     */
    private $meta_bar_providers = [];
    /**
     * @var DynamicToolProvider[]
     */
    private $tool_providers = [];
    /**
     * @var ItemInformation
     */
    private $main_menu_item_information = null;
    /**
     * @var Provider[]
     */
    protected $all_providers;


    /**
     * ProviderFactory constructor.
     *
     * @param StaticMainMenuProvider[] $main_bar_providers
     * @param StaticMetaBarProvider[]  $meta_bar_providers
     * @param DynamicToolProvider[]    $tool_providers
     * @param ItemInformation          $main_menu_item_information
     */
    public function __construct(array $main_bar_providers, array $meta_bar_providers, array $tool_providers, ItemInformation $main_menu_item_information)
    {
        $this->main_bar_providers = $main_bar_providers;
        $this->meta_bar_providers = $meta_bar_providers;
        $this->tool_providers = $tool_providers;
        $this->main_menu_item_information = $main_menu_item_information;

        $this->registerInternal($main_bar_providers);
        $this->registerInternal($meta_bar_providers);
        $this->registerInternal($tool_providers);
    }


    /**
     * @param array $providers
     */
    protected function registerInternal(array $providers)
    {
        array_walk(
            $providers, function (Provider $item) {
            $this->all_providers[get_class($item)] = $item;
        }
        );
    }


    /**
     * @inheritDoc
     */
    public function getFinalModificationProvider() : array
    {
        return $this->final_modification_providers;
    }


    /**
     * @inheritDoc
     */
    public function getMainBarProvider() : array
    {
        return $this->main_bar_providers;
    }


    /**
     * @inheritDoc
     */
    public function getToolProvider() : array
    {
        return $this->tool_providers;
    }


    /**
     * @inheritDoc
     */
    public function getMetaBarProvider() : array
    {
        return $this->meta_bar_providers;
    }


    /**
     * @inheritDoc
     */
    public function getMainBarItemInformation() : ItemInformation
    {
        return $this->main_menu_item_information;
    }


    /**
     * @inheritDoc
     */
    public function getProviderByClassName(string $class_name) : Provider
    {
        return $this->all_providers[$class_name];
    }


    /**
     * @inheritDoc
     */
    public function isInstanceCreationPossible(string $class_name) : bool
    {
        return class_exists($class_name); // && isset($this->all_providers[$class_name])
    }
}
