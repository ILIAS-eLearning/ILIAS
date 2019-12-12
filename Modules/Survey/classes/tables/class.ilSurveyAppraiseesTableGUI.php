<?php
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
*/

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
* @ingroup ModulesSurvey
*/

class ilSurveyAppraiseesTableGUI extends ilTable2GUI
{
    protected $raters_mode; // [bool]
    protected $fallback_url; // [string]
    
    /**
     * Constructor
     *
     * @access public
     * @param
     * @return
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_raters_mode = false, $a_may_delete_rater = false, $a_fallback_url = null)
    {
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
            
            include_once "Services/User/classes/class.ilUserUtil.php";
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

    /**
     * fill row
     *
     * @access public
     * @param
     * @return
     */
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
