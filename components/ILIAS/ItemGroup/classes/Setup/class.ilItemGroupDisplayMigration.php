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

namespace ILIAS\ItemGroup\Setup;

use ilDatabaseUpdateStepsExecutedObjective;
use ilDBInterface;
use ilDBConstants;
use ilItemGroupAR;
use ilItemGroupBehaviour;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;

class ilItemGroupDisplayMigration implements Migration
{
    private const MIGRATED_MARKER = -1;

    private ilDBInterface $db;

    public function getLabel(): string
    {
        return 'Migrate item group hide_title/behaviour to display/toggleable_initially.';
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return Migration::INFINITE;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilDatabaseUpdateStepsExecutedObjective(new ilItemGroupDBUpdateSteps())
        ];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
    }

    public function step(Environment $environment): void
    {
        $result = $this->db->queryF(
            'SELECT id, hide_title, behaviour FROM itgr_data WHERE hide_title <> %s AND behaviour <> %s LIMIT 1',
            [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
            [self::MIGRATED_MARKER, self::MIGRATED_MARKER]
        );

        $row = $this->db->fetchAssoc($result);
        if ($row === null) {
            return;
        }

        [$display, $toggleable_initially] = $this->mapLegacyValues((int) $row['hide_title'], (int) $row['behaviour']);

        $this->db->update(
            'itgr_data',
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
        $result = $this->db->queryF(
            'SELECT COUNT(id) AS cnt FROM itgr_data WHERE hide_title <> %s AND behaviour <> %s',
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
                ilItemGroupAR::DISPLAY_WITHOUT_TITLE,
                ilItemGroupAR::DISPLAY_WITH_TITLE_AND_TOGGLEABLE_INITIALLY_OPEN,
            ],
            $behaviour === ilItemGroupBehaviour::EXPANDABLE_CLOSED => [
                ilItemGroupAR::DISPLAY_WITH_TITLE_AND_TOGGLEABLE,
                ilItemGroupAR::DISPLAY_WITH_TITLE_AND_TOGGLEABLE_INITIALLY_CLOSED,
            ],
            $behaviour === ilItemGroupBehaviour::EXPANDABLE_OPEN => [
                ilItemGroupAR::DISPLAY_WITH_TITLE_AND_TOGGLEABLE,
                ilItemGroupAR::DISPLAY_WITH_TITLE_AND_TOGGLEABLE_INITIALLY_OPEN,
            ],
            default => [
                ilItemGroupAR::DISPLAY_WITH_TITLE,
                ilItemGroupAR::DISPLAY_WITH_TITLE_AND_TOGGLEABLE_INITIALLY_OPEN,
            ]
        };
    }
}
