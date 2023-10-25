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

use ILIAS\Exercise\IRSS\CollectionWrapper;
use ILIAS\Exercise\InstructionFile\InstructionFileRepository;
use ILIAS\Exercise\SampleSolution\SampleSolutionRepository;
use ILIAS\Exercise\Team\TeamDBRepository;
use ILIAS\Exercise\TutorFeedbackFile\TutorFeedbackFileRepositoryInterface;
use ILIAS\Exercise\TutorFeedbackFile\TutorFeedbackFileRepository;
use ILIAS\Exercise\TutorFeedbackFile\TutorFeedbackFileTeamRepository;

/**
 * Internal repo factory
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalRepoService
{
    protected CollectionWrapper $collection_wrapper;
    protected InternalDataService $data;
    protected \ilDBInterface $db;
    protected Submission\SubmissionDBRepository $submission_repo;

    public function __construct(InternalDataService $data, \ilDBInterface $db)
    {
        $this->data = $data;
        $this->db = $db;
        $this->submission_repo = new Submission\SubmissionDBRepository($db);
        $this->collection_wrapper = new CollectionWrapper($data);
    }

    public function assignment(): Assignment\RepoService
    {
        return new Assignment\RepoService(
            $this->data,
            $this->db
        );
    }

    public function submission(): Submission\SubmissionRepositoryInterface
    {
        return $this->submission_repo;
    }

    public function instructionFiles(): InstructionFileRepository
    {
        return new InstructionFileRepository(
            $this->collection_wrapper,
            $this->db
        );
    }

    public function sampleSolution(): SampleSolutionRepository
    {
        return new SampleSolutionRepository(
            $this->collection_wrapper,
            $this->db
        );
    }

    public function tutorFeedbackFile(): TutorFeedbackFileRepository
    {
        return new TutorFeedbackFileRepository(
            $this->collection_wrapper,
            $this->db
        );
    }

    public function tutorFeedbackFileTeam(): TutorFeedbackFileTeamRepository
    {
        return new TutorFeedbackFileTeamRepository(
            $this->collection_wrapper,
            $this->db
        );
    }

    public function team(): TeamDBRepository
    {
        return new TeamDBRepository(
            $this->db,
            $this->data
        );
    }

}
