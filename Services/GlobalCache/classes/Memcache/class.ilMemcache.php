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

/**
 * Class ilMemcache
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilMemcache extends ilGlobalCacheService
{
    protected static ?\Memcached $memcache_object = null;

    /**
     * ilMemcache constructor.
     */
    public function __construct(string $service_id, string $component)
    {
        if (!(self::$memcache_object instanceof Memcached) && $this->getInstallable()) {
            /**
             * @var $ilMemcacheServer ilMemcacheServer
             */
            $memcached = new Memcached();

            if (ilMemcacheServer::count() > 0) {
                $memcached->resetServerList();
                $servers = [];
                $list = ilMemcacheServer::where(array('status' => ilMemcacheServer::STATUS_ACTIVE))
                                           ->get();
                foreach ($list as $ilMemcacheServer) {
                    $servers[] = array(
                        $ilMemcacheServer->getHost(),
                        $ilMemcacheServer->getPort(),
                        $ilMemcacheServer->getWeight(),
                    );
                }
                $memcached->addServers($servers);
            }

            self::$memcache_object = $memcached;
        }
        parent::__construct($service_id, $component);
    }

    protected function getMemcacheObject(): ?\Memcached
    {
        return self::$memcache_object;
    }

    public function exists(string $key): bool
    {
        return $this->getMemcacheObject()->get($this->returnKey($key)) !== null;
    }

    public function set(string $key, $serialized_value, int $ttl = null): bool
    {
        return $this->getMemcacheObject()
                    ->set($this->returnKey($key), $serialized_value, (int) $ttl);
    }

    /**
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->getMemcacheObject()->get($this->returnKey($key));
    }

    public function delete(string $key): bool
    {
        return $this->getMemcacheObject()->delete($this->returnKey($key));
    }

    public function flush(bool $complete = false): bool
    {
        return $this->getMemcacheObject()->flush();
    }

    protected function getActive(): bool
    {
        if ($this->getInstallable()) {
            $stats = $this->getMemcacheObject()->getStats();

            if (!is_array($stats)) {
                return false;
            }

            foreach ($stats as $server) {
                if ((int) $server['pid'] > 1) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }

    protected function getInstallable(): bool
    {
        return class_exists('Memcached');
    }

    public function getInstallationFailureReason(): string
    {
        $stats = $this->getMemcacheObject()->getStats();
        $server_available = false;
        foreach ($stats as $server) {
            if ($server['pid'] > 0) {
                $server_available = true;
            }
        }
        if (!$server_available) {
            return 'No Memcached-Server available';
        }
        return parent::getInstallationFailureReason();
    }

    /**
     * @param mixed $value
     */
    public function serialize($value): string
    {
        return serialize($value);
    }

    /**
     * @param mixed $serialized_value
     * @return mixed
     */
    public function unserialize($serialized_value)
    {
        return unserialize($serialized_value);
    }

    public function getInfo(): array
    {
        $return = [];
        if ($this->isInstallable()) {
            $return['__cache_info'] = $this->getMemcacheObject()->getStats();
            foreach ($this->getMemcacheObject()->getAllKeys() as $key) {
                $return[$key] = $this->getMemcacheObject()->get($key);
            }
        }
        return $return;
    }

    public function isValid(string $key): bool
    {
        return true;
    }
}
