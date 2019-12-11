<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Survey\Survey360;

use ILIAS\Survey\Execution\RunDBRepository;
use ILIAS\Survey\Settings\SettingsDBRepository;

/**
 * Survey 360
 *
 * @author killing@leifos.de
 */
class Survey360Manager
{
    /**
     * @var RunDBRepository
     */
    protected $run_repo;

    /**
     * @var AppraiseeDBRepository
     */
    protected $appr_repo;

    /**
     * @var SettingsDBRepository
     */
    protected $set_repo;

    /**
     * Constructor
     */
    public function __construct(
        AppraiseeDBRepository $appr_repo = null,
        RunDBRepository $run_rep = null,
        SettingsDBRepository $set_repo = null
    ) {
        $this->run_repo = (is_null($run_rep))
            ? new RunDBRepository()
            : $run_rep;

        $this->appr_repo = (is_null($appr_repo))
            ? new AppraiseeDBRepository()
            : $appr_repo;

        $this->set_repo = (is_null($set_repo))
            ? new SettingsDBRepository()
            : $set_repo;
    }

    /**
     * Get open surveys for rater
     *
     * @param int $rater_user_id
     * @return int[]
     */
    public function getOpenSurveysForRater(int $rater_user_id)
    {
        // get all appraisees of the ratier
        $appraisees = $this->appr_repo->getAppraiseesForRater($rater_user_id);

        // filter out finished appraisees
        $finished_ids = array_map(function ($i) {
            return $i["survey_id"] . ":" . $i["appr_id"];
        }, $this->run_repo->getFinishedAppraiseesForRater($rater_user_id));
        $open_appraisees = array_filter($appraisees, function ($i) use ($finished_ids) {
            return !in_array($i["survey_id"] . ":" . $i["appr_id"], $finished_ids);
        });

        // filter out closed appraisees
        $open_surveys = array_unique(array_column($open_appraisees, "survey_id"));

        // remove closed appraisees
        $closed_appr = $this->appr_repo->getClosedAppraiseesForSurveys($open_surveys);
        $closed_appr_ids = array_map(function ($i) {
            return $i["survey_id"] . ":" . $i["appr_id"];
        }, $closed_appr);

        $open_appraisees = array_filter($open_appraisees, function ($i) use ($closed_appr_ids) {
            return !in_array($i["survey_id"] . ":" . $i["appr_id"], $closed_appr_ids);
        });
        $open_surveys = array_unique(array_column($open_appraisees, "survey_id"));

        // filter all surveys that have ended
        $has_ended = $this->set_repo->hasEnded($open_surveys);
        $open_surveys = array_filter($open_surveys, function ($i) use ($has_ended) {
            return !$has_ended[$i];
        });

        return $open_surveys;
    }

    /**
     * Get open surveys for rater
     *
     * @param int $rater_user_id
     * @return int[]
     */
    public function getOpenSurveysForAppraisee(int $appr_user_id)
    {
        // open surveys
        $open_surveys = $this->appr_repo->getUnclosedSurveysForAppraisee($appr_user_id);

        // filter all surveys that have ended
        $has_ended = $this->set_repo->hasEnded($open_surveys);
        $open_surveys = array_filter($open_surveys, function ($i) use ($has_ended) {
            return !$has_ended[$i];
        });

        return $open_surveys;
    }
}
