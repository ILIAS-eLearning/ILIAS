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

use ILIAS\Authentication\KeyValueStorage\SessionRepository;
use ILIAS\Authentication\KeyValueStorage\UiStorageAdapter;
use ILIAS\KeyValueStorage\Internal\KeyRules;
use ILIAS\KeyValueStorage\Internal\NamespacedStore;
use ILIAS\KeyValueStorage\Internal\Values;
use ILIAS\KeyValueStorage\Internal\StorageNamespace;
use ILIAS\UI\Implementation\Component\Navigation\Sequence\Sequence;
use ILIAS\UI\Implementation\Component\Table\Data;
use ILIAS\UI\Implementation\Component\Table\Ordering;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[BackupGlobals(true)]
class UiStorageAdapterTest extends TestCase
{
    private UiStorageAdapter $adapter;

    protected function setUp(): void
    {
        $_SESSION = [];
        $this->adapter = new UiStorageAdapter(new NamespacedStore(
            new StorageNamespace(['ui', 'storage']),
            new SessionRepository(),
            new KeyRules(),
            new Values()
        ));
    }

    public function testAViewStateSurvivesAWriteAndReadCycle(): void
    {
        $this->assertFalse($this->adapter->offsetExists('view_state'));

        $this->adapter->offsetSet('view_state', ['sort' => 'title', 'page' => 2]);

        $this->assertTrue($this->adapter->offsetExists('view_state'));
        $this->assertSame(['sort' => 'title', 'page' => 2], $this->adapter->offsetGet('view_state'));
    }

    public function testUnsetRemovesTheEntry(): void
    {
        $this->adapter->offsetSet('view_state', ['page' => 2]);

        $this->adapter->offsetUnset('view_state');

        $this->assertFalse($this->adapter->offsetExists('view_state'));
        $this->assertNull($this->adapter->offsetGet('view_state'));
    }

    /**
     * The UI builds its storage ids from class names, so the storage has to
     * accept backslashes.
     */
    #[DataProvider('storageIdsUsedByTheUi')]
    public function testTheStorageIdsOfTheUiAreAccepted(string $storage_id): void
    {
        $this->adapter->offsetSet($storage_id, ['sort' => 'title']);

        $this->assertSame(['sort' => 'title'], $this->adapter->offsetGet($storage_id));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function storageIdsUsedByTheUi(): array
    {
        return [
            'data table' => [Data::STORAGE_ID_PREFIX . 'my_table'],
            'ordering table' => [Ordering::STORAGE_ID_PREFIX . 'my_table'],
            'sequence' => [Sequence::STORAGE_ID_PREFIX . 'my_sequence'],
        ];
    }

    public function testANonStringOffsetIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Offset needs to be of type string.');

        $this->adapter->offsetExists(42);
    }
}
