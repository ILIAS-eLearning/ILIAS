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

namespace ILIAS\Survey\Tasks;

use ILIAS\Survey\Participants\InvitationsManager;
use ILIAS\Survey\Settings\SettingsDBRepository;
use ILIAS\Survey\Survey360\Survey360Manager;

/**
 * Exercise derived task provider
 * @author Alexander Killing <killing@leifos.de>
 */
class DerivedTaskProvider implements \ilDerivedTaskProvider
{
    protected \ilTaskService $task_service;
    protected \ilAccessHandler $access;
    protected \ilLanguage $lng;
    protected InvitationsManager $inv_manager;
    protected SettingsDBRepository $set_repo;
    protected Survey360Manager $svy_360_manager;

    public function __construct(
        \ilTaskService $task_service,
        \ilAccess $access,
        \ilLanguage $lng
    ) {
        global $DIC;

        $survey_service = $DIC->survey()->internal();

        $this->access = $access;
        $this->task_service = $task_service;
        $this->lng = $lng;

        $this->lng->loadLanguageModule("svy");

        $this->inv_manager = $survey_service
            ->domain()
            ->participants()
            ->invitations();

        $this->set_repo = $survey_service->repo()->settings();
        $this->svy_360_manager = new Survey360Manager(
            $survey_service->repo()
        );
    }

    public function isActive(): bool
    {
        return true;
    }

    public function getTasks(int $user_id): array
    {
        $lng = $this->lng;

        $tasks = [];

        // open assignments
        $survey_ids = $this->inv_manager->getOpenInvitationsOfUser($user_id);
        if (count($survey_ids) > 0) {
            $obj_ids = $this->set_repo->getObjIdsForSurveyIds($survey_ids);
            $access = $this->set_repo->getAccessSettings($survey_ids);
            foreach ($obj_ids as $survey_id => $obj_id) {
                $ref_id = $this->getFirstRefIdWithPermission("read", $obj_id, $user_id);
                if ($ref_id > 0) {
                    $title = str_replace("%1", \ilObject::_lookupTitle($obj_id), $lng->txt("svy_finish_survey"));
                    $tasks[] = $this->task_service->derived()->factory()->task(
                        $title,
                        $ref_id,
                        $access[$survey_id]->getEndDate(),
                        $access[$survey_id]->getStartDate()
                    );
                }
            }
        }

        // open raters in 360
        $survey_ids = $this->svy_360_manager->getOpenSurveysForRater($user_id);
        if (count($survey_ids) > 0) {
            $obj_ids = $this->set_repo->getObjIdsForSurveyIds($survey_ids);
            $access = $this->set_repo->getAccessSettings($survey_ids);
            foreach ($obj_ids as $survey_id => $obj_id) {
                $ref_id = $this->getFirstRefIdWithPermission("read", $obj_id, $user_id);
                if ($ref_id > 0) {
                    $title = str_replace("%1", \ilObject::_lookupTitle($obj_id), $lng->txt("svy_finish_survey"));
                    $tasks[] = $this->task_service->derived()->factory()->task(
                        $title,
                        $ref_id,
                        $access[$survey_id]->getEndDate(),
                        $access[$survey_id]->getStartDate()
                    );
                }
            }
        }

        // unclosed 360 survey of appraisee
        $survey_ids = $this->svy_360_manager->getOpenSurveysForAppraisee($user_id);
        if (count($survey_ids) > 0) {
            $obj_ids = $this->set_repo->getObjIdsForSurveyIds($survey_ids);
            $access = $this->set_repo->getAccessSettings($survey_ids);
            foreach ($obj_ids as $survey_id => $obj_id) {
                $ref_id = $this->getFirstRefIdWithPermission("read", $obj_id, $user_id);
                if ($ref_id > 0) {
                    $title = str_replace("%1", \ilObject::_lookupTitle($obj_id), $lng->txt("svy_finish_survey"));
                    $tasks[] = $this->task_service->derived()->factory()->task(
                        $title,
                        $ref_id,
                        $access[$survey_id]->getEndDate(),
                        $access[$survey_id]->getStartDate()
                    );
                }
            }
        }

        return $tasks;
    }


    /**
     * Get first ref id for an object id with permission
     */
    protected function getFirstRefIdWithPermission(
        string $perm,
        int $obj_id,
        int $user_id
    ): int {
        $access = $this->access;

        foreach (\ilObject::_getAllReferences($obj_id) as $ref_id) {
            if ($access->checkAccessOfUser($user_id, $perm, "", $ref_id)) {
                return $ref_id;
            }
        }
        return 0;
    }
}
