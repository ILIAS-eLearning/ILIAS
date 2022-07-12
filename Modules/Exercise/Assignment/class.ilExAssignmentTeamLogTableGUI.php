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
 * List all log entries of team
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExAssignmentTeamLogTableGUI extends ilTable2GUI
{
    protected ilExAssignmentTeam $team;
    
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilExAssignmentTeam $a_team
    ) {
        $this->team = $a_team;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $ctrl = $this->ctrl;
        
        $this->setTitle($this->lng->txt("exc_team_log"));

        $this->addColumn($this->lng->txt("date"), "tstamp");
        $this->addColumn($this->lng->txt("user"), "user");
        $this->addColumn($this->lng->txt("details"), "details");
        
        $this->setDefaultOrderField("tstamp");
        $this->setDefaultOrderDirection("desc");
                        
        $this->setRowTemplate("tpl.exc_team_log_row.html", "Modules/Exercise");
        $this->setFormAction($ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->getItems();
    }

    protected function getItems() : void
    {
        $data = array();

        foreach ($this->team->getLog() as $item) {
            $mess = "";
            switch ($item["action"]) {
                case ilExAssignmentTeam::TEAM_LOG_CREATE_TEAM:
                    $mess = "create_team";
                    break;
                
                case ilExAssignmentTeam::TEAM_LOG_ADD_MEMBER:
                    $mess = "add_member";
                    break;
                
                case ilExAssignmentTeam::TEAM_LOG_REMOVE_MEMBER:
                    $mess = "remove_member";
                    break;
                
                case ilExAssignmentTeam::TEAM_LOG_ADD_FILE:
                    $mess = "add_file";
                    break;
                
                case ilExAssignmentTeam::TEAM_LOG_REMOVE_FILE:
                    $mess = "remove_file";
                    break;
            }
            
            $details = $this->lng->txt("exc_team_log_" . $mess);
            if ($item["details"]) {
                $details = sprintf($details, $item["details"]);
            }
            
            $data[] = array(
                "tstamp" => $item["tstamp"],
                "user" => ilObjUser::_lookupFullname($item["user_id"]),
                "details" => $details
            );
        }
        
        $this->setData($data);
    }

    /**
     * @throws ilDateTimeException
     */
    protected function fillRow(array $a_set) : void
    {
        $date = ilDatePresentation::formatDate(new ilDateTime($a_set["tstamp"], IL_CAL_UNIX));
        
        $this->tpl->setVariable("TSTAMP", $date);
        $this->tpl->setVariable("TXT_USER", $a_set["user"]);
        $this->tpl->setVariable("TXT_DETAILS", $a_set["details"]);
    }
}
