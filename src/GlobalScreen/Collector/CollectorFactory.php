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
        if (!isset(self::$instances[StaticMainMenuProvider::PURPOSE_MAINBAR])) {
            $providers = $this->provider_factory->getMainBarProvider();
            $information = $this->provider_factory->getMainBarItemInformation();
            self::$instances[StaticMainMenuProvider::PURPOSE_MAINBAR] = new MainMenuMainCollector($providers, $information);
        }

        return self::$instances[StaticMainMenuProvider::PURPOSE_MAINBAR];
    }


    /**
     * @return MetaBarMainCollector
     */
    public function metaBar() : MetaBarMainCollector
    {
        if (!isset(self::$instances[StaticMetaBarProvider::PURPOSE_MBS])) {
            self::$instances[StaticMetaBarProvider::PURPOSE_MBS] = new MetaBarMainCollector($this->provider_factory->getMetaBarProvider());
        }

        return self::$instances[StaticMetaBarProvider::PURPOSE_MBS];
    }


    /**
     * @return MainToolCollector
     */
    public function tool() : MainToolCollector
    {
        if (!isset(self::$instances[DynamicToolProvider::PURPOSE_TOOLS])) {
            self::$instances[DynamicToolProvider::PURPOSE_TOOLS] = new MainToolCollector($this->provider_factory->getToolProvider());
        }

        return self::$instances[DynamicToolProvider::PURPOSE_TOOLS];
    }
}
