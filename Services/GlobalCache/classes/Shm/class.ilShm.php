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
 * Class ilShm
 * @beta http://php.net/manual/en/shmop.examples-basic.php
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilShm extends ilGlobalCacheService
{
    protected static int $shm_id;
    protected static int $block_size = 0;

    /**
     * @description set self::$active
     */
    protected function getActive() : bool
    {
        return function_exists('shmop_open');
    }

    /**
     * @description set self::$installable
     */
    protected function getInstallable() : bool
    {
        return false;
    }

    /**
     * ilShm constructor.
     */
    public function __construct(string $service_id, string $component)
    {
        parent::__construct($service_id, $component);
        self::$shm_id = shmop_open(0xff3, "c", 0644, 100);
        self::$block_size = shmop_size(self::$shm_id);
    }

    public function exists(string $key) : bool
    {
        return shm_has_var(self::$shm_id, $key);
    }


    public function set(string $key, $serialized_value, int $ttl = null) : bool
    {
        return (bool) shmop_write(self::$shm_id, $key, $serialized_value);
    }


    public function get(string $key)
    {
        return shmop_read(self::$shm_id, 0, self::$block_size);
    }

    public function delete(string $key) : bool
    {
        return shm_remove_var(self::$shm_id, $key);
    }

    public function flush(bool $complete = false) : bool
    {
        // currently a partial flushing is missing
        shmop_delete(self::$shm_id);

        return true;
    }

    /**
     * @param mixed $value
     */
    public function serialize($value) : string
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
}
