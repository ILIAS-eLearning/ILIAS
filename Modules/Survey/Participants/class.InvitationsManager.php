<?php

namespace ILIAS\Survey\Participants;

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Survey\Execution;
use ILIAS\Survey\Settings;

/**
 * Survey invitations
 *
 * @author killing@leifos.de
 */
class InvitationsManager
{
    /**
     * @var InvitationsDBRepository
     */
    protected $repo;

    /**
     * @var Execution\RunDBRepository
     */
    protected $run_repo;

    /**
     * @var Settings\SettingsDBRepository
     */
    protected $set_repo;

    /**
     * Constructor
     */
    public function __construct(InvitationsDBRepository $repo = null,
        Execution\RunDBRepository $run_repo = null, Settings\SettingsDBRepository $set_repo = null)
    {
        $this->repo = (is_null($repo))
            ? new InvitationsDBRepository()
            : $repo;

        $this->run_repo = (is_null($run_repo))
            ? new Execution\RunDBRepository()
            : $run_repo;

        $this->set_repo = (is_null($set_repo))
            ? new Settings\SettingsDBRepository()
            : $set_repo;
    }


    /**
     * Remove invitation
     *
     * @param int $survey_id Survey ID not object ID!
     * @param int $user_id
     */
    public function remove(int $survey_id, int $user_id)
    {
        $this->repo->remove($survey_id, $user_id);
    }
    
    
    /**
     * Add invitation
     *
     * @param int $survey_id Survey ID not object ID!
     * @param int $user_id
     */
    public function add(int $survey_id, int $user_id)
    {
        $this->repo->add($survey_id, $user_id);
    }

    /**
     * Get invitations for survey
     *
     * @param int $survey_id Survey ID not object ID!
     * @return int[]
     */
    public function getAllForSurvey(int $survey_id): array
    {
        return $this->repo->getAllForSurvey($survey_id);
    }

    /**
     * Get all open invitations of a user
     *
     * @param
     * @return
     */
    public function getOpenInvitationsOfUser(int $user_id)
    {
        // get all invitations
        $survey_ids = $this->repo->getAllForUser($user_id);

        // check if user started already
        $finished_surveys = $this->run_repo->getFinishedSurveysOfUser($user_id);

        $open_surveys = array_filter($survey_ids, function ($i) use ($finished_surveys) {
            return !in_array($i, $finished_surveys);
        });

        // filter all surveys that have ended
        $has_ended = $this->set_repo->hasEnded($open_surveys);
        $open_surveys = array_filter($open_surveys, function($i) use ($has_ended) {
            return !$has_ended[$i];
        });

        return $open_surveys;
    }


}