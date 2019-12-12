<?php

require_once('./Services/GlobalCache/classes/class.ilGlobalCacheService.php');
require_once('class.ilMemcacheServer.php');

/**
 * Class ilMemcache
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilMemcache extends ilGlobalCacheService
{

    /**
     * @var Memcached
     */
    protected static $memcache_object;


    /**
     * @param $service_id
     * @param $component
     */
    public function __construct($service_id, $component)
    {
        if (!(self::$memcache_object instanceof Memcached) and $this->getInstallable()) {
            /**
             * @var $ilMemcacheServer ilMemcacheServer
             */
            $memcached = new Memcached();

            if (ilMemcacheServer::count() > 0) {
                $memcached->resetServerList();
                $servers = array();
                $list = ilMemcacheServer::where(array( 'status' => ilMemcacheServer::STATUS_ACTIVE ))
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


    /**
     * @return Memcached
     */
    protected function getMemcacheObject()
    {
        return self::$memcache_object;
    }


    /**
     * @param $key
     *
     * @return bool
     */
    public function exists($key)
    {
        return $this->getMemcacheObject()->get($this->returnKey($key)) != null;
    }


    /**
     * @param          $key
     * @param          $serialized_value
     * @param null|int $ttl
     *
     * @return bool
     */
    public function set($key, $serialized_value, $ttl = null)
    {
        return $this->getMemcacheObject()
                    ->set($this->returnKey($key), $serialized_value, (int) $ttl);
    }


    /**
     * @param      $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return $this->getMemcacheObject()->get($this->returnKey($key));
    }


    /**
     * @param      $key
     *
     * @return bool
     */
    public function delete($key)
    {
        return $this->getMemcacheObject()->delete($this->returnKey($key));
    }


    /**
     * @return bool
     */
    public function flush()
    {
        return $this->getMemcacheObject()->flush();
    }


    /**
     * @return bool
     */
    protected function getActive()
    {
        if ($this->getInstallable()) {
            $stats = $this->getMemcacheObject()->getStats();

            if (!is_array($stats)) {
                return false;
            }

            foreach ($stats as $server) {
                if ($server['pid'] > 0) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }


    /**
     * @return bool
     */
    protected function getInstallable()
    {
        return class_exists('Memcached');
    }


    /**
     * @return string
     */
    public function getInstallationFailureReason()
    {
        if ($this->getMemcacheObject() instanceof Memcached) {
            $stats = $this->getMemcacheObject()->getStats();

            if (!$stats[self::STD_SERVER . ':' . self::STD_PORT]['pid'] > 0) {
                return 'No Memcached-Server available';
            }
        }

        return parent::getInstallationFailureReason();
    }


    /**
     * @param $value
     *
     * @return mixed
     */
    public function serialize($value)
    {
        return serialize($value);
    }


    /**
     * @param $serialized_value
     *
     * @return mixed
     */
    public function unserialize($serialized_value)
    {
        return unserialize($serialized_value);
    }


    /**
     * @return array
     */
    public function getInfo()
    {
        if (self::isInstallable()) {
            $return = array();
            $return['__cache_info'] = $this->getMemcacheObject()->getStats();
            foreach ($this->getMemcacheObject()->getAllKeys() as $key) {
                $return[$key] = $this->getMemcacheObject()->get($key);
            }

            return $return;
        }
    }


    /**
     * @inheritdoc
     */
    public function isValid($key)
    {
        return true;
    }
}
