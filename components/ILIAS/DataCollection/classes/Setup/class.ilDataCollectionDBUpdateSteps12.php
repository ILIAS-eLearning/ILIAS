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

class ilDataCollectionDBUpdateSteps12 implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $this->db->manipulateF(
            'UPDATE il_dcl_datatype SET storage_location = 1 WHERE id = %s',
            [ilDBConstants::T_INTEGER],
            [ilDclDatatype::INPUTFORMAT_MOB]
        );

        $st = $this->db->queryF(
            'SELECT il_dcl_stloc2_value.id as id, il_dcl_record_field.id as rec_id, mob_data.rid as rid FROM il_dcl_field ' .
            'INNER JOIN il_dcl_record_field ON il_dcl_record_field.field_id = il_dcl_field.id ' .
            'INNER JOIN il_dcl_stloc2_value ON il_dcl_stloc2_value.record_field_id = il_dcl_record_field.id ' .
            'INNER JOIN mob_data ON il_dcl_stloc2_value.value = mob_data.id ' .
            'WHERE il_dcl_field.datatype_id = %s AND mob_data.rid != ""',
            [ilDBConstants::T_INTEGER],
            [ilDclDatatype::INPUTFORMAT_MOB]
        );

        while ($row = $this->db->fetchAssoc($st)) {
            $this->db->insert(
                'il_dcl_stloc1_value',
                [
                    'id' => [ilDBConstants::T_INTEGER, $this->db->nextId('il_dcl_stloc1_value')],
                    'record_field_id' => [ilDBConstants::T_INTEGER, (int) $row['rec_id']],
                    'value' => [ilDBConstants::T_TEXT, $row['rid']],
                ]
            );
            $this->db->manipulateF(
                "DELETE FROM il_dcl_stloc2_value WHERE id = %s",
                [ilDBConstants::T_INTEGER],
                [(int) $row['id']]
            );
            $this->db->manipulateF(
                "DELETE FROM mob_data WHERE rid = %s",
                [ilDBConstants::T_TEXT],
                [$row['rid']]
            );
        }
    }
}
