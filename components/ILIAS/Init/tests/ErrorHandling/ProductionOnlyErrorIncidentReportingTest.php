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
use ILIAS\Init\ErrorHandling\Application\ErrorIncidentReporting;
use ILIAS\Init\ErrorHandling\Application\ProductionOnlyErrorIncidentReporting;
use ILIAS\Init\ErrorHandling\Incident\ErrorIncident;
use ILIAS\Init\ErrorHandling\Incident\ErrorIncidentId;
use PHPUnit\Framework\TestCase;
use Whoops\Exception\Inspector;

class ProductionOnlyErrorIncidentReportingTest extends TestCase
{
    public function testDelegatesReportingWhenDevmodeIsInactive(): void
    {
        $inspector = new Inspector(new RuntimeException('test'));
        $incident = new ErrorIncident(new ErrorIncidentId('abc_12'));

        $inner = $this->createMock(ErrorIncidentReporting::class);
        $inner->expects($this->once())
            ->method('report')
            ->with($inspector)
            ->willReturn($incident);

        $reporting = new ProductionOnlyErrorIncidentReporting($inner, $this->devmodeState(false));

        $result = $reporting->report($inspector);

        self::assertSame($incident, $result);
    }

    public function testSkipsReportingWhenDevmodeIsActive(): void
    {
        $inner = $this->createMock(ErrorIncidentReporting::class);
        $inner->expects($this->never())->method('report');

        $reporting = new ProductionOnlyErrorIncidentReporting($inner, $this->devmodeState(true));

        $result = $reporting->report(new Inspector(new RuntimeException('test')));

        self::assertNull($result);
    }

    public function testEvaluatesDevmodeLazilyOnEveryReport(): void
    {
        $inspector = new Inspector(new RuntimeException('test'));
        $incident = new ErrorIncident(new ErrorIncidentId('abc_12'));

        $inner = $this->createStub(ErrorIncidentReporting::class);
        $inner->method('report')->willReturn($incident);

        $devmode = $this->devmodeState(true);
        $reporting = new ProductionOnlyErrorIncidentReporting($inner, $devmode);

        self::assertNull($reporting->report($inspector));

        $devmode->is_active = false;

        self::assertSame($incident, $reporting->report($inspector));
    }

    private function devmodeState(bool $is_active): DevmodeState
    {
        return new class ($is_active) implements DevmodeState {
            public function __construct(public bool $is_active)
            {
            }

            public function isActive(): bool
            {
                return $this->is_active;
            }
        };
    }
}
