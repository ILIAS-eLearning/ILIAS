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

namespace ILIAS\Survey\Participants;

use ILIAS\Survey\Execution;
use ILIAS\Survey\Settings;
use ILIAS\Survey\InternalRepoService;

/**
 * Survey invitations
 * @author Alexander Killing <killing@leifos.de>
 */
class InvitationsManager
{
    protected InvitationsDBRepository $repo;
    protected Execution\RunDBRepository $run_repo;
    protected Settings\SettingsDBRepository $set_repo;

    public function __construct(
        InternalRepoService $repo_service
    ) {
        $this->repo = $repo_service->participants()->invitations();
        $this->run_repo = $repo_service->execution()->run();
        $this->set_repo = $repo_service->settings();
    }

    /**
     * Remove invitation
     *
     * @param int $survey_id Survey ID not object ID!
     * @param int $user_id
     */
    public function remove(
        int $survey_id,
        int $user_id
    ) : void {
        $this->repo->remove($survey_id, $user_id);
    }
    
    
    /**
     * Add invitation
     *
     * @param int $survey_id Survey ID not object ID!
     * @param int $user_id
     */
    public function add(
        int $survey_id,
        int $user_id
    ) : void {
        $this->repo->add($survey_id, $user_id);
    }

    /**
     * Get invitations for survey
     * @param int $survey_id Survey ID not object ID!
     * @return int[]
     */
    public function getAllForSurvey(
        int $survey_id
    ) : array {
        return $this->repo->getAllForSurvey($survey_id);
    }

    /**
     * Get all open invitations of a user
     * @return int[] survey ids
     */
    public function getOpenInvitationsOfUser(
        int $user_id
    ) : array {
        // get all invitations
        $survey_ids = $this->repo->getAllForUser($user_id);

        // check if user started already
        $finished_surveys = $this->run_repo->getFinishedSurveysOfUser($user_id);

        $open_surveys = array_filter($survey_ids, static function (int $i) use ($finished_surveys) {
            return !in_array($i, $finished_surveys, true);
        });

        // filter all surveys that have ended
        $has_ended = $this->set_repo->hasEnded($open_surveys);
        $open_surveys = array_filter($open_surveys, static function (int $i) use ($has_ended) : bool {
            return !($has_ended[$i] ?? false);
        });

        return $open_surveys;
    }
}
