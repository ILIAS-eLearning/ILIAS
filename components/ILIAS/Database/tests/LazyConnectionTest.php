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

namespace ILIAS\Tests\Database;

use ILIAS\Database\Connection;
use ILIAS\Database\LazyConnection;
use ILIAS\DI\Container;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\TestCase;

#[BackupGlobals(true)]
class LazyConnectionTest extends TestCase
{
    public function testTheConnectionIsTakenFromTheContainerWhenItIsAskedFor(): void
    {
        $db = $this->createStub(\ilDBInterface::class);

        $container = new Container();
        $container['ilDB'] = static fn(): \ilDBInterface => $db;
        $GLOBALS['DIC'] = $container;

        $connection = new LazyConnection();

        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertSame($db, $connection->get());
    }

    public function testNothingIsResolvedBeforeGetIsCalled(): void
    {
        unset($GLOBALS['DIC']);

        $this->assertInstanceOf(LazyConnection::class, new LazyConnection());
    }
}
