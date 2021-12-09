<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Survey\Execution;

use ILIAS\Survey\InternalDomainService;
use ILIAS\Survey\InternalRepoService;
use ILIAS\Survey\Mode;

/**
 * Survey Runs
 * @author killing@leifos.de
 */
class RunManager
{
    /**
     * @var RunDBRepository
     */
    protected $repo;

    /**
     * @var int
     */
    protected $survey_id;

    /**
     * @var Mode\FeatureConfig
     */
    protected $feature_config;

    /**
     * @var InternalDomainService
     */
    protected $domain_service;

    /**
     * @var \ilObjSurvey
     */
    protected $survey;

    /**
     * @var int
     */
    protected $current_user_id;

    /**
     * Constructor
     */
    public function __construct(
        InternalRepoService $repo_service,
        InternalDomainService $domain_service,
        \ilObjSurvey $survey,
        int $current_user_id
    ) {
        $this->repo = $repo_service->execution()->run();

        $this->survey_id = $survey->getSurveyId();
        $this->survey = $survey;
        $this->feature_config = $domain_service->modeFeatureConfig($survey->getMode());
        $this->domain_service = $domain_service;
        $this->current_user_id = $current_user_id;
    }

    /**
     * @return SessionManager
     */
    protected function getSessionManager() : SessionManager
    {
        return $this->domain_service->execution()->session(
            $this->survey,
            $this->current_user_id
        );
    }

    public function getCurrentRunId($appraisee = 0)
    {
        $repo = $this->repo;
        $survey_id = $this->survey_id;
        $user_id = $this->current_user_id;
        $code = $this->getSessionManager()->getCode();

        $this->checkUserParameters($user_id, $code, $appraisee);

        $run_id = $repo->getCurrentRunId($survey_id, $user_id, $code, $appraisee);
        return $run_id;
    }

    /**
     * Check user parameters
     * @param int    $user_id
     * @param string $code
     * @param int    $appraisee
     * @throws \ilSurveyException
     */
    protected function checkUserParameters(int $user_id, string $code = "", $appraisee = 0) : void
    {
        if ($this->feature_config->usesAppraisees() && $appraisee == 0) {
            throw new \ilSurveyException("No appraisee specified");
        }

        if (!$this->feature_config->usesAppraisees() && $appraisee > 0) {
            throw new \ilSurveyException("Appraisee ID given, but appraisees not supported");
        }

        if ($user_id == ANONYMOUS_USER_ID && $code == "") {
            throw new \ilSurveyException("Code missing for anonymous user.");
        }
    }


    protected function getCurrentState(int $user_id, string $code = "", $appraisee = 0)
    {
        $repo = $this->repo;
        $survey_id = $this->survey_id;

        $run_id = (int) $this->getCurrentRunId($appraisee);

        return $repo->getState($run_id);
    }

    public function hasStarted(int $user_id, string $code = "", $appraisee = 0)
    {
        return in_array(
            $this->getCurrentState($user_id, $code, $appraisee),
            [RunDBRepository::STARTED_NOT_FINISHED, RunDBRepository::FINISHED]
        );
    }

    public function hasFinished(int $user_id, string $code = "", $appraisee = 0)
    {
        return ($this->getCurrentState($user_id, $code, $appraisee) ===
            RunDBRepository::FINISHED);
    }

    // does code belong to current anonymous started, but not finished run?
    public function isCodeOfCurrentUnfinishedRun(string $code) : bool
    {
        $code_manager = $this->domain_service->code($this->survey, $this->current_user_id);
        if ($code_manager->exists($code)) {
            return (!$this->hasFinished(0, $code));
        }
        return false;
    }

    /**
     * @param int    $user_id
     * @param string $code
     * @return Run[]
     */
    public function getRunsForUser(int $user_id, string $code = "") : array
    {
        return $this->repo->getRunsForUser($this->survey->getSurveyId(), $user_id, $code);
    }

    public function getById(int $run_id) : ?Run
    {
        $run = $this->repo->getById($run_id);
        if (!is_null($run) && $run->getSurveyId() != $this->survey->getSurveyId()) {
            throw new \ilSurveyException("Run survey id mismatch.");
        }
        return $run;
    }

    /**
     * Starts the survey creating an entry in the database
     *
     * @param integer $user_id The database id of the user who starts the survey
     * @access public
     */
    public function start(int $user_id, string $anonymous_id, int $appraisee_id = 0) : void
    {
        $survey = $this->survey;

        if ($survey->getAnonymize() && (strlen($anonymous_id) == 0)) {
            return;
        }

        /*
        if (strcmp($user_id, "") == 0) {
            if ($user_id == ANONYMOUS_USER_ID) {
                $user_id = 0;
            }
        }*/
        $run_id = $this->repo->add($this->survey->getSurveyId(), $user_id, $anonymous_id, $appraisee_id);
    }
}
