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

namespace ILIAS\Cache;

use ILIAS\Cache\Adaptor\Adaptor;
use ILIAS\Cache\Adaptor\AvailableAdaptors;
use ILIAS\Cache\Adaptor\Factory;
use ILIAS\Cache\Container\ActiveContainer;
use ILIAS\Cache\Container\Request;
use ILIAS\Cache\Container\Container;
use ILIAS\Cache\Container\VoidContainer;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Services
{
    /**
     * @var ActiveContainer[]
     */
    private array $containers = [];
    private ?Adaptor $adaptor = null;
    private Factory $adaptor_factory;

    public function __construct(
        private ?Config $config = null,
    ) {
        if ($config === null) {
            $this->config = new Config(Config::PHPSTATIC, true);
        }
        $this->adaptor_factory = new Factory();
    }

    public function get(Request $for_container): Container
    {
        return $this->ensureContainer($for_container);
    }

    private function ensureContainer(Request $for_container): Container
    {
        if (!array_key_exists($for_container->getContainerKey(), $this->containers)) {
            $this->containers[$for_container->getContainerKey()] = $this->new($for_container);
        }
        return $this->containers[$for_container->getContainerKey()];
    }

    private function new(Request $for_container): Container
    {
        if (!$this->config->isActivated()) {
            return new VoidContainer($for_container);
        }

        if (
            !in_array($for_container->getContainerKey(), $this->config->getActivatedContainerKeys(), true)
            && !in_array(Config::ALL, $this->config->getActivatedContainerKeys(), true)
            && $for_container->isForced() === false
        ) {
            return new VoidContainer($for_container);
        }

        return new ActiveContainer(
            $for_container,
            $this->getAdaptor(),
            $this->config
        );
    }

    private function getAdaptor(): Adaptor
    {
        if ($this->adaptor === null) {
            $this->adaptor = $this->adaptor_factory->getWithConfig($this->config);
        }
        return $this->adaptor;
    }

    public function flushContainer(Request $container_request): void
    {
        $this->ensureContainer($container_request)->flush();
    }

    public function flushAdapter(): void
    {
        $this->getAdaptor()->flush();
    }
}
