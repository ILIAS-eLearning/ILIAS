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

/**
 * TableGUI class for found users in survey administration
 *
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class ilFoundUsersTableGUI extends ilTable2GUI
{
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd = ""
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->addColumn("", "f", "1");
        $this->addColumn($lng->txt("login"), "", "33%");
        $this->addColumn($lng->txt("firstname"), "", "33%");
        $this->addColumn($lng->txt("lastname"), "", "33%");
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.table_found_users_row.html", "Modules/Survey");
        $this->setDefaultOrderField("lastname");
        $this->setDefaultOrderDirection("asc");
    }

    protected function fillRow(array $a_set): void
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->setParameterByClass("ilObjSurveyAdministrationGUI", "item_id", $a_set["usr_id"]);
        $this->tpl->setVariable("USER_ID", $a_set["usr_id"]);
        $this->tpl->setVariable("LOGIN", $a_set["login"]);
        $this->tpl->setVariable("FIRSTNAME", $a_set["firstname"]);
        $this->tpl->setVariable("LASTNAME", $a_set["lastname"]);
    }
}
