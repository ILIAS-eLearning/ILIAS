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

use ILIAS\Init\ErrorHandling\Application\ErrorIncidentReporting;
use ILIAS\Init\ErrorHandling\Infrastructure\Whoops\RecordErrorIncidentHandler;
use PHPUnit\Framework\TestCase;
use Whoops\Exception\Inspector;

class RecordErrorIncidentHandlerTest extends TestCase
{
    public function testDelegatesReportingAndContinuesHandlerChain(): void
    {
        $reporting = $this->createMock(ErrorIncidentReporting::class);
        $inspector = new Inspector(new RuntimeException('test'));
        $reporting->expects($this->once())->method('report')->with($inspector)->willReturn(null);

        $handler = new RecordErrorIncidentHandler($reporting);
        $handler->setInspector($inspector);

        self::assertNull($handler->handle());
    }
}
