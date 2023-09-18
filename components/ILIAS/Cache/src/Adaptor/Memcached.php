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

namespace ILIAS\Cache\Adaptor;

use ILIAS\Cache\Config;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Memcached extends BaseAdaptor implements Adaptor
{
    private const PERSISTENT_ID = 'ilias_cache';
    private ?\Memcached $server = null;

    public function __construct(Config $config)
    {
        parent::__construct($config);
        if (!class_exists(\Memcached::class)) {
            return;
        }
        if ($config->getNodes() === []) {
            return;
        }
        $this->initServer($config);
    }


    public function isAvailable(): bool
    {
        return class_exists(\Memcached::class) && $this->server !== null && $this->server->getVersion() !== false;
    }

    public function has(string $container, string $key): bool
    {
        return $this->server->get($this->buildKey($container, $key)) !== false;
    }

    public function get(string $container, string $key): ?string
    {
        return $this->server->get($this->buildKey($container, $key)) ?: null;
    }

    public function set(string $container, string $key, string $value, int $ttl): void
    {
        $this->server->set($this->buildKey($container, $key), $value);
    }

    public function delete(string $container, string $key): void
    {
        $this->server->delete($this->buildKey($container, $key), 0);
    }

    public function flushContainer(string $container): void
    {
        $prefix = $this->buildContainerPrefix($container);
        foreach ($this->server->getAllKeys() as $key) {
            if (str_starts_with($key, $prefix)) {
                $this->server->set($key, false, 0);
            }
        }
        foreach ($this->server->getAllKeys() as $key) {
            if (str_starts_with($key, $prefix)) {
                $this->server->get(
                    $key
                ); // invalidates the key, see https://www.php.net/manual/en/memcached.expiration.php
            }
        }
        $this->server->flushBuffers();
        // $this->lock(1);// see https://github.com/memcached/memcached/issues/307
    }

    public function flush(): void
    {
        $this->server->flush();
    }

    protected function initServer(Config $config): void
    {
        $this->server = new \Memcached(self::PERSISTENT_ID);
        $nodes = $config->getNodes();
        foreach ($nodes as $node) {
            $this->server->addServer(
                $node->getHost(),
                $node->getPort(),
                $node->getWeight() ?? 100 / count($nodes)
            );
        }
    }
}
