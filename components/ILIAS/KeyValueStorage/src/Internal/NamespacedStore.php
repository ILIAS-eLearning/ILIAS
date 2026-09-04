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

namespace ILIAS\KeyValueStorage\Internal;

use ILIAS\KeyValueStorage\Repository;
use ILIAS\KeyValueStorage\Store;
use ILIAS\Refinery\Transformation;

/**
 * A store bound to one namespace of one repository.
 *
 * Validates the keys, encodes the values and remembers what it has already seen
 * during this request, so that reading the same key twice does not hit the
 * session or the database twice. This is not a cross-request cache - use
 * ILIAS\Cache for that.
 *
 * @internal
 */
final class NamespacedStore implements Store
{
    /**
     * Everything this store has already read or written during this request.
     *
     * @var array<string, array{bool, mixed}> key => [is present, decoded value]
     */
    private array $seen = [];

    public function __construct(
        private readonly StorageNamespace $namespace,
        private readonly Repository $repository,
        private readonly KeyRules $key_rules,
        private readonly Values $values
    ) {
    }

    public function has(string $key): bool
    {
        $this->key_rules->check($key);

        if (isset($this->seen[$key])) {
            return $this->seen[$key][0];
        }

        return $this->repository->has($this->namespace, $key);
    }

    public function get(string $key, Transformation $transformation): mixed
    {
        $this->key_rules->check($key);

        [$is_present, $value] = $this->readDecoded($key);

        return $transformation->transform($is_present ? $value : null);
    }

    public function set(string $key, mixed $value): void
    {
        $this->key_rules->check($key);

        $encoded = $this->values->encode($value);
        $this->repository->write($this->namespace, $key, $encoded);

        // the decoded form is remembered, so that reading a value back within
        // this request yields exactly what a later request would read.
        $this->seen[$key] = [true, $this->values->decode($encoded)];
    }

    public function delete(string $key): void
    {
        $this->key_rules->check($key);

        $this->repository->remove($this->namespace, $key);
        $this->seen[$key] = [false, null];
    }

    public function clear(): void
    {
        $this->repository->removeAll($this->namespace);
        $this->seen = [];
    }

    /**
     * @return array{bool, mixed}
     */
    private function readDecoded(string $key): array
    {
        if (!isset($this->seen[$key])) {
            $stored = $this->repository->read($this->namespace, $key);
            $this->seen[$key] = $stored === null
                ? [false, null]
                : [true, $this->values->decode($stored)];
        }

        return $this->seen[$key];
    }
}
