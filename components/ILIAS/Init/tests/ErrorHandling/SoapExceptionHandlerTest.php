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

use ILIAS\Init\ErrorHandling\Application\DevmodeState;
use ILIAS\Init\ErrorHandling\Incident\ErrorIncident;
use ILIAS\Init\ErrorHandling\Incident\ErrorIncidentId;
use ILIAS\Init\ErrorHandling\Incident\InMemoryErrorIncidentRegistry;
use ILIAS\Init\ErrorHandling\Infrastructure\Whoops\SoapExceptionHandler;
use PHPUnit\Framework\TestCase;
use Whoops\Exception\Inspector;

class SoapExceptionHandlerTest extends TestCase
{
    public function testAppendsIncidentReferenceInProductionFaultString(): void
    {
        $registry = new InMemoryErrorIncidentRegistry();
        $registry->record(new ErrorIncident(new ErrorIncidentId('abc_12')));

        $handler = new SoapExceptionHandler($registry, $this->devmodeState(false));
        $handler->setInspector(new Inspector(new RuntimeException('internal soap failure')));

        ob_start();
        $handler->handle();
        $output = (string) ob_get_clean();

        self::assertStringContainsString('internal soap failure', $output);
        self::assertStringContainsString('abc_12', $output);
    }

    public function testFallsBackToExceptionMessageWithoutIncident(): void
    {
        $handler = new SoapExceptionHandler(new InMemoryErrorIncidentRegistry(), $this->devmodeState(false));
        $handler->setInspector(new Inspector(new RuntimeException('internal soap failure')));

        ob_start();
        $handler->handle();
        $output = (string) ob_get_clean();

        self::assertStringContainsString('internal soap failure', $output);
    }

    public function testAppendsIncidentReferenceInDevmodeFaultString(): void
    {
        $registry = new InMemoryErrorIncidentRegistry();
        $registry->record(new ErrorIncident(new ErrorIncidentId('abc_12')));

        $handler = new SoapExceptionHandler($registry, $this->devmodeState(true));
        $handler->setInspector(new Inspector(new RuntimeException('internal soap failure')));

        ob_start();
        $handler->handle();
        $output = (string) ob_get_clean();

        self::assertStringContainsString('internal soap failure', $output);
        self::assertStringContainsString('abc_12', $output);
    }

    private function devmodeState(bool $is_active): DevmodeState
    {
        return new class ($is_active) implements DevmodeState {
            public function __construct(private bool $is_active)
            {
            }

            public function isActive(): bool
            {
                return $this->is_active;
            }
        };
    }
}
