<?php

declare(strict_types=1);

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
 * Class ilForumNotificationCache
 * @author Niels Theen <ntheen@databay.de>
 */
class ilForumNotificationCache
{
    /** @var array<string, mixed> */
    private array $storage = [];

    /**
     * @param string $id
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
     * @return void
     */
    public function store(string $key, $data): void
    {
        $this->storage[$key] = $data;
    }

    public function exists(string $id): bool
    {
        return array_key_exists($id, $this->storage);
    }

    /**
     * @param array $values
     * @return string An´ MD5 encoded key based on the given arrays
     */
    public function createKeyByValues(array $values): string
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
