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
 * Class ilApc
 * @beta
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilApc extends ilGlobalCacheService implements ilGlobalCacheServiceInterface
{
    /**
     * @var int
     */
    private const MIN_MEMORY = 16;

    public function exists(string $key): bool
    {
        if (function_exists('apcu_exists')) {
            return apcu_exists($this->returnKey($key));
        }
        return (bool) apcu_fetch($this->returnKey($key));
    }

    public function set(string $key, $serialized_value, int $ttl = null): bool
    {
        return apcu_store($this->returnKey($key), $serialized_value, $ttl);
    }

    /**
     * @return mixed
     */
    public function get(string $key)
    {
        return apcu_fetch($this->returnKey($key));
    }

    /**
     * @return bool|string[]
     */
    public function delete(string $key): bool
    {
        return apcu_delete($this->returnKey($key));
    }

    public function flush(bool $complete = false): bool
    {
        // incomplete flushing is not supported by APCu, an own implementation coused issues like https://mantis.ilias.de/view.php?id=28201
        return function_exists('apcu_clear_cache') && apcu_clear_cache();
    }

    /**
     * @param mixed $value
     * @return mixed|string
     */
    public function serialize($value)
    {
        return ($value);
    }

    /**
     * @param mixed $serialized_value
     * @return mixed
     */
    public function unserialize($serialized_value)
    {
        return ($serialized_value);
    }

    public function getInfo(): array
    {
        $return = array();

        $cache_info = apc_cache_info();

        unset($cache_info['cache_list']);
        unset($cache_info['slot_distribution']);

        $return['__cache_info'] = array(
            'apc.enabled' => ini_get('apc.enabled'),
            'apc.shm_size' => ini_get('apc.shm_size'),
            'apc.shm_segments' => ini_get('apc.shm_segments'),
            'apc.gc_ttl' => ini_get('apc.gc_ttl'),
            'apc.user_ttl' => ini_get('apc.ttl'),
            'info' => $cache_info,
        );

        $cache_info = apc_cache_info();
        foreach ($cache_info['cache_list'] as $dat) {
            $key = $dat['key'];

            if (preg_match('/' . $this->getServiceId() . '_' . $this->getComponent() . '/', $key)) {
                $return[$key] = apc_fetch($key);
            }
        }

        return $return;
    }

    protected function getActive(): bool
    {
        return function_exists('apcu_store');
    }

    protected function getInstallable(): bool
    {
        return function_exists('apcu_store');
    }

    protected function getMemoryLimit(): string
    {
        if (ilRuntime::getInstance()->isHHVM()) {
            return $this->getMinMemory() . 'M';
        }

        return ini_get('apc.shm_size');
    }

    /**
     * @inheritDoc
     */
    protected function getMinMemory(): int
    {
        return self::MIN_MEMORY;
    }

    /**
     * @inheritdoc
     */
    public function isValid(string $key): bool
    {
        return true;
    }
}
