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

use PHPUnit\Framework\TestCase;

/**
 * The progress of the Assignment and Grade Services scores as the learning progress reads them.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTIToolProgressTest extends TestCase
{
    public function testActivityProgressIsReadByTheNameOfTheSpecification(): void
    {
        $this->assertSame(
            ['Initialized', 'Started', 'InProgress', 'Submitted', 'Completed'],
            array_map(static fn(ilLTIToolActivityProgress $progress): string => $progress->value, ilLTIToolActivityProgress::cases())
        );
        $this->assertNull(ilLTIToolActivityProgress::tryFrom('inprogress'));
    }

    public function testActivityInProgress(): void
    {
        $in_progress = array_filter(
            ilLTIToolActivityProgress::cases(),
            static fn(ilLTIToolActivityProgress $progress): bool => $progress->isInProgress()
        );

        $this->assertSame(
            [ilLTIToolActivityProgress::STARTED, ilLTIToolActivityProgress::IN_PROGRESS],
            array_values($in_progress)
        );
    }

    public function testGradingProgressIsReadByTheNameOfTheSpecification(): void
    {
        $this->assertSame(
            ['FullyGraded', 'Pending', 'PendingManual', 'Failed', 'NotReady'],
            array_map(static fn(ilLTIToolGradingProgress $progress): string => $progress->value, ilLTIToolGradingProgress::cases())
        );
    }

    public function testOnlyAFullyGradedScoreIsFinal(): void
    {
        foreach (ilLTIToolGradingProgress::cases() as $progress) {
            $this->assertSame($progress !== ilLTIToolGradingProgress::FULLY_GRADED, $progress->isPending(), $progress->value);
        }
    }
}
