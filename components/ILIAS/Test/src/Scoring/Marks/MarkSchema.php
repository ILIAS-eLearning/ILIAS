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

namespace ILIAS\Test\Scoring\Marks;

use ILIAS\Test\ExportImport\Exportable;
use ILIAS\Test\Logging\AdditionalInformationGenerator;

/**
 * A class defining mark schemas for assessment test objects
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version	$Id$
 *
 * @ingroup components\ILIASTest
 */
class MarkSchema implements Exportable
{
    /**
     * @var array<\ILIAS\Test\Scoring\Marks\Mark>
     */
    private array $mark_steps;
    private int $nr_of_passed_marks;
    private int $nr_of_zero_percentage_marks;

    public function __construct(
        private int $test_id
    ) {
        $this->mark_steps = [];
    }

    public function withTestId(int $test_id): self
    {
        $clone = clone $this;
        $clone->test_id = $test_id;
        return $clone;
    }

    public function getTestId(): int
    {
        return $this->test_id;
    }

    public function hasSinglePassedMark(): bool
    {
        return $this->nr_of_passed_marks === 1;
    }

    public function hasSingleZeroPercentageMark(): bool
    {
        return $this->nr_of_zero_percentage_marks === 1;
    }

    public function getMatchingMark(
        float $percentage
    ): ?Mark {
        $reached = round($percentage, 2);
        foreach (array_reverse($this->mark_steps) as $step) {
            $level = round($step->getMinimumLevel(), 2);
            if ($reached >= $level) {
                return $step;
            }
        }
        return null;
    }

    public function checkForMissingZeroPercentage(): bool
    {
        return $this->nr_of_zero_percentage_marks === 0;
    }

    public function checkForMissingPassed(): bool
    {
        return $this->nr_of_passed_marks === 0;
    }

    public function checkForFailedAfterPassed(): bool
    {
        $has_to_be_passed = false;
        foreach ($this->mark_steps as $step) {
            if ($has_to_be_passed && !$step->getPassed()) {
                return true;
            }
            if ($step->getPassed() === true) {
                $has_to_be_passed = true;
            }
        }
        return false;
    }

    /**
     * @return Mark[]
     */
    public function getMarkSteps(): array
    {
        return $this->mark_steps;
    }

    /**
     * @param array<\ILIAS\Test\Scoring\Marks\Mark> $mark_steps
     */
    public function withMarkSteps(array $mark_steps): self
    {
        $clone = clone $this;
        $clone->mark_steps = $this->sort($mark_steps);
        [$clone->nr_of_passed_marks, $clone->nr_of_zero_percentage_marks] = array_reduce(
            $mark_steps,
            function (array $c, Mark $v): array {
                if ($v->getPassed()) {
                    $c[0]++;
                }
                if ($v->getMinimumLevel() === 0.0) {
                    $c[1]++;
                }
                return $c;
            },
            [0, 0]
        );
        return $clone;
    }

    private function sort(array $mark_steps): array
    {
        usort(
            $mark_steps,
            function ($a, $b): int {
                if ($a->getMinimumLevel() === $b->getMinimumLevel()) {
                    $res = strcmp($a->getShortName(), $b->getShortName());
                    if ($res === 0) {
                        return strcmp($a->getOfficialName(), $b->getOfficialName());
                    } else {
                        return $res;
                    }
                }
                return ($a->getMinimumLevel() < $b->getMinimumLevel()) ? -1 : 1;
            }
        );
        return $mark_steps;
    }


    public function toLog(AdditionalInformationGenerator $additional_info): array
    {
        $log_array = [];
        foreach ($this->getMarkSteps() as $mark) {
            $log_array[$mark->getShortName()] = [
                AdditionalInformationGenerator::KEY_MARK_SHORT_NAME => $mark->getShortName(),
                AdditionalInformationGenerator::KEY_MARK_OFFICIAL_NAME => $mark->getOfficialName(),
                AdditionalInformationGenerator::KEY_MARK_MINIMUM_LEVEL => $mark->getMinimumLevel(),
                AdditionalInformationGenerator::KEY_MARK_IS_PASSING => $additional_info
                    ->getTrueFalseTagForBool($mark->getPassed())
            ];
        }
        return $log_array;
    }

    public function toExport(): array
    {
        return ['mark_steps' => array_map(static fn(Mark $mark): array => $mark->toExport(), $this->mark_steps)];
    }

    public static function fromExport(array $data): static
    {
        return (new self($data['test_id'] ?? -1))
            ->withMarkSteps(array_map(static fn(array $mark): Mark => Mark::fromExport($mark), $data['mark_steps']));
    }
}
