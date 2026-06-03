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

namespace ILIAS\KeyValueStorage\Implementation;

use ILIAS\KeyValueStorage\KeyValidator;
use ILIAS\KeyValueStorage\Storage;
use ILIAS\KeyValueStorage\StorageNamespace;
use ILIAS\KeyValueStorage\StoragePort;
use ILIAS\KeyValueStorage\ValueCodec;

/**
 * Namespace-scoped storage delegating to a backend port.
 */
final readonly class NamespacedStorage implements Storage
{
    public function __construct(
        private StorageNamespace $namespace,
        private StoragePort $port,
        private KeyValidator $key_validator,
        private ValueCodec $value_codec
    ) {
    }

    public function has(string $key): bool
    {
        $this->key_validator->validate($key);

        return $this->port->has($this->namespace, $key);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->key_validator->validate($key);

        $encoded = $this->port->read($this->namespace, $key);
        if ($encoded === null) {
            return $default;
        }

        return $this->value_codec->decode($encoded);
    }

    public function set(string $key, mixed $value): void
    {
        $this->key_validator->validate($key);

        $this->port->write($this->namespace, $key, $this->value_codec->encode($value));
    }

    public function delete(string $key): void
    {
        $this->key_validator->validate($key);

        $this->port->remove($this->namespace, $key);
    }

    public function clear(): void
    {
        $this->port->clearNamespace($this->namespace);
    }
}
