<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilForumNotificationCache
 *
 * @author Niels Theen <ntheen@databay.de>
 */
class ilForumNotificationCache
{
    /** @var array */
    private $storage = array();

    /**
     * @param string $id - id to access the cache.
     *                  SHOULD be md5 encoded
     * @return mixed
     */
    public function fetch(string $id)
    {
        if (false === $this->exists($id)) {
            throw new InvalidArgumentException('Storage id doesn\'t exist');
        }

        return $this->storage[$id];
    }

    /**
     * @param string $key
     * @param mixed $data
     */
    public function store(string $key, $data)
    {
        $this->storage[$key] = $data;
    }

    /**
     * Checks if the current id exists
     *
     * @param string $id
     * @return bool
     */
    public function exists(string $id)
    {
        return array_key_exists($id, $this->storage);
    }

    /**
     * @param array $values
     * @return string - MD5 encoded key based on the
     *                  given arrays
     */
    public function createKeyByValues(array $values)
    {
        $cacheKey = md5(implode('|', $values));

        return $cacheKey;
    }
}
