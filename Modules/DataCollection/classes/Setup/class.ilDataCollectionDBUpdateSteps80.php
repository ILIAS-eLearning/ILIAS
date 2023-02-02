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

class ilDataCollectionDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $this->db->manipulate("UPDATE il_dcl_tableview " .
            "SET description=" . $this->db->quote("", "text") .
            "WHERE description is null");
        $this->db->modifyTableColumn("il_dcl_tableview", "description", [ 'notnull' => true,
              'default' => '']);
    }

    public function step_2(): void
    {
        $this->db->manipulate("UPDATE il_dcl_tview_set " .
            "SET in_filter=0 " .
            "WHERE in_filter is null");
        $this->db->manipulate("UPDATE il_dcl_tview_set " .
            "SET visible=0 " .
            "WHERE visible is null");
        $this->db->manipulate("UPDATE il_dcl_tview_set " .
            "SET filter_changeable=0 " .
            "WHERE filter_changeable is null");
        $this->db->modifyTableColumn("il_dcl_tview_set", "in_filter", [ 'notnull' => true,
                                                                          'default' => 0]);
        $this->db->modifyTableColumn("il_dcl_tview_set", "visible", [ 'notnull' => true,
                                                                        'default' => 0]);
        $this->db->modifyTableColumn("il_dcl_tview_set", "filter_changeable", [ 'notnull' => true,
                                                                      'default' => 0]);
    }

    public function step_3(): void
    {
        $this->db->manipulate("UPDATE il_dcl_tfield_set " .
            "SET exportable=0 " .
            "WHERE exportable is null");
        $this->db->modifyTableColumn("il_dcl_tfield_set", "exportable", [ 'notnull' => true,
                                                                        'default' => 0]);
    }

    public function step_4(): void
    {
        $this->db->modifyTableColumn("il_dcl_stloc3_value", "value", [ 'notnull' => false]);
    }
}
