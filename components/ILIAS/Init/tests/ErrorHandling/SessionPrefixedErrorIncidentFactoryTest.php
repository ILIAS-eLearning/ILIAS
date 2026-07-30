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

use ILIAS\Init\ErrorHandling\Incident\SessionPrefixedErrorIncidentFactory;
use PHPUnit\Framework\TestCase;

class SessionPrefixedErrorIncidentFactoryTest extends TestCase
{
    public function testCreatesIdentifierFromSessionPrefixAndRandomNumber(): void
    {
        $engine = new \Random\Engine\Mt19937(12345);
        $factory = new SessionPrefixedErrorIncidentFactory(new \Random\Randomizer($engine));

        $incident = $factory->create('abcdef0123456789');

        self::assertSame('abcde_9997', $incident->identifier()->value());
    }

    public function testCreatesIdentifierWhenSessionIdIsEmpty(): void
    {
        $engine = new \Random\Engine\Mt19937(99);
        $factory = new SessionPrefixedErrorIncidentFactory(new \Random\Randomizer($engine));

        $incident = $factory->create('');

        self::assertSame('_3172', $incident->identifier()->value());
    }
}
