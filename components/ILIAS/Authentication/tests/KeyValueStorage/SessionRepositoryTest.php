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
use ILIAS\KeyValueStorage\Internal\StorageNamespace;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\TestCase;

#[BackupGlobals(true)]
class SessionRepositoryTest extends TestCase
{
    private SessionRepository $repository;

    private StorageNamespace $namespace;

    protected function setUp(): void
    {
        $_SESSION = [];
        $this->repository = new SessionRepository();
        $this->namespace = new StorageNamespace(['my_component', 'view_state']);
    }

    public function testEachEntryIsASessionVariableOfItsOwn(): void
    {
        $this->repository->write($this->namespace, 'sort', '"title"');

        $this->assertSame(['kvs:my_component.view_state:sort' => '"title"'], $_SESSION);
    }

    public function testWhatWasWrittenCanBeReadBack(): void
    {
        $this->repository->write($this->namespace, 'sort', '"title"');

        $this->assertTrue($this->repository->has($this->namespace, 'sort'));
        $this->assertSame('"title"', $this->repository->read($this->namespace, 'sort'));
    }

    public function testAnAbsentEntryReadsAsNull(): void
    {
        $this->assertFalse($this->repository->has($this->namespace, 'sort'));
        $this->assertNull($this->repository->read($this->namespace, 'sort'));
    }

    public function testAForeignSessionVariableIsNotMistakenForAnEntry(): void
    {
        $_SESSION['kvs:my_component.view_state:sort'] = ['not', 'a', 'string'];

        $this->assertNull($this->repository->read($this->namespace, 'sort'));
    }

    public function testRemoveDropsOneEntry(): void
    {
        $this->repository->write($this->namespace, 'a', '1');
        $this->repository->write($this->namespace, 'b', '2');

        $this->repository->remove($this->namespace, 'a');

        $this->assertFalse($this->repository->has($this->namespace, 'a'));
        $this->assertTrue($this->repository->has($this->namespace, 'b'));
    }

    public function testRemoveAllLeavesOtherNamespacesAndTheRestOfTheSessionAlone(): void
    {
        $nested = new StorageNamespace(['my_component', 'view_state', 'details']);
        $sibling = new StorageNamespace(['other_component']);
        $this->repository->write($this->namespace, 'a', '1');
        $this->repository->write($nested, 'a', '2');
        $this->repository->write($sibling, 'a', '3');
        $_SESSION['AccountId'] = 6;

        $this->repository->removeAll($this->namespace);

        $this->assertFalse($this->repository->has($this->namespace, 'a'));
        $this->assertTrue($this->repository->has($nested, 'a'));
        $this->assertTrue($this->repository->has($sibling, 'a'));
        $this->assertSame(6, $_SESSION['AccountId']);
    }

    public function testRemoveAllOnAnEmptySessionDoesNothing(): void
    {
        $this->repository->removeAll($this->namespace);

        $this->assertSame([], $_SESSION);
    }

    public function testRemoveAllLeavesASessionThatHoldsNoEntriesUntouched(): void
    {
        $_SESSION['AccountId'] = 6;
        $_SESSION['_authsession'] = ['user' => 'root'];

        $this->repository->removeAll($this->namespace);

        $this->assertSame(['AccountId' => 6, '_authsession' => ['user' => 'root']], $_SESSION);
    }

    public function testANamespaceCannotReachTheEntryOfAnother(): void
    {
        $this->repository->write(new StorageNamespace(['a', 'b']), 'c', 'nested');
        $this->repository->write(new StorageNamespace(['a']), 'b.c', 'flat');

        $this->assertSame('nested', $this->repository->read(new StorageNamespace(['a', 'b']), 'c'));
        $this->assertSame('flat', $this->repository->read(new StorageNamespace(['a']), 'b.c'));
    }
}
