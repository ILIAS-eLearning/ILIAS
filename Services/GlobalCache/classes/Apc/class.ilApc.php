<?php

require_once('./Services/GlobalCache/classes/class.ilGlobalCacheService.php');
require_once('./Services/Environment/classes/class.ilRuntime.php');

/**
 * Class ilApc
 *
 * @beta
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilApc extends ilGlobalCacheService
{
    const MIN_MEMORY = 16;
    const CACHE_ID = 'user';


    /**
     * @param $key
     *
     * @return bool
     */
    public function exists($key)
    {
        if (function_exists('apcu_exists')) {
            return apcu_exists($this->returnKey($key));
        } else {
            return apcu_fetch($this->returnKey($key));
        }
    }


    /**
     * @param     $key
     * @param     $serialized_value
     * @param int $ttl
     *
     * @return array|bool
     */
    public function set($key, $serialized_value, $ttl = 0)
    {
        return apcu_store($this->returnKey($key), $serialized_value, $ttl);
    }


    /**
     * @param $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return apcu_fetch($this->returnKey($key));
    }


    /**
     * @param $key
     *
     * @return bool|string[]
     */
    public function delete($key)
    {
        return apcu_delete($this->returnKey($key));
    }


    /**
     * @return bool
     */
    public function flush()
    {
        return apcu_clear_cache();
    }


    /**
     * @param $value
     *
     * @return mixed|string
     */
    public function serialize($value)
    {
        return ($value);
    }


    /**
     * @param $serialized_value
     *
     * @return mixed
     */
    public function unserialize($serialized_value)
    {
        return ($serialized_value);
    }


    /**
     * @return array
     */
    public function getInfo()
    {
        $return = array();

        $cache_info = apc_cache_info();

        unset($cache_info['cache_list']);
        unset($cache_info['slot_distribution']);

        $return['__cache_info'] = array(
            'apc.enabled'      => ini_get('apc.enabled'),
            'apc.shm_size'     => ini_get('apc.shm_size'),
            'apc.shm_segments' => ini_get('apc.shm_segments'),
            'apc.gc_ttl'       => ini_get('apc.gc_ttl'),
            'apc.user_ttl'     => ini_get('apc.ttl'),
            'info'             => $cache_info,
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


    protected function getActive()
    {
        return function_exists('apcu_store');
    }


    /**
     * @return bool
     */
    protected function getInstallable()
    {
        return function_exists('apcu_store');
    }


    /**
     * @return string
     */
    protected function getMemoryLimit()
    {
        if (ilRuntime::getInstance()->isHHVM()) {
            return $this->getMinMemory() . 'M';
        }

        return ini_get('apc.shm_size');
    }


    /**
     * @inheritDoc
     */
    protected function getMinMemory()
    {
        return self::MIN_MEMORY;
    }


    /**
     * @inheritdoc
     */
    public function isValid($key)
    {
        return true;
    }
}
