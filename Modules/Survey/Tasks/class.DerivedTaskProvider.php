<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Survey\Tasks;

use ILIAS\Survey\Participants\InvitationsManager;
use ILIAS\Survey\Settings\SettingsDBRepository;
use ILIAS\Survey\Survey360\Survey360Manager;

/**
 * Exercise derived task provider
 *
 * @author @leifos.de
 * @ingroup ModulesExercise
 */
class DerivedTaskProvider implements \ilDerivedTaskProvider
{
    /**
     * @var \ilTaskService
     */
    protected $task_service;

    /**
     * @var \ilAccess
     */
    protected $access;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var InvitationsManager
     */
    protected $inv_manager;

    /**
     * @var SettingsDBRepository
     */
    protected $set_repo;

    /**
     * @var Survey360Manager
     */
    protected $svy_360_manager;

    /**
     * Constructor
     */
    public function __construct(\ilTaskService $task_service, \ilAccess $access, \ilLanguage $lng)
    {
        $this->access = $access;
        $this->task_service = $task_service;
        $this->lng = $lng;

        $this->lng->loadLanguageModule("svy");

        $this->inv_manager = new InvitationsManager();
        $this->set_repo = new SettingsDBRepository();
        $this->svy_360_manager = new Survey360Manager();
    }

    /**
     * @inheritdoc
     */
    public function isActive() : bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getTasks(int $user_id) : array
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
                        (int) $access[$survey_id]->getEndDate(),
                        (int) $access[$survey_id]->getStartDate()
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
                        (int) $access[$survey_id]->getEndDate(),
                        (int) $access[$survey_id]->getStartDate()
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
                        (int) $access[$survey_id]->getEndDate(),
                        (int) $access[$survey_id]->getStartDate()
                    );
                }
            }
        }

        return $tasks;
    }


    /**
     * Get first ref id for an object id with permission
     *
     * @param int $obj_id
     * @param int $user_id
     * @return int
     */
    protected function getFirstRefIdWithPermission($perm, int $obj_id, int $user_id) : int
    {
        $access = $this->access;

        foreach (\ilObject::_getAllReferences($obj_id) as $ref_id) {
            if ($access->checkAccessOfUser($user_id, $perm, "", $ref_id)) {
                return $ref_id;
            }
        }
        return 0;
    }
}
