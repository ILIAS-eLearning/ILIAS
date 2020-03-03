<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesSurvey
 */
class ilSurveyParticipantsTableGUI extends ilTable2GUI
{
    public function __construct($a_parent_obj, $a_parent_cmd, ilObjSurvey $a_svy)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
                    
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setTitle($lng->txt("svy_anonymous_participants_svy"));
        
        $this->addColumn($this->lng->txt("name"), "name");
        $this->addColumn($this->lng->txt("login"), "login");
        // $this->addColumn($this->lng->txt("gender"), "gender");
        $this->addColumn($this->lng->txt("status"), "status");
        
        $this->setRowTemplate("tpl.il_svy_svy_participants_row.html", "Modules/Survey");
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setDefaultOrderField("name");
        
        $this->getItems($a_svy);
    }
    
    protected function getItems(ilObjSurvey $a_svy)
    {
        $lng = $this->lng;
        
        $data = array();
                        
        foreach ($a_svy->getSurveyParticipants(null, true) as $user) {
            if ($user["finished"]) {
                $status = $lng->txt("survey_results_finished");
            } else {
                $status = $lng->txt("survey_results_started");
            }
            
            $data[$user["login"]] = array(
                "name" => $user["sortname"],
                "login" => $user["login"],
                "status" => $status
            );
        }
        
        foreach ($a_svy->getInvitedUsers() as $user_id) {
            $user = ilObjUser::_lookupName($user_id);
            if ($user["login"] &&
                !array_key_exists($user["login"], $data)) {
                $data[$user["login"]] = array(
                    "name" => $user["lastname"] . ", " . $user["firstname"],
                    "login" => $user["login"],
                    "status" => $lng->txt("survey_results_not_started")
                );
            }
        }
        
        $this->setData($data);
    }

    public function fillRow($a_set)
    {
        $this->tpl->setVariable("NAME", $a_set["name"]);
        $this->tpl->setVariable("LOGIN", $a_set["login"]);
        $this->tpl->setVariable("STATUS", $a_set["status"]);
    }
}
