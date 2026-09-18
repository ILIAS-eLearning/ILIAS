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

namespace ILIAS\Authentication\KeyValueStorage;

use ILIAS\KeyValueStorage\Store;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Storage as UiStorage;

/**
 * Adapts session-scoped key-value storage to the UI ArrayAccess contract.
 */
final readonly class UiStorageAdapter implements UiStorage
{
    private Transformation $as_stored;

    public function __construct(
        private Store $storage,
        Refinery $refinery
    ) {
        $this->as_stored = $refinery->identity();
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->storage->has($this->assertStringOffset($offset));
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->storage->get($this->assertStringOffset($offset), $this->as_stored);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->storage->set($this->assertStringOffset($offset), $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->storage->delete($this->assertStringOffset($offset));
    }

    private function assertStringOffset(mixed $offset): string
    {
        if (!\is_string($offset)) {
            throw new \InvalidArgumentException('Offset needs to be of type string.');
        }

        return $offset;
    }
}
