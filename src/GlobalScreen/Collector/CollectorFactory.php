<?php namespace ILIAS\GlobalScreen\Collector;

use ILIAS\GlobalScreen\Provider\ProviderFactory;
use ILIAS\GlobalScreen\Scope\Layout\Collector\MainLayoutCollector;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\MainMenuMainCollector;
use ILIAS\GlobalScreen\Scope\MetaBar\Collector\MetaBarMainCollector;
use ILIAS\GlobalScreen\Scope\Notification\Collector\MainNotificationCollector;
use ILIAS\GlobalScreen\Scope\Tool\Collector\MainToolCollector;
use ILIAS\GlobalScreen\SingletonTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\MainMenuItemFactory;

/**
 * Class CollectorFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class CollectorFactory
{
    use SingletonTrait;
    /**
     * @var array
     */
    protected static $instances = [];
    /**
     * @var ProviderFactory
     */
    private $provider_factory;


    /**
     * CollectorFactory constructor.
     *
     * @param ProviderFactory $provider_factory
     */
    public function __construct(ProviderFactory $provider_factory)
    {
        $this->provider_factory = $provider_factory;
    }


    /**
     * @return MainMenuMainCollector
     * @throws \Throwable
     */
    public function mainmenu() : MainMenuMainCollector
    {
        if (!$this->has(MainMenuMainCollector::class)) {
            $providers = $this->provider_factory->getMainBarProvider();
            $information = $this->provider_factory->getMainBarItemInformation();
    
            return $this->getWithMultipleArguments(
                MainMenuMainCollector::class,
                [
                    $providers,
                    new MainMenuItemFactory(),
                    $information
                ]
            );
        }

        return $this->get(MainMenuMainCollector::class);
    }


    /**
     * @return MetaBarMainCollector
     */
    public function metaBar() : MetaBarMainCollector
    {
        return $this->getWithArgument(MetaBarMainCollector::class, $this->provider_factory->getMetaBarProvider());
    }


    /**
     * @return MainToolCollector
     * @throws \ReflectionException
     */
    public function tool() : MainToolCollector
    {
        if (!$this->has(MainToolCollector::class)) {
            $providers = $this->provider_factory->getToolProvider();
            $information = $this->provider_factory->getMainBarItemInformation();

            return $this->getWithMultipleArguments(MainToolCollector::class, [$providers, $information]);
        }

        return $this->get(MainToolCollector::class);
    }


    /**
     * @return MainLayoutCollector
     * @throws \ReflectionException
     */
    public function layout() : MainLayoutCollector
    {
        return $this->getWithMultipleArguments(MainLayoutCollector::class, [$this->provider_factory->getModificationProvider()]);
    }


    /**
     * @return MainNotificationCollector
     */
    public function notifications() : MainNotificationCollector
    {
        return $this->getWithArgument(MainNotificationCollector::class, $this->provider_factory->getNotificationsProvider());
    }
}
