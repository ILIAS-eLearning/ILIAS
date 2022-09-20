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

use ILIAS\Survey\Participants;

/**
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilSurveyParticipantsTableGUI extends ilTable2GUI
{
    protected Participants\InvitationsManager $invitation_manager;
    protected \ILIAS\Survey\InternalService $survey_service;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilObjSurvey $a_svy
    ) {
        global $DIC;

        $this->survey_service = $DIC->survey()->internal();

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $this->invitation_manager = $this
            ->survey_service
            ->domain()
            ->participants()
            ->invitations();


        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle($lng->txt("svy_anonymous_participants_svy"));

        $this->addColumn($this->lng->txt("name"), "name");
        $this->addColumn($this->lng->txt("login"), "login");
        // $this->addColumn($this->lng->txt("gender"), "gender");
        $this->addColumn($this->lng->txt("status"), "status");

        $this->setRowTemplate("tpl.il_svy_svy_participants_row.html", "Modules/Survey/Participants");
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setDefaultOrderField("name");

        $this->getItems($a_svy);
    }

    protected function getItems(ilObjSurvey $a_svy): void
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

        foreach ($this->invitation_manager->getAllForSurvey($a_svy->getSurveyId()) as $user_id) {
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

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("NAME", $a_set["name"]);
        $this->tpl->setVariable("LOGIN", $a_set["login"]);
        $this->tpl->setVariable("STATUS", $a_set["status"]);
    }
}
