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
* @ingroup ModulesTest
*/

class ilTestHistoryTableGUI extends ilTable2GUI
{
    protected ?object $tstObject;

    public function __construct(ilObjTestGUI $parent_obj, string $parent_cmd)
    {
        parent::__construct($parent_obj, $parent_cmd);

        $this->setFormName('questionbrowser');
        $this->setFormAction($this->ctrl->getFormAction($this->parent_obj, "history"));
        $this->setStyle('table', 'fullwidth');
        $this->addColumn($this->lng->txt("assessment_log_datetime"), 'datetime', '25%');
        $this->addColumn($this->lng->txt("user"), 'user', '25%');
        $this->addColumn($this->lng->txt("assessment_log_text"), 'log', '50%');

        $this->setRowTemplate("tpl.il_as_tst_history_row.html", "Modules/Test");

        $this->setDefaultOrderField("datetime");
        $this->setDefaultOrderDirection("asc");

        $this->enable('header');
    }

    public function setTestObject($obj): void
    {
        $this->tstObject = $obj;
    }

    public function fillRow(array $a_set): void
    {
        $username = $this->tstObject->userLookupFullName($a_set["user_fi"], true);
        $this->tpl->setVariable("DATETIME", ilDatePresentation::formatDate(new ilDateTime($a_set["tstamp"], IL_CAL_UNIX)));
        $this->tpl->setVariable("USER", $username);
        $this->tpl->setVariable("LOG", trim(ilLegacyFormElementsUtil::prepareFormOutput($a_set["logtext"])));
    }
}
