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

namespace ILIAS\Tests\KeyValueStorage;

use ILIAS\Database\Connection;
use ILIAS\KeyValueStorage\Internal\DatabaseRepository;
use ILIAS\KeyValueStorage\Internal\StorageServices;
use ILIAS\KeyValueStorage\Services;
use ILIAS\KeyValueStorage\SessionRepository;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Setup\Agent;
use PHPUnit\Framework\TestCase;

class KeyValueStorageTest extends TestCase
{
    /** @var list<string> */
    private array $define = [];

    private array|\ArrayAccess $implement;

    private array|\ArrayAccess $contribute;

    private array|\ArrayAccess $internal;

    protected function setUp(): void
    {
        $this->define = [];
        $this->implement = new LazyContainer();
        $this->contribute = new LazyContainer();
        $this->internal = new LazyContainer();

        $use = new LazyContainer([
            SessionRepository::class => $this->createStub(SessionRepository::class),
        ]);
        $pull = new LazyContainer([
            Connection::class => $this->createStub(Connection::class),
            Refinery::class => $this->createStub(Refinery::class),
        ]);
        $unused = new LazyContainer();

        (new \ILIAS\KeyValueStorage())->init(
            $this->define,
            $this->implement,
            $use,
            $this->contribute,
            $unused,
            $unused,
            $pull,
            $this->internal
        );
    }

    public function testOnlyTheConsumerEntryPointAndTheSessionScopeAreDeclared(): void
    {
        $this->assertSame([Services::class, SessionRepository::class], $this->define);
    }

    public function testTheServicesAreImplemented(): void
    {
        $services = $this->implement[Services::class];

        $this->assertInstanceOf(StorageServices::class, $services);
        $this->assertInstanceOf(Services::class, $services);
    }

    public function testThePersistentScopeIsStoredByThisComponent(): void
    {
        $this->assertInstanceOf(
            DatabaseRepository::class,
            $this->internal[DatabaseRepository::class]
        );
    }

    public function testTheSetupAgentIsContributed(): void
    {
        $agent = $this->contribute[Agent::class];

        $this->assertInstanceOf(\ILIAS\KeyValueStorage\Setup\Agent::class, $agent);
    }
}
