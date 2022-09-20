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
class ilSurveyCodesTableGUI extends ilTable2GUI
{
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd
    ) {
        global $DIC;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;

        $this->setFormName('codesform');

        $this->addColumn('', '', '1%');
        $this->addColumn($this->lng->txt("survey_code"), 'code', '');
        $this->addColumn($this->lng->txt("email"), 'email', '');
        $this->addColumn($this->lng->txt("lastname"), 'last_name', '');
        $this->addColumn($this->lng->txt("firstname"), 'first_name', '');
        $this->addColumn($this->lng->txt("create_date"), 'date', '');
        $this->addColumn($this->lng->txt("survey_code_used"), 'used', '');
        $this->addColumn($this->lng->txt("mail_sent_short"), 'sent', '');
        $this->addColumn($this->lng->txt("survey_code_url"));

        $this->setRowTemplate("tpl.il_svy_svy_codes_row.html", "Modules/Survey");

        $this->addMultiCommand('editCodes', $this->lng->txt('edit'));
        $this->addMultiCommand('exportCodes', $this->lng->txt('export'));
        $this->addMultiCommand('deleteCodesConfirm', $this->lng->txt('delete'));

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $button = ilSubmitButton::getInstance();
        $button->setCaption("export_all_survey_codes");
        $button->setCommand("exportAllCodes");
        $button->setOmitPreventDoubleSubmission(true);
        $this->addCommandButtonInstance($button);

        $this->setDefaultOrderField("code");
        $this->setDefaultOrderDirection("asc");

        $this->setPrefix('chb_code');
        $this->setSelectAllCheckbox('chb_code');
    }

    protected function fillRow(array $a_set): void
    {
        $lng = $this->lng;

        $this->tpl->setVariable('CB_CODE', $a_set['id']);

        // :TODO: see permalink gui
        if (($a_set['href'] ?? '') !== '') {
            $this->tpl->setCurrentBlock('url');
            $this->tpl->setVariable("URL", $lng->txt("survey_code_url_name"));
            $this->tpl->setVariable("HREF", $a_set['href']);
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("USED", ($a_set['used']) ? $lng->txt("used") : $lng->txt("not_used"));
        $this->tpl->setVariable("SENT", ($a_set['sent']) ? '&#10003;' : '');
        $this->tpl->setVariable("USED_CLASS", ($a_set['used']) ? ' smallgreen' : ' smallred');
        $this->tpl->setVariable("DATE", ilDatePresentation::formatDate(new ilDateTime($a_set['date'], IL_CAL_UNIX)));
        $this->tpl->setVariable("CODE", $a_set['code']);
        $this->tpl->setVariable("EMAIL", $a_set['email']);
        $this->tpl->setVariable("LAST_NAME", $a_set['last_name']);
        $this->tpl->setVariable("FIRST_NAME", $a_set['first_name']);
    }
}
