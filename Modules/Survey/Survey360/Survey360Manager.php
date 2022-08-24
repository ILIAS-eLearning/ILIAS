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

namespace ILIAS\Survey\Survey360;

use ILIAS\Survey\Execution\RunDBRepository;
use ILIAS\Survey\Settings\SettingsDBRepository;
use ILIAS\Survey\InternalRepoService;

/**
 * @todo this should be moved to a general appraisee/appraisal manager
 * @author Alexander Killing <killing@leifos.de>
 */
class Survey360Manager
{
    protected RunDBRepository $run_repo;
    protected AppraiseeDBRepository $appr_repo;
    protected SettingsDBRepository $set_repo;

    public function __construct(
        InternalRepoService $repo_service
    ) {
        $this->run_repo = $repo_service->execution()->run();

        $this->appr_repo = new AppraiseeDBRepository();
        $this->set_repo = $repo_service->settings();
    }

    /**
     * Get open surveys for rater
     * @param int $rater_user_id
     * @return int[]
     */
    public function getOpenSurveysForRater(
        int $rater_user_id
    ): array {
        // get all appraisees of the ratier
        $appraisees = $this->appr_repo->getAppraiseesForRater($rater_user_id);

        // filter out finished appraisees
        $finished_ids = array_map(static function (array $i): string {
            return $i["survey_id"] . ":" . $i["appr_id"];
        }, $this->run_repo->getFinishedAppraiseesForRater($rater_user_id));
        $open_appraisees = array_filter($appraisees, static function (array $i) use ($finished_ids): bool {
            return !in_array($i["survey_id"] . ":" . $i["appr_id"], $finished_ids, true);
        });

        // filter out closed appraisees
        $open_surveys = array_unique(array_column($open_appraisees, "survey_id"));

        // remove closed appraisees
        $closed_appr = $this->appr_repo->getClosedAppraiseesForSurveys($open_surveys);
        $closed_appr_ids = array_map(static function (array $i): string {
            return $i["survey_id"] . ":" . $i["appr_id"];
        }, $closed_appr);

        $open_appraisees = array_filter($open_appraisees, static function (array $i) use ($closed_appr_ids): bool {
            return !in_array($i["survey_id"] . ":" . $i["appr_id"], $closed_appr_ids, true);
        });
        $open_surveys = array_unique(array_column($open_appraisees, "survey_id"));

        // filter all surveys that have ended
        $has_ended = $this->set_repo->hasEnded($open_surveys);
        $open_surveys = array_filter($open_surveys, static function (int $i) use ($has_ended): bool {
            return !($has_ended[$i] ?? false);
        });

        return $open_surveys;
    }

    /**
     * Get open surveys for rater
     * @return int[]
     */
    public function getOpenSurveysForAppraisee(
        int $appr_user_id
    ): array {
        // open surveys
        $open_surveys = $this->appr_repo->getUnclosedSurveysForAppraisee($appr_user_id);

        // filter all surveys that have ended
        $has_ended = $this->set_repo->hasEnded($open_surveys);
        $open_surveys = array_filter($open_surveys, static function (int $i) use ($has_ended): bool {
            return !($has_ended[$i] ?? false);
        });

        return $open_surveys;
    }
}
