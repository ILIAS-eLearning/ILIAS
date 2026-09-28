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

use ILIAS\Init\ErrorHandling\Incident\ErrorIncident;
use ILIAS\Init\ErrorHandling\Incident\ErrorIncidentId;
use ILIAS\Init\ErrorHandling\Incident\InMemoryErrorIncidentRegistry;
use PHPUnit\Framework\TestCase;

class InMemoryErrorIncidentRegistryTest extends TestCase
{
    public function testStartsWithoutCurrentIncident(): void
    {
        $registry = new InMemoryErrorIncidentRegistry();

        self::assertNull($registry->current());
    }

    public function testRecordsAndReturnsCurrentIncident(): void
    {
        $registry = new InMemoryErrorIncidentRegistry();
        $incident = new ErrorIncident(new ErrorIncidentId('abc_99'));

        $registry->record($incident);

        self::assertSame($incident, $registry->current());
    }

    public function testClearRemovesCurrentIncident(): void
    {
        $registry = new InMemoryErrorIncidentRegistry();
        $registry->record(new ErrorIncident(new ErrorIncidentId('abc_99')));

        $registry->clear();

        self::assertNull($registry->current());
    }
}
