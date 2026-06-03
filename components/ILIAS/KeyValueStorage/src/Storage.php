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

namespace ILIAS\KeyValueStorage;

/**
 * Persistent key-value storage scoped to a namespace within one backend.
 *
 * The operation shape follows common PHP key-value conventions (similar to PSR-16)
 * but this is intentionally not a cache interface: values represent application
 * state that must be retained until explicitly changed or cleared, not derived
 * data that may be discarded for performance.
 */
interface Storage
{
    /**
     * @throws \InvalidArgumentException if the key is invalid
     */
    public function has(string $key): bool;

    /**
     * @throws \InvalidArgumentException if the key is invalid
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * @throws \InvalidArgumentException if the key is invalid
     */
    public function set(string $key, mixed $value): void;

    /**
     * @throws \InvalidArgumentException if the key is invalid
     */
    public function delete(string $key): void;

    /**
     * Removes all entries belonging to this namespace.
     */
    public function clear(): void;
}
