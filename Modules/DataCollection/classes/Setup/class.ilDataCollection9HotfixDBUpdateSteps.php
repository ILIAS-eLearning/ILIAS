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
}
