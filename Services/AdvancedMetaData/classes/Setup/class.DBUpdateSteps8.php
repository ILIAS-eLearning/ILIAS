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

namespace ILIAS\AdvancedMetaData\Setup;

use ILIAS\Setup;

class DBUpdateSteps8 implements \ilDatabaseUpdateSteps
{
    private \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if ($this->db->tableColumnExists('adv_mdf_definition', 'field_values')) {
            $field_infos = [
                'type' => 'clob',
                'notnull' => false,
                'default' => null
            ];
            $this->db->modifyTableColumn('adv_mdf_definition', 'field_values', $field_infos);
        }
    }

    public function step_2(): void
    {
        if (!$this->db->tableColumnExists('pg_amd_page_list', 'sdata')) {
            $field_infos = [
                'type' => 'clob',
                'notnull' => false,
                'default' => null
            ];
            $this->db->addTableColumn('pg_amd_page_list', 'sdata', $field_infos);
        }
    }

    private function migrate(int $id, int $field_id, $data): void
    {
        $query = 'UPDATE pg_amd_page_list ' .
            'SET sdata = ' . $this->db->quote(serialize(serialize($data)), \ilDBConstants::T_TEXT) . ' ' .
            'WHERE id = ' . $this->db->quote($id, \ilDBConstants::T_INTEGER) . ' ' .
            'AND field_id = ' . $this->db->quote($field_id, \ilDBConstants::T_INTEGER);
        $this->db->manipulate($query);
    }

    private function migrateData(int $field_id, $data): array
    {
        if (!is_array($data)) {
            return [];
        }
        $indexes = [];
        foreach ($data as $idx => $value) {
            $query = 'SELECT idx from adv_mdf_enum ' .
                'WHERE value = ' . $this->db->quote($value, \ilDBConstants::T_TEXT) . ' ' .
                'AND field_id = ' . $this->db->quote($field_id, \ilDBConstants::T_INTEGER);
            $res = $this->db->query($query);

            $found_index = false;
            while ($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
                $indexes[] = (int) $row->idx;
                $found_index = true;
            }
            if ($found_index) {
                continue;
            }
            $query = 'SELECT idx from adv_mdf_enum ' .
                'WHERE idx = ' . $this->db->quote($value, \ilDBConstants::T_TEXT) . ' ' .
                'AND field_id = ' . $this->db->quote($field_id, \ilDBConstants::T_INTEGER);
            $res = $this->db->query($query);
            while ($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
                $indexes[] = (int) $row->idx;
            }
        }
        return $indexes;
    }



    public function step_3(): void
    {
        $query = 'SELECT id, pg.field_id, data, field_type FROM pg_amd_page_list pg ' .
            'JOIN adv_mdf_definition adv ' .
            'ON pg.field_id = adv.field_id ' .
            'WHERE sdata IS null ';
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
            if ($row->field_type == 1 || $row->field_type == 8) {
                $this->migrate(
                    (int) $row->id,
                    (int) $row->field_id,
                    $this->migrateData(
                        (int) $row->field_id,
                        unserialize(unserialize($row->data))
                    )
                );
            } else {
                $this->migrate(
                    (int) $row->id,
                    (int) $row->field_id,
                    unserialize(unserialize($row->data))
                );
            }
        }
    }
}
