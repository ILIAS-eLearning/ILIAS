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

namespace ILIAS\Tests\KeyValueStorage\Internal;

use ILIAS\KeyValueStorage\Internal\StorageServices;
use ILIAS\KeyValueStorage\SessionRepository;
use ILIAS\Tests\KeyValueStorage\InMemoryRepository;
use PHPUnit\Framework\TestCase;

class StorageServicesTest extends TestCase
{
    private InMemoryRepository $session;

    private InMemoryRepository $persistent;

    private StorageServices $services;

    protected function setUp(): void
    {
        $this->session = new class () extends InMemoryRepository implements SessionRepository {
        };
        $this->persistent = new InMemoryRepository();
        $this->services = new StorageServices($this->session, $this->persistent);
    }

    public function testTheScopesUseSeparateRepositories(): void
    {
        $this->services->session(['ui', 'storage'])->set('a', 1);
        $this->services->persistent(['ui', 'storage'])->set('b', 2);

        $this->assertSame(['a' => '1'], $this->session->entries['ui.storage']);
        $this->assertSame(['b' => '2'], $this->persistent->entries['ui.storage']);
    }

    public function testTheSameScopeAndNamespaceYieldTheSameStore(): void
    {
        $this->assertSame(
            $this->services->session(['ui', 'storage']),
            $this->services->session(['ui', 'storage'])
        );
    }

    public function testDifferentScopesYieldDifferentStoresForTheSameNamespace(): void
    {
        $this->assertNotSame(
            $this->services->session(['ui', 'storage']),
            $this->services->persistent(['ui', 'storage'])
        );
    }

    public function testDifferentNamespacesYieldDifferentStores(): void
    {
        $this->assertNotSame(
            $this->services->session(['ui', 'storage']),
            $this->services->session(['ui', 'table'])
        );
    }

    public function testTheSharedStoreKeepsWhatWasWrittenThroughIt(): void
    {
        $this->services->session(['ui', 'storage'])->set('sort', 'title');

        $this->assertSame('title', $this->services->session(['ui', 'storage'])->get('sort'));
        $this->assertSame(0, $this->session->reads);
    }
}
