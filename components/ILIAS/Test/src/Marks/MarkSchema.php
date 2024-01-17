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

namespace ILIAS\Test\Marks;

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
    public array $mark_steps;

    public function __construct(
        private int $test_id
    ) {
        $this->mark_steps = [];
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
        int $failed_passed = 0,
        string $txt_passed_short = "passed",
        string $txt_passed_official = "passed",
        float $percentage_passed = 50,
        int $passed_passed = 1
    ) {
        $this->flush();
        $this->addMarkStep($txt_failed_short, $txt_failed_official, $percentage_failed, $failed_passed);
        $this->addMarkStep($txt_passed_short, $txt_passed_official, $percentage_passed, $passed_passed);
    }

    /**
     * Adds a mark step to the mark schema. A new Mark object will be created and stored
     * in the $mark_steps array.
     *
     * @see $mark_steps
     *
     * @param string  $txt_short    The short text of the mark.
     * @param string  $txt_official The official text of the mark.
     * @param float   $percentage   The minimum percentage level reaching the mark.
     * @param integer $passed       The passed status of the mark (0 = failed, 1 = passed).
     */
    public function addMarkStep(string $txt_short = "", string $txt_official = "", float $percentage = 0, int $passed = 0): void
    {
        $mark = new Mark($txt_short, $txt_official, $percentage, $passed);
        array_push($this->mark_steps, $mark);
    }

    public function flush(): void
    {
        $this->mark_steps = [];
    }

    /**
     * Sorts the mark schema using the minimum level values.
     *
     * @see $mark_steps
     */
    public function sort(): void
    {
        function level_sort($a, $b): int
        {
            if ($a->getMinimumLevel() == $b->getMinimumLevel()) {
                $res = strcmp($a->getShortName(), $b->getShortName());
                if ($res == 0) {
                    return strcmp($a->getOfficialName(), $b->getOfficialName());
                } else {
                    return $res;
                }
            }
            return ($a->getMinimumLevel() < $b->getMinimumLevel()) ? -1 : 1;
        }
        usort($this->mark_steps, 'level_sort');
    }

    /**
     * Deletes the mark step with a given index.
     *
     * @see $mark_steps
     *
     * @param integer $index The index of the mark step to delete.
     */
    public function deleteMarkStep($index = 0): void
    {
        if ($index < 0) {
            return;
        }
        if (count($this->mark_steps) < 1) {
            return;
        }
        if ($index >= count($this->mark_steps)) {
            return;
        }
        unset($this->mark_steps[$index]);
        $this->mark_steps = array_values($this->mark_steps);
    }

    /**
     * Deletes multiple mark steps using their index positions.
     * @param array $indexes An array with all the index positions to delete.
     */
    public function deleteMarkSteps(array $indexes): void
    {
        foreach ($indexes as $key => $index) {
            if (!(($index < 0) or (count($this->mark_steps) < 1))) {
                unset($this->mark_steps[$index]);
            }
        }
        $this->mark_steps = array_values($this->mark_steps);
    }

    /**
     * Returns the matching mark for a given percentage.
     *
     * @see $mark_steps
     *
     * @param double $percentage A percentage value between 0 and 100.
     *
     * @return Mark|bool The mark object, if a matching mark was found, false otherwise.
     */
    public function getMatchingMark($percentage): Mark|bool
    {
        for ($i = count($this->mark_steps) - 1; $i >= 0; $i--) {
            $curMinLevel = $this->mark_steps[$i]->getMinimumLevel();
            $reached = round($percentage, 2);
            $level = round($curMinLevel, 2);
            if ($reached >= $level) {
                return $this->mark_steps[$i];
            }
        }
        return false;
    }

    /**
     * Check the marks for consistency.
     *
     * @see $mark_steps
     *
     * @return bool|string true if the check succeeds, als a text string containing a language string for an error message
     */
    public function checkMarks(): bool|string
    {
        $minimum_percentage = 100;
        $passed = 0;
        for ($i = 0; $i < count($this->mark_steps); $i++) {
            if ($this->mark_steps[$i]->getMinimumLevel() < $minimum_percentage) {
                $minimum_percentage = $this->mark_steps[$i]->getMinimumLevel();
            }
            if ($this->mark_steps[$i]->getPassed()) {
                $passed++;
            }
        }

        if ($minimum_percentage != 0) {
            return "min_percentage_ne_0";
        }

        if ($passed == 0) {
            return "no_passed_mark";
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
     * @param Mark[] $mark_steps
     */
    public function setMarkSteps(array $mark_steps): void
    {
        $this->mark_steps = $mark_steps;
    }


    public function toLog(): array
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
