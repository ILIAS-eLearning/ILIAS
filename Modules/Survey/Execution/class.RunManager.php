<?php declare(strict_types = 1);

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
    protected \ILIAS\Survey\Code\CodeManager $code_manager;
    protected RunSessionRepo $session_repo;
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
        $this->session_repo = $repo_service->execution()->runSession();
        $this->code_manager = $domain_service->code($survey, $current_user_id);
    }

    protected function codeNeeded() : bool
    {
        return !$this->survey->isAccessibleWithoutCode();
    }

    public function getCurrentRunId(int $appraisee = 0) : int
    {
        $repo = $this->repo;
        $survey_id = $this->survey_id;
        $user_id = $this->current_user_id;
        $code = $this->getCode();

        $this->checkUserParameters($user_id, $code, $appraisee);

        // code needed, no code given -> no run
        if ($code === "" && $this->codeNeeded()) {
            return 0;
        }

        $run_id = $repo->getCurrentRunId($survey_id, $user_id, $code, $appraisee);
        return (int) $run_id;
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
        if ($this->feature_config->usesAppraisees() && $appraisee === 0) {
            throw new \ilSurveyException("No appraisee specified");
        }

        if (!$this->feature_config->usesAppraisees() && $appraisee > 0) {
            throw new \ilSurveyException("Appraisee ID given, but appraisees not supported");
        }

        /* this fails on the info screen
        if ($user_id === ANONYMOUS_USER_ID && $code === "") {
            throw new \ilSurveyException("Code missing for anonymous user.");
        }*/
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
            [RunDBRepository::STARTED_NOT_FINISHED, RunDBRepository::FINISHED],
            true
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
    public function belongsToFinishedRun(
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
                if ($state === RunDBRepository::FINISHED) {
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
        if (!is_null($run) && $run->getSurveyId() !== $this->survey->getSurveyId()) {
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
        $code = $this->getCode();
        $user_id = $this->current_user_id;
        $survey = $this->survey;

        if ($survey->getAnonymize() && $code === '') {
            return;
        }
        $this->repo->add($this->survey->getSurveyId(), $user_id, $code, $appraisee_id);
    }

    public function initSession(
        string $requested_code = ""
    ) : void {
        $user_id = $this->current_user_id;
        $survey = $this->survey;
        $session_repo = $this->session_repo;
        // validate incoming
        $code_input = false;
        // ->requested_code
        $anonymous_code = $requested_code;
        if ($anonymous_code !== "") {
            $code_input = true;
            if ($this->belongsToFinishedRun($anonymous_code)) { // #15031 - valid as long survey is not finished
                $anonymous_code = "";
            } else {
                // #15860
                // a user has used a valid code, we store this in table
                // svy_anonymous
                $this->code_manager->bindUser($anonymous_code, $user_id);
                $session_repo->setCode($survey->getId(), $anonymous_code);
            }
        }
        // now we try to get the code from the session
        if (!$anonymous_code) {
            $anonymous_code = $session_repo->getCode($survey->getId());
            if ($anonymous_code) {
                $code_input = true;     // ??
            }
        }

        // if the survey is anonymous, codes are stored for logged
        // in users in svy_finished. Here we get this code, if already stored
        if ($survey->getAnonymize() && !$anonymous_code) {
            $anonymous_code = $survey->findCodeForUser($user_id);
        }

        // get existing runs for current user, might generate code
        $execution_status = $survey->getUserSurveyExecutionStatus($anonymous_code);
        if ($execution_status) {
            $anonymous_code = (string) $execution_status["code"];
            $execution_status = $execution_status["runs"];
        }

        // (final) check for proper anonymous code
        if (!$survey->isAccessibleWithoutCode() &&
//          !$is_appraisee &&
            $code_input && // #11346
            (!$anonymous_code || !$this->code_manager->exists($anonymous_code))) {
            $anonymous_code = "";
            throw new \ilWrongSurveyCodeException("Wrong Survey Code used.");
        }
        $this->session_repo->setCode($survey->getId(), $anonymous_code);
    }

    /**
     * Get current valid code
     */
    public function getCode() : string
    {
        return $this->session_repo->getCode($this->survey->getId());
    }

    public function clearCode() : void
    {
        $this->session_repo->clearCode($this->survey->getId());
    }

    /**
     * Set start time of run
     */
    public function setStartTime(
        int $first_question
    ) : void {
        $run_id = $this->getCurrentRunId();
        $time = time();
        $this->session_repo->setPageEnter($time);
        $this->repo->addTime($run_id, $time, $first_question);
    }

    public function setEndTime() : void
    {
        $run_id = $this->getCurrentRunId();
        $time = time();
        $this->repo->updateTime($run_id, $time, $this->session_repo->getPageEnter());
        $this->session_repo->clearPageEnter();
    }

    public function getPageEnter() : int
    {
        return $this->session_repo->getPageEnter();
    }

    public function setPreviewData(int $question_id, array $data) : void
    {
        $this->session_repo->setPreviewData($this->survey_id, $question_id, $data);
    }

    public function getPreviewData(int $question_id) : array
    {
        return $this->session_repo->getPreviewData($this->survey_id, $question_id);
    }

    public function clearPreviewData(int $question_id) : void
    {
        $this->session_repo->clearPreviewData($this->survey_id, $question_id);
    }

    public function clearAllPreviewData() : void
    {
        $this->session_repo->clearAllPreviewData($this->survey_id);
    }

    public function setErrors(array $errors) : void
    {
        $this->session_repo->setErrors($errors);
    }

    public function getErrors() : array
    {
        return $this->session_repo->getErrors();
    }

    public function clearErrors() : void
    {
        $this->session_repo->clearErrors();
    }

    public function setPostData(array $data) : void
    {
        $this->session_repo->setPostData($data);
    }

    public function getPostData() : array
    {
        return $this->session_repo->getPostData();
    }

    public function clearPostData() : void
    {
        $this->session_repo->clearPostData();
    }
}
