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

namespace ILIAS\Cloud;

class RemoveCloudDBUpdate implements \ilDatabaseUpdateSteps
{
    private ?\ilDBInterface $db = null;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $this->db->dropTable('il_cld_data', false);
    }

    public function step_2(): void
    {
        $q = "SELECT obj_id FROM object_data WHERE type = %s";
        $res = $this->db->queryF($q, ['text'], ['cld']);
        while ($row = $this->db->fetchAssoc($res)) {
            $this->db->manipulateF('DELETE FROM object_data WHERE id = %s', ['integer'], [$row['obj_id']]);
            $this->db->manipulateF('DELETE FROM object_reference WHERE object_id = %s', ['integer'], [$row['obj_id']]);
            // tree?
        }
    }
}
