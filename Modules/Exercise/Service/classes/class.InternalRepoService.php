<?php declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Exercise;

/**
 * Internal repo factory
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalRepoService
{
    protected InternalDataService $data;
    protected \ilDBInterface $db;
    protected Submission\SubmissionDBRepository $submission_repo;

    public function __construct(InternalDataService $data, \ilDBInterface $db)
    {
        $this->data = $data;
        $this->db = $db;
        $this->submission_repo = new Submission\SubmissionDBRepository($db);
    }

    public function assignment() : Assignment\RepoService
    {
        return new Assignment\RepoService(
            $this->data,
            $this->db
        );
    }

    public function submission() : Submission\SubmissionRepositoryInterface
    {
        return $this->submission_repo;
    }
}
