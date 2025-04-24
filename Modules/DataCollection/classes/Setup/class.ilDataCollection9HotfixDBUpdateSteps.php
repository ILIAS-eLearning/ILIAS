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

class ilDataCollection9HotfixDBUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $stmt = $this->db->queryF(
            'SELECT DISTINCT tableview_id FROM il_dcl_tview_set WHERE il_dcl_tview_set.tableview_id NOT IN (SELECT DISTINCT tableview_id FROM il_dcl_tview_set WHERE field = %s);',
            [ilDBConstants::T_TEXT],
            ['comments']
        );
        while ($row = $this->db->fetchAssoc($stmt)) {
            $field_set = new ilDclTableViewFieldSetting();
            $field_set->setTableviewId((int) $row['tableview_id']);
            $field_set->setField('comments');
            $field_set->setFilterChangeable(true);
            $field_set->setVisibleCreate(true);
            $field_set->setVisibleEdit(true);
            $field_set->create();
        }
    }

    public function step_2(): void
    {
        $this->db->manipulateF(
            'UPDATE il_dcl_stloc1_value ' .
            'LEFT JOIN il_dcl_record_field ON il_dcl_stloc1_value.record_field_id = il_dcl_record_field.id ' .
            'LEFT JOIN il_dcl_field ON il_dcl_record_field.field_id = il_dcl_field.id ' .
            'LEFT JOIN il_dcl_field_prop ON il_dcl_field.id = il_dcl_field_prop.field_id AND il_dcl_field_prop.name = "multiple_selection" ' .
            'SET il_dcl_stloc1_value.value = REPLACE(il_dcl_stloc1_value.value, %s, %s) ' .
            'WHERE il_dcl_field.datatype_id = %s AND il_dcl_field_prop.value = %s',
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT, ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
            [', ', '; ', ilDclDatatype::INPUTFORMAT_COPY, 1]
        );
    }
}
