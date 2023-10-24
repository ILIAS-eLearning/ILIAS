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

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilFileObjectRBACDatabaseSteps implements ilDatabaseUpdateSteps
{
    public const EDIT_FILE = "edit_file";
    private ?ilDBInterface $database = null;

    public function prepare(ilDBInterface $db): void
    {
        $this->database = $db;
    }

    protected function getOpsID(ilDBInterface $db, string $operation): ?int
    {
        $res = $db->queryF(
            "SELECT ops_id FROM rbac_operations WHERE operation = %s",
            ["text"],
            [self::EDIT_FILE]
        );

        return $db->fetchAssoc($res)['ops_id'] ?? null;
    }

    /**
     * @description assign to all roles which already have the "edit" operation assigned fo files
     */
    public function step_1(): void
    {
        $edit_file_ops_id = $this->getOpsID($this->database, self::EDIT_FILE);

        $q = "SELECT
                rbac_pa.rol_id,
                rbac_pa.ops_id,
                rbac_pa.ref_id
            FROM rbac_pa
            JOIN object_reference ON rbac_pa.ref_id = object_reference.ref_id
            JOIN object_data ON object_reference.obj_id = object_data.obj_id
            WHERE object_data.type = 'file';
        ";

        $res = $this->database->query($q);
        while ($row = $this->database->fetchAssoc($res)) {
            $ops_ids = unserialize($row['ops_id'], ['allowed_classes' => false]);
            if (in_array(4, $ops_ids, false) && !in_array($edit_file_ops_id, $ops_ids, false)) {
                $ops_ids[] = $edit_file_ops_id;
                $ops_ids = array_unique($ops_ids);
                $this->database->update(
                    "rbac_pa",
                    ['ops_id' => serialize($ops_ids)],
                    ['rol_id' => ['integer', $row['rol_id']], 'ref_id' => ['integer', $row['ref_id']]]
                );
            }
        }
    }

    /**
     * @description add to all templates which already have the "edit" operation assigned fo files
     */
    public function step_2(): void
    {
        $edit_file_ops_id = $this->getOpsID($this->database, self::EDIT_FILE);

        $q = "SELECT * from rbac_templates WHERE type = %s AND ops_id = %s";
        $res = $this->database->queryF(
            $q,
            ['text', 'integer'],
            ['file', 4]
        );
        while ($row = $this->database->fetchAssoc($res)) {
            try {
                $this->database->insert(
                    "rbac_templates",
                    [
                        'type' => ['text', 'file'],
                        'ops_id' => ['integer', $edit_file_ops_id],
                        'rol_id' => ['integer', $row['rol_id']],
                        'parent' => ['integer', $row['parent']]
                    ]
                );
            } catch (Throwable) {
            };
        }
    }
}
