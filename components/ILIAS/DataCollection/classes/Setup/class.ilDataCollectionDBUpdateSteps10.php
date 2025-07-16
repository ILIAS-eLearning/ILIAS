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

class ilDataCollectionDBUpdateSteps10 implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $stmt = $this->db->queryF(
            'SELECT il_dcl_field.*, area.value As text_area FROM il_dcl_field ' .
            'LEFT JOIN il_dcl_field_prop AS lenght ON il_dcl_field.id = lenght.field_id AND lenght.name = %s' .
            'LEFT JOIN il_dcl_field_prop AS area ON il_dcl_field.id = area.field_id AND area.name = %s' .
            'WHERE il_dcl_field.datatype_id = %s AND lenght.value IS NULL',
            [ilDBConstants::T_TEXT, ilDbConstants::T_TEXT, ilDbConstants::T_INTEGER],
            [ilDclBaseFieldModel::PROP_LENGTH, 'text_area', ilDclDatatype::INPUTFORMAT_TEXT]
        );

        while ($row = $this->db->fetchAssoc($stmt)) {
            $this->db->insert(
                'il_dcl_field_prop',
                [
                    'id' => [ilDBConstants::T_INTEGER, $this->db->nextId('il_dcl_field_prop')],
                    'field_id' => [ilDBConstants::T_INTEGER, $row['id']],
                    'name' => [ilDBConstants::T_TEXT, ilDclBaseFieldModel::PROP_LENGTH],
                    'value' => [ilDBConstants::T_TEXT, ($row['text_area'] === '1') ? '4000' : '200'],
                ]
            );
        }

        $this->db->manipulateF(
            "DELETE FROM il_dcl_field_prop WHERE name = %s",
            [ilDBConstants::T_TEXT],
            ['text_area']
        );
    }
}
