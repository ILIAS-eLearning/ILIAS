<?php declare(strict_types = 1);

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

namespace ILIAS\Survey\Execution;

use ILIAS\Survey\InternalDomainService;
use ILIAS\Survey\InternalRepoService;
use ILIAS\Survey\Mode;

/**
 * Survey Run
 * Note: The manager should get the current user id passed.
 *       The manager also receives the current access key (code) from the session manager
 *       automatically.
 * @author Alexander Killing <killing@leifos.de>
 */
class RunManager
{
    protected RunDBRepository $repo;
    protected int $survey_id;
    protected Mode\FeatureConfig $feature_config;
    protected InternalDomainService $domain_service;
    protected \ilObjSurvey $survey;
    protected int $current_user_id;

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

    protected function getSessionManager() : SessionManager
    {
        return $this->domain_service->execution()->session(
            $this->survey,
            $this->current_user_id
        );
    }

    public function getCurrentRunId(int $appraisee = 0) : int
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
     * @throws \ilSurveyException
     * @todo: somehow this does not belong here, maybe session manager instead?
     */
    protected function checkUserParameters(
        int $user_id,
        string $code = "",
        int $appraisee = 0
    ) : void {
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


    // Get state of current run
    protected function getCurrentState(int $appraisee = 0) : int
    {
        $repo = $this->repo;
        $run_id = $this->getCurrentRunId($appraisee);
        return $repo->getState($run_id);
    }

    public function hasStarted(int $appraisee = 0) : bool
    {
        return in_array(
            $this->getCurrentState($appraisee),
            [RunDBRepository::STARTED_NOT_FINISHED, RunDBRepository::FINISHED]
        );
    }

    public function hasFinished(int $appraisee = 0) : bool
    {
        return ($this->getCurrentState($appraisee) ===
            RunDBRepository::FINISHED);
    }

    /**
     * Does code belong to current anonymous started, but not finished run?
     * Note: this method acts on the current user, but accepts the passed code
     * and does not retrieve the code from the session.
     */
    public function isCodeOfCurrentUnfinishedRun(
        string $code,
        int $appraisee_id = 0
    ) : bool {
        $repo = $this->repo;
        $code_manager = $this->domain_service->code($this->survey, $this->current_user_id);

        if ($code_manager->exists($code)) {
            $run_id = $repo->getCurrentRunId(
                $this->survey_id,
                $this->current_user_id,
                $code,
                $appraisee_id
            );
            if ($run_id > 0) {
                $state = $repo->getState($run_id);
                if ($state !== RunDBRepository::FINISHED) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return Run[]
     */
    public function getRunsForUser(
        int $user_id,
        string $code = ""
    ) : array {
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
     * Starts the survey creating a new run
     */
    public function start(
        int $appraisee_id = 0
    ) : void {
        $code = $this->getSessionManager()->getCode();
        $user_id = $this->current_user_id;
        $survey = $this->survey;

        if ($survey->getAnonymize() && (strlen($code) == 0)) {
            return;
        }
        $this->repo->add($this->survey->getSurveyId(), $user_id, $code, $appraisee_id);
    }
}
