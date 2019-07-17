<?php namespace ILIAS\GlobalScreen\Collector;

use ILIAS\GlobalScreen\Provider\ProviderFactoryInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\MainMenuMainCollector;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Collector\MetaBarMainCollector;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\Tool\Collector\MainToolCollector;
use ILIAS\GlobalScreen\Scope\Tool\Provider\DynamicToolProvider;

/**
 * Class CollectorFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class CollectorFactory
{

    /**
     * @var array
     */
    protected static $instances = [];
    /**
     * @var ProviderFactoryInterface
     */
    private $provider_factory;


    /**
     * CollectorFactory constructor.
     *
     * @param ProviderFactoryInterface $provider_factory
     */
    public function __construct(ProviderFactoryInterface $provider_factory)
    {
        $this->provider_factory = $provider_factory;
    }


    /**
     * @return MainMenuMainCollector
     * @throws \Throwable
     */
    public function mainmenu() : MainMenuMainCollector
    {
        if (!isset(self::$instances[StaticMainMenuProvider::class])) {
            $providers = $this->provider_factory->getMainBarProvider();
            $information = $this->provider_factory->getMainBarItemInformation();
            self::$instances[StaticMainMenuProvider::class] = new MainMenuMainCollector($providers, $information);
        }

        return self::$instances[StaticMainMenuProvider::class];
    }


    /**
     * @return MetaBarMainCollector
     */
    public function metaBar() : MetaBarMainCollector
    {
        if (!isset(self::$instances[StaticMetaBarProvider::class])) {
            self::$instances[StaticMetaBarProvider::class] = new MetaBarMainCollector($this->provider_factory->getMetaBarProvider());
        }

        return self::$instances[StaticMetaBarProvider::class];
    }


    /**
     * @return MainToolCollector
     */
    public function tool() : MainToolCollector
    {
        if (!isset(self::$instances[DynamicToolProvider::class])) {
            self::$instances[DynamicToolProvider::class] = new MainToolCollector($this->provider_factory->getToolProvider());
        }

        return self::$instances[DynamicToolProvider::class];
    }
}
