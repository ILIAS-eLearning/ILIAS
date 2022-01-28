<?php /** @noinspection PhpIncompatibleReturnTypeInspection */

namespace ILIAS\GlobalScreen\Collector;

use ILIAS\GlobalScreen\Provider\ProviderFactory;
use ILIAS\GlobalScreen\Scope\Layout\Collector\MainLayoutCollector;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\MainMenuMainCollector;
use ILIAS\GlobalScreen\Scope\MetaBar\Collector\MetaBarMainCollector;
use ILIAS\GlobalScreen\Scope\Notification\Collector\MainNotificationCollector;
use ILIAS\GlobalScreen\Scope\Tool\Collector\MainToolCollector;
use ILIAS\GlobalScreen\SingletonTrait;

/******************************************************************************
 * This file is part of ILIAS, a powerful learning management system.
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *****************************************************************************/

/**
 * Class CollectorFactory
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class CollectorFactory
{
    use SingletonTrait;
    
    protected static array $instances = [];
    private ProviderFactory $provider_factory;
    
    /**
     * CollectorFactory constructor.
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
            
            return $this->getWithMultipleArguments(MainMenuMainCollector::class, [$providers, $information]);
        }
        
        return $this->get(MainMenuMainCollector::class);
    }
    
    public function metaBar() : MetaBarMainCollector
    {
        return $this->getWithArgument(MetaBarMainCollector::class, $this->provider_factory->getMetaBarProvider());
    }
    
    public function tool() : MainToolCollector
    {
        if (!$this->has(MainToolCollector::class)) {
            $providers = $this->provider_factory->getToolProvider();
            $information = $this->provider_factory->getMainBarItemInformation();
            
            return $this->getWithMultipleArguments(MainToolCollector::class, [$providers, $information]);
        }
        
        return $this->get(MainToolCollector::class);
    }
    
    public function layout() : MainLayoutCollector
    {
        return $this->getWithMultipleArguments(MainLayoutCollector::class, [$this->provider_factory->getModificationProvider()]);
    }
    
    public function notifications() : MainNotificationCollector
    {
        return $this->getWithArgument(MainNotificationCollector::class, $this->provider_factory->getNotificationsProvider());
    }
}
