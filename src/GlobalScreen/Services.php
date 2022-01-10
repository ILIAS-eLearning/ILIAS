<?php /** @noinspection PhpIncompatibleReturnTypeInspection */

namespace ILIAS\GlobalScreen;

use ILIAS\GlobalScreen\Collector\CollectorFactory;
use ILIAS\GlobalScreen\Identification\IdentificationFactory;
use ILIAS\GlobalScreen\Provider\ProviderFactory;
use ILIAS\GlobalScreen\Scope\Layout\LayoutServices;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\MainMenuItemFactory;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\MetaBarItemFactory;
use ILIAS\GlobalScreen\Scope\Notification\NotificationServices;
use ILIAS\GlobalScreen\Scope\Tool\ToolServices;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class Services
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Services
{
    use SingletonTrait;
    
    private static ?Services $instance = null;
 
    private ProviderFactory $provider_factory;
    
    /**
     * Services constructor.
     * @param ProviderFactory $provider_factory
     */
    public function __construct(ProviderFactory $provider_factory)
    {
        $this->provider_factory = $provider_factory;
    }
    
    public static function getInstance(ProviderFactory $provider_factory): Services
    {
        if (!isset(self::$instance)) {
            self::$instance = new self($provider_factory);
        }
        
        return self::$instance;
    }
    
    /**
     * @return MainMenuItemFactory
     * @see MainMenuItemFactory
     */
    public function mainBar() : MainMenuItemFactory
    {
        return $this->get(MainMenuItemFactory::class);
    }
    
    /**
     * @return MetaBarItemFactory
     */
    public function metaBar() : MetaBarItemFactory
    {
        return $this->get(MetaBarItemFactory::class);
    }
    
    /**
     * @return ToolServices
     * @see ToolServices
     */
    public function tool() : ToolServices
    {
        return $this->get(ToolServices::class);
    }
    
    /**
     * @return LayoutServices
     */
    public function layout() : LayoutServices
    {
        return $this->get(LayoutServices::class);
    }
    
    /**
     * @return NotificationServices
     */
    public function notifications() : NotificationServices
    {
        return $this->get(NotificationServices::class);
    }
    
    /**
     * @return CollectorFactory
     */
    public function collector() : CollectorFactory
    {
        return $this->getWithArgument(CollectorFactory::class, $this->provider_factory);
    }
    
    /**
     * @return IdentificationFactory
     * @see IdentificationFactory
     */
    public function identification() : IdentificationFactory
    {
        return $this->getWithArgument(IdentificationFactory::class, $this->provider_factory);
    }
}
