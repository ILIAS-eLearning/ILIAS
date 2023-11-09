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
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
* @ingroup components\ILIASTest
*/

class ilTestAggregatedResultsTableGUI extends ilTable2GUI
{
    public function __construct(ilTestEvaluationGUI $parent_obj, string $parent_cmd)
    {
        parent::__construct($parent_obj, $parent_cmd);

        $this->setFormName('aggregated');
        $this->setTitle($this->lng->txt('tst_results_aggregated'));
        $this->setStyle('table', 'fullwidth');
        $this->addColumn($this->lng->txt("result"), 'result', '');
        $this->addColumn($this->lng->txt("value"), 'value', '');

        $this->setRowTemplate("tpl.il_as_tst_aggregated_results_row.html", "components/ILIAS/Test");

        $this->setFormAction($this->ctrl->getFormAction($parent_obj, $parent_cmd));

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
