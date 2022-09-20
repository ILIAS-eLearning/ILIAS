<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
* @ingroup ModulesTest
*/

class ilTestAggregatedResultsTableGUI extends ilTable2GUI
{
    public function __construct(?object $a_parent_obj, string $a_parent_cmd)
    {
        parent::__construct($a_parent_obj, $a_parent_cmd);

        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;

        $this->setFormName('aggregated');
        $this->setTitle($this->lng->txt('tst_results_aggregated'));
        $this->setStyle('table', 'fullwidth');
        $this->addColumn($this->lng->txt("result"), 'result', '');
        $this->addColumn($this->lng->txt("value"), 'value', '');

        $this->setRowTemplate("tpl.il_as_tst_aggregated_results_row.html", "Modules/Test");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->disable('sort');
        $this->enable('header');
        $this->disable('select_all');
    }

    public function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("RESULT", $a_set["result"]);
        $this->tpl->setVariable("VALUE", $a_set["value"]);
    }
}
