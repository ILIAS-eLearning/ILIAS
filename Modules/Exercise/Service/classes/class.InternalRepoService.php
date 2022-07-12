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
