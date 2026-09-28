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

namespace ILIAS\Logging\Config\ByComponent;

use ilDBInterface;
use ilDBConstants;
use ILIAS\Logging\ILIASLogLevel;

class DBRepository implements RepositoryInterface
{
    public function __construct(
        protected ilDBInterface $db
    ) {
    }

    public function addComponent(string $component_id): void
    {
        $res = $this->db->queryF(
            'SELECT * FROM log_components WHERE component_id = %s',
            [ilDBConstants::T_TEXT],
            [$component_id]
        );
        if (!$res->numRows()) {
            $this->db->insert(
                'log_components',
                ['component_id' => [ilDBConstants::T_TEXT, $component_id]]
            );
        }
    }

    /**
     * @return array<string, ?ILIASLogLevel>
     */
    public function getAllLevelsForComponents(): array
    {
        $levels_by_components = [];
        $res = $this->db->query('SELECT * FROM log_components');
        while (($row = $res->fetchAssoc())) {
            $levels_by_components[(string) $row['component_id']] = isset($row['log_level']) ?
                ILIASLogLevel::tryFrom((int) $row['log_level']) : null;
        }
        return $levels_by_components;
    }

    public function getLevelForComponent(string $component_id): ?ILIASLogLevel
    {
        $res = $this->db->queryF(
            'SELECT * FROM log_components WHERE component_id = %s',
            [ilDBConstants::T_TEXT],
            [$component_id]
        );
        if (($row = $res->fetchAssoc()) && isset($row['log_level'])) {
            return ILIASLogLevel::tryFrom((int) $row['log_level']);
        }
        return null;
    }

    public function updateLevelForComponent(string $component_id, ILIASLogLevel $level): void
    {
        $this->db->replace(
            'log_components',
            ['component_id' => [ilDBConstants::T_TEXT, $component_id]],
            ['log_level' => [ilDBConstants::T_INTEGER, $level->value]]
        );
    }

    public function resetLevelForComponent(string $component_id): void
    {
        $this->db->manipulateF(
            'DELETE FROM log_components WHERE component_id = %s',
            [ilDBConstants::T_TEXT],
            [$component_id]
        );
    }

    public function resetLevelsForAllComponents(): void
    {
        $this->db->manipulate('DELETE FROM log_components');
    }
}
