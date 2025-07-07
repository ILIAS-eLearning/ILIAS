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

class ilDataCollectionDBUpdateSteps11 implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if ($this->db->tableColumnExists('il_dcl_field', 'is_unique')) {
            $st = $this->db->queryF(
                'SELECT id FROM il_dcl_field WHERE is_unique = %s AND datatype_id IN (%s, %s, %s, %s)',
                [
                    ilDBConstants::T_INTEGER,
                    ilDBConstants::T_INTEGER,
                    ilDBConstants::T_INTEGER,
                    ilDBConstants::T_INTEGER,
                    ilDBConstants::T_INTEGER
                ],
                [
                    1,
                    ilDclDatatype::INPUTFORMAT_TEXT,
                    ilDclDatatype::INPUTFORMAT_TEXT_SELECTION,
                    ilDclDatatype::INPUTFORMAT_DATE_SELECTION,
                    ilDclDatatype::INPUTFORMAT_ILIAS_REF,
                ]
            );
            while ($row = $this->db->fetchAssoc($st)) {
                $this->db->insert(
                    'il_dcl_field_prop',
                    [
                        "id" => [ilDBConstants::T_INTEGER, $this->db->nextId('il_dcl_field_prop')],
                        "field_id" => [ilDBConstants::T_INTEGER, (int) $row['id']],
                        "name" => [ilDBConstants::T_TEXT, ilDclBaseFieldModel::PROP_UNIQUE],
                        "value" => [ilDBConstants::T_TEXT, '1'],
                    ]
                );
            }
            $this->db->dropTableColumn('il_dcl_field', 'is_unique');
        }
    }

    public function step_2(): void
    {
        if (!$this->db->tableColumnExists('il_dcl_tableview', 'role_limitation')) {
            $this->db->addTableColumn('il_dcl_tableview', 'role_limitation', [
                'type' => ilDBConstants::T_INTEGER,
                'length' => 1,
                'notnull' => true,
                'default' => 0
            ]);
        }
        $this->db->manipulateF('UPDATE il_dcl_tableview SET role_limitation = %s', [ilDBConstants::T_INTEGER], [1]);
    }
}
