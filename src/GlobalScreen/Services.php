<?php namespace ILIAS\GlobalScreen;

use ILIAS\GlobalScreen\Collector\CollectorFactory;
use ILIAS\GlobalScreen\Identification\IdentificationFactory;
use ILIAS\GlobalScreen\Provider\ProviderFactory;
use ILIAS\GlobalScreen\Scope\Layout\LayoutServices;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\MainMenuItemFactory;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\MetaBarItemFactory;
use ILIAS\GlobalScreen\Scope\Notification\NotificationServices;
use ILIAS\GlobalScreen\Scope\Tool\ToolServices;

/**
 * Class Services
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Services
{
    use SingletonTrait;
    /**
     * @var Services
     */
    private static $instance = null;
    /**
     * @var ProviderFactory
     */
    private $provider_factory;
    /**
     * @var string
     */
    public $resource_version = '';

    /**
     * Services constructor.
     *
     * @param ProviderFactory $provider_factory
     */
    public function __construct(ProviderFactory $provider_factory, string $resource_version = '')
    {
        $this->provider_factory = $provider_factory;
        $this->resource_version = $resource_version;
    }


    /**
     * @return MainMenuItemFactory
     * @see MainMenuItemFactory
     *
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
        return $this->getWithArgument(LayoutServices::class, $this->resource_version);
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
     *
     */
    public function identification() : IdentificationFactory
    {
        return $this->getWithArgument(IdentificationFactory::class, $this->provider_factory);
    }
}
