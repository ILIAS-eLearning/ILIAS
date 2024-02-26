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
class MarkSchema
{
    /**
     * @var array<\ILIAS\Test\Scoring\Marks\Mark>
     */
    public array $mark_steps;

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

    /**
     * Creates a simple mark schema for two mark steps:
     * failed and passed.
     *
     * @see    $mark_steps
     *
     * @param string    $txt_failed_short    The short text of the failed mark.
     * @param string    $txt_failed_official The official text of the failed mark.
     * @param float|int $percentage_failed   The minimum percentage level reaching the failed mark.
     * @param integer   $failed_passed       Indicates the passed status of the failed mark (0 = failed, 1 = passed).
     * @param string    $txt_passed_short    The short text of the passed mark.
     * @param string    $txt_passed_official The official text of the passed mark.
     * @param float|int $percentage_passed   The minimum percentage level reaching the passed mark.
     * @param integer   $passed_passed       Indicates the passed status of the passed mark (0 = failed, 1 = passed).
     */
    public function createSimpleSchema(
        string $txt_failed_short = "failed",
        string $txt_failed_official = "failed",
        float $percentage_failed = 0,
        bool $failed_passed = false,
        string $txt_passed_short = "passed",
        string $txt_passed_official = "passed",
        float $percentage_passed = 50,
        bool $passed_passed = true
    ): self {
        $mark_steps = [
            new Mark($txt_failed_short, $txt_failed_official, $percentage_failed, $failed_passed),
            new Mark($txt_passed_short, $txt_passed_official, $percentage_passed, $passed_passed)
        ];
        return $this->withMarkSteps($mark_steps);
    }

    public function getMatchingMark(
        float $percentage
    ): ?Mark {
        $reached = round($percentage, 2);
        foreach ($this->mark_steps as $step) {
            $level = round($step->getMinimumLevel(), 2);
            if ($reached >= $level) {
                return $step;
            }
        }
        return null;
    }

    public function checkForMissingZeroPercentage(): bool
    {
        foreach ($this->mark_steps as $step) {
            if ($step->getMinimumLevel() === 0.0) {
                return false;
            }
        }
        return true;
    }

    public function checkForMissingPassed(): bool|string
    {
        foreach ($this->mark_steps as $step) {
            if ($step->getPassed() === true) {
                return false;
            }
        }
        return true;
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
        return $clone;
    }

    private function sort(array $mark_steps): array
    {
        usort(
            $mark_steps,
            function ($a, $b): int {
                if ($a->getMinimumLevel() === $b->getMinimumLevel()) {
                    $res = strcmp($a->getShortName(), $b->getShortName());
                    if ($res == 0) {
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


    public function toLog(\ilLanguage $lng): array
    {
        $log_array = [];
        foreach ($this->getMarkSteps() as $mark) {
            $log_array[$mark->getShortName()] = [
                $lng->txt('tst_mark_official_form') => $mark->getOfficialName(),
                $lng->txt('tst_mark_minimum_level') => $mark->getMinimumLevel(),
                $lng->txt('tst_mark_passed') => $mark->getPassed()
            ];
        }
        return $log_array;
    }
}
