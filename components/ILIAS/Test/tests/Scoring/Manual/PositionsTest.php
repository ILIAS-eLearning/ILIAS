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
use ILIAS\Test\Scoring\Manual\Positions;
use ILIAS\Test\Scoring\Manual\ConsecutiveScoringMode;

class PositionsTest extends TestCase
{
    protected Positions $positions;

    protected function setUp(): void
    {
        parent::setUp();
        $user_questions = [
            100 => [400, 401, 402, 403],
            101 => [401, 403],
            102 => [400, 402, 404],
            103 => [403]
        ];
        $this->positions = new Positions($user_questions, [], []);
    }

    public function testPositions(): void
    {
        $this->assertInstanceOf(Positions::class, $this->positions);
        $this->assertEquals([], $this->positions->getAllQuestionProperties());
        $this->assertEquals([], $this->positions->getAllAttempts());
    }

    public function testPositionsModeAllByUsers(): void
    {
        $mode = new ConsecutiveScoringMode(
            ConsecutiveScoringMode::ORIENTATION_USER,
            ConsecutiveScoringMode::MODE_ALL_AT_ONCE,
        );
        $expected = [
            [[100],[400, 401, 402, 403]],
            [[101],[401, 403]],
            [[102],[400, 402, 404]],
            [[103],[403]],
        ];
        $this->assertEquals($expected, $this->positions->get($mode));
    }

    public function testPositionsModeAllByQuestion(): void
    {
        $mode = new ConsecutiveScoringMode(
            ConsecutiveScoringMode::ORIENTATION_QUESTION,
            ConsecutiveScoringMode::MODE_ALL_AT_ONCE,
        );
        $expected = [
            [[100, 102],[400]],
            [[100, 101],[401]],
            [[100, 102],[402]],
            [[100, 101, 103],[403]],
            [[102], [404]],
        ];
        $this->assertEquals($expected, $this->positions->get($mode));
    }

    public function testPositionsModeSingleByUser(): void
    {
        $mode = new ConsecutiveScoringMode(
            ConsecutiveScoringMode::ORIENTATION_USER,
            ConsecutiveScoringMode::MODE_ONE_BY_ONE,
        );
        $expected = [
            [[100],[400]],
            [[100],[401]],
            [[100],[402]],
            [[100],[403]],
            [[101],[401]],
            [[101],[403]],
            [[102],[400]],
            [[102],[402]],
            [[102],[404]],
            [[103],[403]]
        ];
        $this->assertEquals($expected, $this->positions->get($mode));
    }

    public function testPositionsModeSingleByQuestion(): void
    {
        $mode = new ConsecutiveScoringMode(
            ConsecutiveScoringMode::ORIENTATION_QUESTION,
            ConsecutiveScoringMode::MODE_ONE_BY_ONE,
        );
        $expected = [
            [[100],[400]],
            [[100],[401]],
            [[100],[402]],
            [[100],[403]],
            [[101],[401]],
            [[101],[403]],
            [[102],[400]],
            [[102],[402]],
            [[102],[404]],
            [[103],[403]]
        ];
        $this->assertEquals($expected, $this->positions->get($mode));
    }

    public function testPositionsFilter(): void
    {
        $mode = new ConsecutiveScoringMode(
            ConsecutiveScoringMode::ORIENTATION_QUESTION,
            ConsecutiveScoringMode::MODE_ONE_BY_ONE,
        );

        $filter_users = static fn(array $uids, array $qids): array => [
            array_intersect($uids, [100, 102]),
            $qids
        ];
        $filter_questions = static fn(array $uids, array $qids): array => [
            $uids,
            array_intersect($qids, [400, 402, 404]),
        ];

        $positions = $this->positions->applyFilters(
            $filter_users,
            $filter_questions
        );

        $expected = [
            [[100],[400]],
            [[100],[402]],
            [[102],[400]],
            [[102],[402]],
            [[102],[404]]
        ];
        $this->assertEquals($expected, $positions->get($mode));
    }
}
