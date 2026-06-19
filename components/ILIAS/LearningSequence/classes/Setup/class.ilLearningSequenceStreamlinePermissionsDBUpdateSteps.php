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

class ilLearningSequenceStreamlinePermissionsDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    private const string TYPE_TITLE = 'lso';

    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $read_ops_id = $this->getOperationId('read');
        $participate_ops_id = $this->getOperationId('participate');
        $unparticipate_ops_id = $this->getOperationId('unparticipate');

        if ($read_ops_id === null) {
            throw new \RuntimeException('Cannot migrate learning sequence permissions: RBAC operation "read" not found.');
        }

        $old_ops_ids = array_values(
            array_filter([
                $participate_ops_id,
                $unparticipate_ops_id
            ])
        );
        if ($old_ops_ids === []) {
            return;
        }

        $res = $this->db->query(
            "SELECT pa.rol_id, pa.ref_id, pa.ops_id " .
            "FROM rbac_pa pa " .
            "JOIN object_reference ref ON ref.ref_id = pa.ref_id " .
            "JOIN object_data obj ON obj.obj_id = ref.obj_id " .
            "WHERE obj.type = '" . self::TYPE_TITLE . "'"
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $serialized = $row['ops_id'] ?? null;
            if (!is_string($serialized) || $serialized === '') {
                continue;
            }

            $ops = @unserialize($serialized, ['allowed_classes' => false]);
            if (!is_array($ops)) {
                continue;
            }

            $ops = array_map('intval', $ops);
            $has_old = false;
            foreach ($old_ops_ids as $old_ops_id) {
                if (in_array((int) $old_ops_id, $ops, true)) {
                    $has_old = true;
                    break;
                }
            }
            if (!$has_old) {
                continue;
            }

            $ops[] = $read_ops_id;
            $ops = array_values(array_unique(array_diff($ops, array_map('intval', $old_ops_ids))));
            sort($ops);

            $this->db->manipulateF(
                'UPDATE rbac_pa SET ops_id = %s WHERE rol_id = %s AND ref_id = %s',
                [\ilDBConstants::T_TEXT, \ilDBConstants::T_INTEGER, \ilDBConstants::T_INTEGER],
                [serialize($ops), (int) $row['rol_id'], (int) $row['ref_id']]
            );
        }
    }

    public function step_2(): void
    {
        $participate_ops_id = $this->getOperationId('participate');
        $unparticipate_ops_id = $this->getOperationId('unparticipate');

        if ($participate_ops_id === null && $unparticipate_ops_id === null) {
            return;
        }

        $ops_ids = array_values(array_filter([(int) $participate_ops_id, (int) $unparticipate_ops_id]));
        if ($ops_ids === []) {
            return;
        }

        $in = implode(',', array_map('intval', $ops_ids));
        $sql =
            "DELETE FROM rbac_ta " .
            "WHERE typ_id IN (" .
                "SELECT obj_id FROM object_data " .
                "WHERE type = 'typ' AND title = '" . self::TYPE_TITLE . "'" .
            ") " .
            "AND ops_id IN (" . $in . ")";

        $this->db->manipulate($sql);
    }

    private function getOperationId(string $operation): ?int
    {
        $res = $this->db->queryF(
            'SELECT ops_id FROM rbac_operations WHERE operation = %s',
            [\ilDBConstants::T_TEXT],
            [$operation]
        );
        $row = $this->db->fetchAssoc($res);
        if (!is_array($row) || !isset($row['ops_id'])) {
            return null;
        }
        return (int) $row['ops_id'];
    }
}
