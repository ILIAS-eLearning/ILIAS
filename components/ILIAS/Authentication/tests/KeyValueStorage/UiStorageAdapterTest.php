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

use ILIAS\Authentication\Domain\AuthenticatedSubjectResolver;
use ILIAS\Authentication\KeyValueStorage\SessionRepository;
use ILIAS\Authentication\KeyValueStorage\UiStorageAdapter;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\KeyValueStorage\Internal\KeyRules;
use ILIAS\KeyValueStorage\Internal\NamespacedStore;
use ILIAS\KeyValueStorage\Internal\StorageNamespace;
use ILIAS\KeyValueStorage\Internal\Values;
use ILIAS\KeyValueStorage\Services;
use ILIAS\KeyValueStorage\Store;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
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

    private Refinery $refinery;

    protected function setUp(): void
    {
        $_SESSION = [];
        $language = $this->getMockBuilder(\ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->refinery = new Refinery(new DataFactory(), $language);
        $store = new NamespacedStore(
            new StorageNamespace(['ui', 'storage']),
            new SessionRepository(),
            new KeyRules(),
            new Values($this->refinery)
        );
        $subjects = $this->createStub(AuthenticatedSubjectResolver::class);
        $subjects->method('supportsPersistentStorage')->willReturn(false);
        $services = $this->createMock(Services::class);
        $services->method('session')->willReturn($store);
        $services->expects($this->never())->method('persistentFor');
        $this->adapter = new UiStorageAdapter($subjects, $services, $this->refinery);
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

    public function testAFullyAuthenticatedUserUsesThePersistentSubjectStore(): void
    {
        $store = $this->createMock(Store::class);
        $store->expects($this->once())->method('has')->with('view_state')->willReturn(true);
        $subjects = $this->createStub(AuthenticatedSubjectResolver::class);
        $subjects->method('supportsPersistentStorage')->willReturn(true);
        $services = $this->createMock(Services::class);
        $services->expects($this->once())
            ->method('persistentFor')
            ->with($subjects, ['ui', 'storage'])
            ->willReturn($store);
        $services->expects($this->never())->method('session');

        self::assertTrue((new UiStorageAdapter($subjects, $services, $this->refinery))->offsetExists('view_state'));
    }

    public function testAnAnonymousActorUsesTheSessionStore(): void
    {
        $store = $this->createMock(Store::class);
        $store->expects($this->once())->method('has')->with('view_state')->willReturn(false);
        $subjects = $this->createStub(AuthenticatedSubjectResolver::class);
        $subjects->method('supportsPersistentStorage')->willReturn(false);
        $services = $this->createMock(Services::class);
        $services->expects($this->once())->method('session')->with(['ui', 'storage'])->willReturn($store);
        $services->expects($this->never())->method('persistentFor');

        self::assertFalse((new UiStorageAdapter($subjects, $services, $this->refinery))->offsetExists('view_state'));
    }

    public function testNoStoreIsChosenWhileTheAdapterIsBuilt(): void
    {
        $subjects = $this->createMock(AuthenticatedSubjectResolver::class);
        $subjects->expects($this->never())->method('supportsPersistentStorage');
        $services = $this->createMock(Services::class);
        $services->expects($this->never())->method($this->anything());

        new UiStorageAdapter($subjects, $services, $this->refinery);
    }

    /**
     * The adapter may be built before the user logs in within the same request.
     */
    public function testALoginAfterTheAdapterWasBuiltSwitchesToThePersistentStore(): void
    {
        $session_store = $this->createMock(Store::class);
        $session_store->expects($this->once())->method('set')->with('view_state', ['page' => 1]);
        $persistent_store = $this->createMock(Store::class);
        $persistent_store->expects($this->once())->method('set')->with('view_state', ['page' => 2]);
        $subjects = $this->createStub(AuthenticatedSubjectResolver::class);
        $subjects->method('supportsPersistentStorage')->willReturnOnConsecutiveCalls(false, true);
        $services = $this->createStub(Services::class);
        $services->method('session')->willReturn($session_store);
        $services->method('persistentFor')->willReturn($persistent_store);

        $adapter = new UiStorageAdapter($subjects, $services, $this->refinery);
        $adapter->offsetSet('view_state', ['page' => 1]);
        $adapter->offsetSet('view_state', ['page' => 2]);
    }

    public function testOffsetGetPassesTheIdentityTransformation(): void
    {
        $store = $this->createMock(Store::class);
        $store->expects($this->once())
            ->method('get')
            ->with(
                'view_state',
                $this->callback(static function (Transformation $transformation): bool {
                    return $transformation->transform(['sort' => 'title']) === ['sort' => 'title']
                        && $transformation->transform(null) === null;
                })
            )
            ->willReturn(['sort' => 'title']);
        $subjects = $this->createStub(AuthenticatedSubjectResolver::class);
        $subjects->method('supportsPersistentStorage')->willReturn(false);
        $services = $this->createStub(Services::class);
        $services->method('session')->willReturn($store);

        $adapter = new UiStorageAdapter($subjects, $services, $this->refinery);

        self::assertSame(['sort' => 'title'], $adapter->offsetGet('view_state'));
    }
}
