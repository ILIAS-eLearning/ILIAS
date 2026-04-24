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

class ilForumDatabaseUpdateSteps11 implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $res = $this->db->queryF(
            "SELECT * FROM settings WHERE module = %s AND keyword = %s",
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            ['forum', 'frm_mod_tpl_perm_revocation']
        );

        $row = $this->db->fetchAssoc($res);
        if ($row) {
            $this->db->manipulateF(
                "DELETE FROM settings WHERE module = %s AND keyword = %s",
                [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
                ['forum', 'frm_mod_tpl_perm_revocation']
            );

            return;
        }

        $res = $this->db->queryF(
            "SELECT obj_id FROM object_data WHERE type = %s AND title = %s",
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            ['rolt', 'il_frm_moderator']
        );

        $row = $this->db->fetchAssoc($res);
        if (!isset($row['obj_id'])) {
            return;
        }

        $rol_id = (int) $row['obj_id'];

        $res = $this->db->query(
            "SELECT ops_id FROM rbac_operations WHERE " . $this->db->in(
                'operation',
                ['edit_permission', 'delete', 'copy'],
                false,
                ilDBConstants::T_TEXT
            )
        );

        $rows = $this->db->fetchAll($res);
        if (!$rows) {
            return;
        }

        $operations_to_remove = [];
        foreach ($rows as $row) {
            $operations_to_remove[] = (int) $row['ops_id'];
        }

        foreach ($operations_to_remove as $op_id) {
            $this->db->manipulateF(
                "DELETE FROM rbac_templates WHERE rol_id = %s AND type = %s AND ops_id = %s AND parent = %s",
                [ilDBConstants::T_INTEGER, ilDBConstants::T_TEXT, ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
                [$rol_id, 'frm', $op_id, 8]
            );
        }
    }
}
