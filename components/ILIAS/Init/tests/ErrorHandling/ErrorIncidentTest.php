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
use PHPUnit\Framework\TestCase;

class ErrorIncidentTest extends TestCase
{
    public function testExposesIdentifierAsValueObject(): void
    {
        $incident_id = new ErrorIncidentId('abc_1234');
        $incident = new ErrorIncident($incident_id);

        self::assertSame($incident_id, $incident->identifier());
        self::assertSame('abc_1234', $incident->identifier()->value());
    }
}
