<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);
/** @noinspection PhpIncompatibleReturnTypeInspection */

namespace ILIAS\GlobalScreen;

use ILIAS\GlobalScreen\Collector\CollectorFactory;
use ILIAS\GlobalScreen\Identification\IdentificationFactory;
use ILIAS\GlobalScreen\Provider\ProviderFactory;
use ILIAS\GlobalScreen\Scope\Layout\LayoutServices;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\MainMenuItemFactory;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\MetaBarItemFactory;
use ILIAS\GlobalScreen\Scope\Notification\NotificationServices;
use ILIAS\GlobalScreen\Scope\Tool\ToolServices;
use ILIAS\DI\UIServices;

/**
 * Class Services
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Services
{
    use SingletonTrait;

    /**
     * @var \ILIAS\GlobalScreen\Services|null
     */
    private static $instance;

    /**
     * @var \ILIAS\GlobalScreen\Provider\ProviderFactory
     */
    private $provider_factory;

    /**
     * @var string
     */
    public $resource_version = '';

    /**
     * Services constructor.
     * @param ProviderFactory $provider_factory
     * @param string          $resource_version
     */
    public function __construct(
        ProviderFactory $provider_factory,
        string $resource_version = ''
    ) {
        global $DIC;
        $this->provider_factory = $provider_factory;
        $this->resource_version = urlencode($resource_version);
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
     */
    public function identification() : IdentificationFactory
    {
        return $this->getWithArgument(IdentificationFactory::class, $this->provider_factory);
    }
}
