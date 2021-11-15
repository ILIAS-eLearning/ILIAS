<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilForumNotificationCache
 * @author Niels Theen <ntheen@databay.de>
 */
class ilForumNotificationCache
{
    /** @var array<string, mixed> */
    private array $storage = [];

    public function fetch(string $id)
    {
        if (false === $this->exists($id)) {
            throw new InvalidArgumentException('Storage id doesn\'t exist');
        }

        return $this->storage[$id];
    }

    public function store(string $key, $data) : void
    {
        $this->storage[$key] = $data;
    }

    public function exists(string $id) : bool
    {
        return array_key_exists($id, $this->storage);
    }

    /**
     * @param array $values
     * @return string An´ MD5 encoded key based on the given arrays
     */
    public function createKeyByValues(array $values) : string
    {
        foreach ($values as &$value) {
            if ($value !== null && !is_scalar($value)) {
                throw new InvalidArgumentException(sprintf(
                    "Value %s is not scalar and can't be used to build a key",
                    print_r($value, true)
                ));
            }

            $value = (string) $value;
        }

        return md5(implode('|', $values));
    }
}
