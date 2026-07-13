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

use ILIAS\Setup\Migration;
use ILIAS\Setup\Environment;

class ilCourseCleanupActivationMigration implements Migration
{
    private \ilDBInterface $db;

    public function getLabel(): string
    {
        return "Remove Duplicate Rows In Activation Table";
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 10000;
    }

    public function getPreconditions(Environment $environment): array
    {
        return \ilResourceStorageMigrationHelper::getPreconditions();
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
    }

    public function step(Environment $environment): void
    {
        $activation_data = $this->db->fetchObject(
            $this->db->query(
                'SELECT obj_id FROM crs_items' . PHP_EOL
                . 'GROUP BY obj_id HAVING COUNT(*) > 1 LIMIT 1'
            )
        );

        if ($activation_data === null) {
            return;
        }

        $parent_data = $this->db->fetchObject(
            $this->db->queryF(
                'SELECT parent FROM tree WHERE child=%s',
                [ilDBConstants::T_INTEGER],
                [$activation_data->obj_id]
            )
        );

        if ($parent_data === null) {
            $this->db->manipulateF(
                'DELETE FROM crs_items WHERE obj_id = %s',
                [ilDBConstants::T_INTEGER],
                [$activation_data->obj_id]
            );
            return;
        }

        $this->db->manipulateF(
            'DELETE FROM crs_items WHERE obj_id = %s AND parent_id != %s',
            [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
            [$activation_data->obj_id, $parent_data->parent]
        );
    }

    public function getRemainingAmountOfSteps(): int
    {
        return $this->db->numRows(
            $this->db->query(
                'SELECT obj_id, parent_id FROM crs_items GROUP BY obj_id HAVING COUNT(*) > 1'
            )
        );
    }
}
