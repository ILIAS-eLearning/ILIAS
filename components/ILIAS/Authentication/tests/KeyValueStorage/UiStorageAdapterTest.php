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

namespace ILIAS\Tests\Authentication\KeyValueStorage;

use ILIAS\Authentication\KeyValueStorage\UiStorageAdapter;
use ILIAS\KeyValueStorage\Storage;
use PHPUnit\Framework\TestCase;

class UiStorageAdapterTest extends TestCase
{
    private RecordingStorage $storage;
    private UiStorageAdapter $adapter;

    protected function setUp(): void
    {
        $this->storage = new RecordingStorage();
        $this->adapter = new UiStorageAdapter($this->storage);
    }

    public function testOffsetExistsDelegatesToHas(): void
    {
        $this->storage->has_result = true;

        self::assertTrue($this->adapter->offsetExists('view_state'));
        self::assertSame('view_state', $this->storage->has_key);
    }

    public function testOffsetGetReturnsStoredValue(): void
    {
        $this->storage->get_result = ['sort' => 'title'];

        self::assertSame(['sort' => 'title'], $this->adapter->offsetGet('view_state'));
        self::assertSame('view_state', $this->storage->get_key);
    }

    public function testOffsetSetWritesKeyAndValue(): void
    {
        $this->adapter->offsetSet('view_state', ['page' => 2]);

        self::assertSame('view_state', $this->storage->set_key);
        self::assertSame(['page' => 2], $this->storage->set_value);
    }

    public function testOffsetUnsetDeletesKey(): void
    {
        $this->adapter->offsetUnset('view_state');

        self::assertSame('view_state', $this->storage->deleted_key);
    }

    public function testOffsetExistsRejectsNonStringOffset(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Offset needs to be of type string.');

        $this->adapter->offsetExists(42);
    }
}

final class RecordingStorage implements Storage
{
    public bool $has_result = false;
    public mixed $get_result = null;
    public ?string $has_key = null;
    public ?string $get_key = null;
    public ?string $set_key = null;
    public mixed $set_value = null;
    public ?string $deleted_key = null;

    public function has(string $key): bool
    {
        $this->has_key = $key;

        return $this->has_result;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->get_key = $key;

        return $this->get_result;
    }

    public function set(string $key, mixed $value): void
    {
        $this->set_key = $key;
        $this->set_value = $value;
    }

    public function delete(string $key): void
    {
        $this->deleted_key = $key;
    }

    public function clear(): void
    {
    }
}
