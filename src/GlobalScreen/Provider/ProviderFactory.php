<?php namespace ILIAS\GlobalScreen\Provider;

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
     * @var StaticMainMenuProvider[]
     */
    private $main_bar_providers = [];
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
     * @param array           $main_bar_providers
     * @param ItemInformation $main_menu_item_information
     */
    public function __construct(array $main_bar_providers, ItemInformation $main_menu_item_information)
    {
        $this->main_bar_providers = $main_bar_providers;
        $this->main_menu_item_information = $main_menu_item_information;

        $this->registerInternal($main_bar_providers);
    }


    /**
     * @param array $providers
     */
    protected function registerInternal(array $providers)
    {
        array_walk(
            $providers,
            function (Provider $item) {
                $this->all_providers[get_class($item)] = $item;
            }
        );
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
    public function getMainBarItemInformation() : ItemInformation
    {
        return $this->main_menu_item_information;
    }


    /**
     * @inheritDoc
     */
    public function getProviderByClassName(string $class_name) : Provider
    {
        if (!$this->isInstanceCreationPossible($class_name) || !$this->isRegistered($class_name)) {
            throw new \LogicException("the GlobalScreen-Provider $class_name is not available");
        }

        return $this->all_providers[$class_name];
    }


    /**
     * @inheritDoc
     */
    public function isInstanceCreationPossible(string $class_name) : bool
    {
        return class_exists($class_name);
    }


    /**
     * @inheritDoc
     */
    public function isRegistered(string $class_name) : bool
    {
        return isset($this->all_providers[$class_name]);
    }
}
