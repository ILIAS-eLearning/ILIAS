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

class MarkSchemaFactory
{
    /**
     * @param list<array{short_name: string, offial_name: string, minmum_level: string|float, passed: string|bool}> $rows
     */
    public function createMarkSchemaFromDBRow(array $rows, int $test_id): MarkSchema
    {
        if ($rows === []) {
            return $this->createSimpleSchema($test_id);
        }

        $schema = new MarkSchema($test_id);

        $mark_steps = [];
        foreach ($rows as $mark) {
            $mark_steps[] = new Mark(
                $mark['short_name'],
                $mark['official_name'],
                (float) $mark['minimum_level'],
                (bool) $mark['passed']
            );
        }

        return $schema->withMarkSteps($mark_steps);
    }

    /**
     * Creates a simple mark schema for two mark steps:
     * failed and passed.
     *
     * @see    $mark_steps
     *
     * @param int       $test_id             The test id.
     * @param string    $txt_failed_short    The short text of the failed mark.
     * @param string    $txt_failed_official The official text of the failed mark.
     * @param float     $percentage_failed   The minimum percentage level reaching the failed mark.
     * @param bool      $failed_passed       Indicates the passed status of the failed mark (0 = failed, 1 = passed).
     * @param string    $txt_passed_short    The short text of the passed mark.
     * @param string    $txt_passed_official The official text of the passed mark.
     * @param float     $percentage_passed   The minimum percentage level reaching the passed mark.
     * @param bool      $passed_passed       Indicates the passed status of the passed mark (0 = failed, 1 = passed).
     */
    public function createSimpleSchema(
        int $test_id,
        string $txt_failed_short = 'failed',
        string $txt_failed_official = 'failed',
        float $percentage_failed = 0,
        bool $failed_passed = false,
        string $txt_passed_short = 'passed',
        string $txt_passed_official = 'passed',
        float $percentage_passed = 50,
        bool $passed_passed = true
    ): MarkSchema {
        return (new MarkSchema($test_id))->withMarkSteps([
            new Mark($txt_failed_short, $txt_failed_official, $percentage_failed, $failed_passed),
            new Mark($txt_passed_short, $txt_passed_official, $percentage_passed, $passed_passed)
        ]);
    }
}
