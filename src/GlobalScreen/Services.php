<?php namespace ILIAS\GlobalScreen;

use ILIAS\GlobalScreen\Collector\CollectorFactory;
use ILIAS\GlobalScreen\Identification\IdentificationFactory;
use ILIAS\GlobalScreen\Provider\ProviderFactoryInterface;
use ILIAS\GlobalScreen\Scope\Layout\LayoutServices;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\MainMenuItemFactory;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\MetaBarItemFactory;
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
     * @var ProviderFactoryInterface
     */
    private $provider_factory;


    /**
     * Services constructor.
     *
     * @param ProviderFactoryInterface $provider_factory
     */
    public function __construct(ProviderFactoryInterface $provider_factory) { $this->provider_factory = $provider_factory; }


    /**
     * @param ProviderFactoryInterface $provider_factory
     *
     * @return Services
     */
    public static function getInstance(ProviderFactoryInterface $provider_factory)
    {
        if (!isset(self::$instance)) {
            self::$instance = new self($provider_factory);
        }

        return self::$instance;
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
        return $this->get(LayoutServices::class);
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
