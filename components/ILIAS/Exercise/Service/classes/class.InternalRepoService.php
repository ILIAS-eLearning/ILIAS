<?php

declare(strict_types=1);

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

namespace ILIAS\Exercise;

use ILIAS\Exercise\IRSS\IRSSWrapper;
use ILIAS\Exercise\InstructionFile\InstructionFileRepository;
use ILIAS\Exercise\SampleSolution\SampleSolutionRepository;
use ILIAS\Exercise\Team\TeamDBRepository;
use ILIAS\Exercise\TutorFeedbackFile\TutorFeedbackFileRepositoryInterface;
use ILIAS\Exercise\TutorFeedbackFile\TutorFeedbackFileRepository;
use ILIAS\Exercise\TutorFeedbackFile\TutorFeedbackFileTeamRepository;
use ILIAS\Exercise\TutorFeedbackFile\TutorFeedbackZipRepository;
use ILIAS\Exercise\Settings\SettingsDBRepository;

class InternalRepoService
{
    protected IRSSWrapper $irss_wrapper;
    protected Submission\SubmissionRepository $submission_repo;
    protected static array $instance = [];

    public function __construct(
        protected InternalDataService $data,
        protected \ilDBInterface $db
    ) {
        $this->irss_wrapper = new IRSSWrapper($data);
    }

    public function settings(): SettingsDBRepository
    {
        return self::$instance["settings"] ??= new SettingsDBRepository(
            $this->db,
            $this->data
        );
    }

    public function assignment(): Assignment\RepoService
    {
        return self::$instance["assignment"] ??= new Assignment\RepoService(
            $this->data,
            $this->db
        );
    }

    public function peerReview(): PeerReview\RepoService
    {
        return self::$instance["peer_review"] ??= new PeerReview\RepoService(
            $this->irss_wrapper,
            $this->data,
            $this->db
        );
    }


    public function submission(): Submission\SubmissionRepositoryInterface
    {
        return self::$instance["submission"] ??= new Submission\SubmissionRepository(
            $this->irss_wrapper,
            $this->data,
            $this->db
        );
    }

    public function instructionFiles(): InstructionFileRepository
    {
        return self::$instance["instruction"] ??= new InstructionFileRepository(
            $this->irss_wrapper,
            $this->db
        );
    }

    public function sampleSolution(): SampleSolutionRepository
    {
        return self::$instance["sample_sol"] ??= new SampleSolutionRepository(
            $this->irss_wrapper,
            $this->db
        );
    }

    public function tutorFeedbackFile(): TutorFeedbackFileRepository
    {
        return self::$instance["tutor_feedback"] ??= new TutorFeedbackFileRepository(
            $this->irss_wrapper,
            $this->db
        );
    }

    public function tutorFeedbackFileTeam(): TutorFeedbackFileTeamRepository
    {
        return self::$instance["tutor_feedback_team"] ??= new TutorFeedbackFileTeamRepository(
            $this->irss_wrapper,
            $this->db
        );
    }

    public function tutorFeedbackZip(): TutorFeedbackZipRepository
    {
        return self::$instance["tutor_feedback_zip"] ??= new TutorFeedbackZipRepository(
            $this->irss_wrapper,
            $this->db
        );
    }

    public function team(): TeamDBRepository
    {
        return self::$instance["team"] ??= new TeamDBRepository(
            $this->db,
            $this->data
        );
    }
}
