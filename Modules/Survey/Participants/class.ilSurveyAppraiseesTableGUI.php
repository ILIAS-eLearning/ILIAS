<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * @author Helmut Schottmüller <ilias@aurealis.de>
 */
class ilSurveyAppraiseesTableGUI extends ilTable2GUI
{
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
        
        $this->raters_mode = (bool) $a_raters_mode;
        $this->fallback_url = trim($a_fallback_url);

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        
        $this->setFormName('apprform');
        
        $this->addColumn('', '', '1%');
        $this->addColumn($this->lng->txt("name"), 'name', '');
        $this->addColumn($this->lng->txt("login"), 'login', '');
        $this->addColumn($this->lng->txt("email"), 'email', '');
                        
        if (!$this->raters_mode) {
            $this->addColumn($this->lng->txt("survey_360_raters_finished"), "finished");
            $this->addColumn($this->lng->txt("survey_360_appraisee_close_table"), "closed");
            $this->addColumn($this->lng->txt("actions"));
            
            $this->setTitle($this->lng->txt("survey_360_appraisees"));
        } else {
            $this->addColumn($this->lng->txt("survey_360_rater_finished"), "finished");
            $this->addColumn($this->lng->txt("survey_code_url"));
            $this->addColumn($this->lng->txt("survey_360_rater_mail_sent"), "sent");
            
            $this->setTitle($this->lng->txt("survey_360_edit_raters") . " : " .
                ilUserUtil::getNamePresentation($_REQUEST["appr_id"]));
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

    public function fillRow($data)
    {
        $lng = $this->lng;
                
        if (!$this->raters_mode) {
            if ($data['closed']) {
                $this->tpl->setVariable("CLOSED", ilDatePresentation::formatDate(new ilDateTime($data['closed'], IL_CAL_UNIX)));
            } else {
                $this->tpl->setCurrentBlock("cb");
                $this->tpl->setVariable('MODE', $this->raters_mode ? "rtr" : "appr");
                $this->tpl->setVariable('ID', $data['user_id']);
                $this->tpl->parseCurrentBlock();
                $this->tpl->setVariable("CLOSED", "");
            }

            $this->tpl->setVariable("FINISHED", $data['finished']);

            $this->ctrl->setParameter($this->getParentObject(), "appr_id", $data["user_id"]);
            $this->tpl->setVariable("URL", $lng->txt("survey_360_edit_raters"));
            $this->tpl->setVariable("HREF", $this->ctrl->getLinkTarget($this->getParentObject(), "editRaters"));
            $this->ctrl->setParameter($this->getParentObject(), "appr_id", "");
        } else {
            $this->tpl->setVariable('MODE', $this->raters_mode ? "rtr" : "appr");
            $this->tpl->setVariable('ID', $data['user_id']);
            $this->tpl->setVariable("FINISHED", $data['finished'] ? $lng->txt("yes") : $lng->txt("no"));
            
            $sent = "";
            if ($data["sent"]) {
                $sent = ilDatePresentation::formatDate(new ilDateTime($data["sent"], IL_CAL_UNIX));
            }
            $this->tpl->setVariable("MAIL_SENT", $sent);
            
            if ($data["href"] || $this->fallback_url) {
                if ($data["href"]) {
                    $this->tpl->setVariable("DIRECT_HREF", $data["href"]);
                } else {
                    $this->tpl->setVariable("DIRECT_HREF", $this->fallback_url);
                }
            } else {
                $this->tpl->setVariable("NO_HREF", "");
            }
        }

        $this->tpl->setVariable("LOGIN", $data['login']);
        $this->tpl->setVariable("EMAIL", $data['email']);
        $this->tpl->setVariable("NAME", $data['name']);
    }
}
