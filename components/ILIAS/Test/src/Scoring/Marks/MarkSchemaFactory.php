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
    public function createMarkSchema(array $rows, int $test_id): MarkSchema
    {
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

        return $mark_steps !== []
            ? $schema->withMarkSteps($mark_steps)
            : $schema->createSimpleSchema();
    }
}
