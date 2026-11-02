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

namespace ILIAS\ObjectGroup\Setup;

use ilDatabaseUpdateStepsExecutedObjective;
use ilDBConstants;
use ilDBInterface;
use ilObjectGroupAR;
use ilObjectGroupBehaviour;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;

class ilObjectGroupDisplayMigration implements Migration
{
    public const MIGRATED_MARKER = -1;

    private ilDBInterface $db;

    public function getLabel(): string
    {
        return 'Migrate object group hide_title/behaviour to display/toggleable_initially.';
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return Migration::INFINITE;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilDatabaseUpdateStepsExecutedObjective(new ilObjectGroupDBUpdateSteps()),
        ];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
    }

    public function step(Environment $environment): void
    {
        if (!$this->columnsExist()) {
            return;
        }

        $table = $this->dataTableName();

        $result = $this->db->queryF(
            'SELECT id, hide_title, behaviour FROM ' . $table . ' WHERE hide_title <> %s AND behaviour <> %s LIMIT 1',
            [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
            [self::MIGRATED_MARKER, self::MIGRATED_MARKER]
        );

        $row = $this->db->fetchAssoc($result);
        if ($row === null) {
            return;
        }

        [$display, $toggleable_initially] = $this->mapLegacyValues((int) $row['hide_title'], (int) $row['behaviour']);

        $this->db->update(
            $table,
            [
                'display' => [ilDBConstants::T_TEXT, $display],
                'toggleable_initially' => [ilDBConstants::T_TEXT, $toggleable_initially],
                'hide_title' => [ilDBConstants::T_INTEGER, self::MIGRATED_MARKER],
                'behaviour' => [ilDBConstants::T_INTEGER, self::MIGRATED_MARKER],
            ],
            [
                'id' => [ilDBConstants::T_INTEGER, (int) $row['id']],
            ]
        );
    }

    public function getRemainingAmountOfSteps(): int
    {
        if (!$this->columnsExist()) {
            return 0;
        }

        $table = $this->dataTableName();

        $result = $this->db->queryF(
            'SELECT COUNT(id) AS cnt FROM ' . $table . ' WHERE hide_title <> %s AND behaviour <> %s',
            [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
            [self::MIGRATED_MARKER, self::MIGRATED_MARKER]
        );

        return (int) ($this->db->fetchObject($result)?->cnt ?? 0);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function mapLegacyValues(int $hide_title, int $behaviour): array
    {
        return match (true) {
            $hide_title === 1 => [
                ilObjectGroupAR::DISPLAY_WITHOUT_TITLE,
                ilObjectGroupAR::DISPLAY_WITH_TITLE_AND_TOGGLEABLE_INITIALLY_OPEN,
            ],
            $behaviour === ilObjectGroupBehaviour::EXPANDABLE_CLOSED => [
                ilObjectGroupAR::DISPLAY_WITH_TITLE_AND_TOGGLEABLE,
                ilObjectGroupAR::DISPLAY_WITH_TITLE_AND_TOGGLEABLE_INITIALLY_CLOSED,
            ],
            $behaviour === ilObjectGroupBehaviour::EXPANDABLE_OPEN => [
                ilObjectGroupAR::DISPLAY_WITH_TITLE_AND_TOGGLEABLE,
                ilObjectGroupAR::DISPLAY_WITH_TITLE_AND_TOGGLEABLE_INITIALLY_OPEN,
            ],
            default => [
                ilObjectGroupAR::DISPLAY_WITH_TITLE,
                ilObjectGroupAR::DISPLAY_WITH_TITLE_AND_TOGGLEABLE_INITIALLY_OPEN,
            ],
        };
    }

    private function columnsExist(): bool
    {
        $table = $this->dataTableName();

        return $this->db->tableColumnExists($table, 'hide_title')
            && $this->db->tableColumnExists($table, 'behaviour');
    }

    private function dataTableName(): string
    {
        if ($this->db->tableExists('obgr_data')) {
            return 'obgr_data';
        }

        return 'itgr_data';
    }
}
