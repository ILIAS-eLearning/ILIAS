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
 * @author Helmut Schottmüller <ilias@aurealis.de>
 */
class ilSurveyCodesEditTableGUI extends ilTable2GUI
{
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd
    ) {
        global $DIC;

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
    
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        
        $this->addColumn($this->lng->txt("survey_code"), 'code', '');
        $this->addColumn($this->lng->txt("email"), 'email', '');
        $this->addColumn($this->lng->txt("lastname"), 'last_name', '');
        $this->addColumn($this->lng->txt("firstname"), 'first_name', '');
        $this->addColumn($this->lng->txt("mail_sent_short"), 'sent', '');
        
        $this->setRowTemplate("tpl.il_svy_svy_codes_edit_row.html", "Modules/Survey");

        $this->addCommandButton('updateCodes', $this->lng->txt('save'));
        $this->addCommandButton('codes', $this->lng->txt('cancel'));

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->setDefaultOrderField("code");
        $this->setDefaultOrderDirection("asc");
    }

    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable('ID', $a_set["id"]);
        $this->tpl->setVariable("SENT", ($a_set['sent']) ?  ' checked="checked"' : '');
        $this->tpl->setVariable("CODE", $a_set['code']);
        $this->tpl->setVariable("EMAIL", $a_set['email']);
        $this->tpl->setVariable("LAST_NAME", $a_set['last_name']);
        $this->tpl->setVariable("FIRST_NAME", $a_set['first_name']);
    }
}
