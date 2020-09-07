<?php namespace ILIAS\GlobalScreen;

use ILIAS\GlobalScreen\Collector\CollectorFactory;
use ILIAS\GlobalScreen\Identification\IdentificationFactory;
use ILIAS\GlobalScreen\Provider\ProviderFactoryInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\MainMenuItemFactory;

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
    public function __construct(ProviderFactoryInterface $provider_factory)
    {
        $this->provider_factory = $provider_factory;
    }


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
    public function mainmenu() : MainMenuItemFactory
    {
        return $this->get(MainMenuItemFactory::class);
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
