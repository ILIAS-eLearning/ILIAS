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
class ilSurveyAppraiseesTableGUI extends ilTable2GUI
{
    protected \ILIAS\Survey\Editing\EditingGUIRequest $edit_request;
    protected bool $raters_mode;
    protected string $fallback_url;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        bool $a_raters_mode = false,
        bool $a_may_delete_rater = false,
        ?string $a_fallback_url = null
    ) {
        global $DIC;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->raters_mode = $a_raters_mode;
        $this->fallback_url = trim($a_fallback_url);

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;

        $this->setFormName('apprform');

        $this->addColumn('', '', '1%');
        $this->addColumn($this->lng->txt("name"), 'name', '');
        $this->addColumn($this->lng->txt("login"), 'login', '');
        $this->addColumn($this->lng->txt("email"), 'email', '');

        $this->edit_request = $DIC->survey()
            ->internal()
            ->gui()
            ->editing()
            ->request();

        if (!$this->raters_mode) {
            $this->addColumn($this->lng->txt("survey_360_raters_finished"), "finished");
            $this->addColumn($this->lng->txt("survey_360_appraisee_close_table"), "closed");
            $this->addColumn($this->lng->txt("actions"));

            $this->setTitle($this->lng->txt("survey_360_appraisees"));
        } else {
            $this->addColumn($this->lng->txt("survey_360_rater_finished"), "finished");
            $this->addColumn($this->lng->txt("survey_code_url"));
            $this->addColumn($this->lng->txt("survey_360_rater_mail_sent"), "sent");

            $this->setTitle(
                $this->lng->txt("survey_360_edit_raters") . " : " .
                    ilUserUtil::getNamePresentation($this->edit_request->getAppraiseeId())
            );
        }

        $this->setRowTemplate("tpl.il_svy_svy_appraisees_row.html", "Modules/Survey");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->setDefaultOrderField("last_name");
        $this->setDefaultOrderDirection("asc");

        if (!$this->raters_mode) {
            $this->addMultiCommand('confirmAdminAppraiseesClose', $this->lng->txt('survey_360_appraisee_close_action'));
            $this->addMultiCommand('confirmDeleteAppraisees', $this->lng->txt('survey_360_remove_appraisees'));
            $this->setPrefix('appr_id');
            $this->setSelectAllCheckbox('appr_id');
        } else {
            $this->addMultiCommand('mailRaters', $this->lng->txt('mail'));
            if ($a_may_delete_rater) {
                $this->addMultiCommand('confirmDeleteRaters', $this->lng->txt('remove'));
            }
            $this->setPrefix('rtr_id');
            $this->setSelectAllCheckbox('rtr_id');
        }
    }

    protected function fillRow(array $a_set): void
    {
        $lng = $this->lng;

        if (!$this->raters_mode) {
            if ($a_set['closed']) {
                $this->tpl->setVariable("CLOSED", ilDatePresentation::formatDate(new ilDateTime($a_set['closed'], IL_CAL_UNIX)));
            } else {
                $this->tpl->setCurrentBlock("cb");
                $this->tpl->setVariable('MODE', "appr");
                $this->tpl->setVariable('ID', $a_set['user_id']);
                $this->tpl->parseCurrentBlock();
                $this->tpl->setVariable("CLOSED", "");
            }

            $this->tpl->setVariable("FINISHED", $a_set['finished']);

            $this->ctrl->setParameter($this->getParentObject(), "appr_id", $a_set["user_id"]);
            $this->tpl->setVariable("URL", $lng->txt("survey_360_edit_raters"));
            $this->tpl->setVariable("HREF", $this->ctrl->getLinkTarget($this->getParentObject(), "editRaters"));
            $this->ctrl->setParameter($this->getParentObject(), "appr_id", "");
        } else {
            $this->tpl->setVariable('MODE', "rtr");
            $this->tpl->setVariable('ID', $a_set['user_id']);
            $this->tpl->setVariable("FINISHED", $a_set['finished'] ? $lng->txt("yes") : $lng->txt("no"));

            $sent = "";
            if ($a_set["sent"]) {
                $sent = ilDatePresentation::formatDate(new ilDateTime($a_set["sent"], IL_CAL_UNIX));
            }
            $this->tpl->setVariable("MAIL_SENT", $sent);

            if ($a_set["href"] || $this->fallback_url) {
                if ($a_set["href"]) {
                    $this->tpl->setVariable("DIRECT_HREF", $a_set["href"]);
                } else {
                    $this->tpl->setVariable("DIRECT_HREF", $this->fallback_url);
                }
            } else {
                $this->tpl->setVariable("NO_HREF", "");
            }
        }

        $this->tpl->setVariable("LOGIN", $a_set['login']);
        $this->tpl->setVariable("EMAIL", $a_set['email']);
        $this->tpl->setVariable("NAME", $a_set['name']);
    }
}
