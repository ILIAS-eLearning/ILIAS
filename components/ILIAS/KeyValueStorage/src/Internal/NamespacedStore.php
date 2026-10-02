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
 * session or the database twice. Reading the keys of the namespace or several
 * keys at once remembers every key of it. This is not a cross-request cache -
 * use ILIAS\Cache for that.
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

    /**
     * True once every entry of the namespace has been read. Later lookups of
     * keys this request has not touched can then be answered without the backend.
     */
    private bool $namespace_loaded = false;

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

        // reads the value along, since consumers like `$storage[$key] ?? null`
        // ask has() right before get().
        return $this->readDecoded($key)[0];
    }

    public function get(string $key, Transformation $transformation): mixed
    {
        $this->key_rules->check($key);

        [$is_present, $value] = $this->readDecoded($key);

        return $transformation->transform($is_present ? $value : null);
    }

    public function getMany(array $transformations): array
    {
        $requested = [];
        foreach ($transformations as $key => $transformation) {
            $key = (string) $key;
            if (!$transformation instanceof Transformation) {
                throw new \InvalidArgumentException(
                    'Each getMany() entry must be a ' . Transformation::class . ', got '
                    . \get_debug_type($transformation) . ' for key "' . $key . '".'
                );
            }
            $this->key_rules->check($key);
            $requested[$key] = $transformation;
        }

        if ($this->mustLoadNamespaceFor($requested)) {
            $this->loadNamespace();
        }

        $entries = [];
        foreach ($requested as $key => $transformation) {
            $key = (string) $key;
            [$is_present, $value] = $this->readDecoded($key);
            $entries[$key] = $transformation->transform($is_present ? $value : null);
        }

        return $entries;
    }

    public function keys(): array
    {
        $this->loadNamespace();

        $keys = [];
        foreach ($this->seen as $key => [$is_present]) {
            if ($is_present) {
                $keys[] = (string) $key;
            }
        }

        \sort($keys, \SORT_STRING);

        return $keys;
    }

    public function set(string $key, mixed $value): void
    {
        $this->key_rules->check($key);

        $encoded = $this->values->encode($value);
        // the decoded form is remembered, so that reading a value back within
        // this request yields exactly what a later request would read.
        $decoded = $this->values->decode($encoded);

        // consumers like the UI tables store their state on every rendering, which
        // must not become a write per page view when nothing changed.
        if (($this->seen[$key] ?? null) === [true, $decoded]) {
            return;
        }

        $this->repository->write($this->namespace, $key, $encoded);
        $this->seen[$key] = [true, $decoded];
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
        $this->namespace_loaded = false;
    }

    /**
     * @return array{bool, mixed}
     */
    private function readDecoded(string $key): array
    {
        if (!isset($this->seen[$key])) {
            if ($this->namespace_loaded) {
                $this->seen[$key] = [false, null];
            } else {
                $stored = $this->repository->read($this->namespace, $key);
                $this->seen[$key] = $stored === null
                    ? [false, null]
                    : [true, $this->values->decode($stored)];
            }
        }

        return $this->seen[$key];
    }

    /**
     * @param array<string, Transformation> $requested
     */
    private function mustLoadNamespaceFor(array $requested): bool
    {
        if ($requested === [] || $this->namespace_loaded) {
            return false;
        }

        foreach ($requested as $key => $_) {
            if (!isset($this->seen[(string) $key])) {
                return true;
            }
        }

        return false;
    }

    private function loadNamespace(): void
    {
        if ($this->namespace_loaded) {
            return;
        }

        $decoded = [];
        foreach ($this->repository->readAll($this->namespace) as $key => $stored) {
            $key = (string) $key;
            if (isset($this->seen[$key])) {
                continue;
            }

            $decoded[$key] = [true, $this->values->decode($stored)];
        }

        $this->seen += $decoded;
        $this->namespace_loaded = true;
    }
}
