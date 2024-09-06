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

declare(strict_types=1);

namespace ILIAS\Exercise\PeerReview\Criteria;

use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\Exercise\InternalDataService;
use ILIAS\Exercise\InternalDomainService;
use ILIAS\Exercise\InternalRepoService;
use ILIAS\Filesystem\Stream\FileStream;

class CriteriaFileManager
{
    protected \ILIAS\Exercise\PeerReview\Criteria\CriteriaFileRepository $repo;
    protected \ilLogger $log;

    public function __construct(
        InternalRepoService $repo,
        protected InternalDomainService $domain,
        protected \ilExcPeerReviewFileStakeholder $stakeholder,
        protected int $ass_id
    ) {
        $this->log = $domain->logger()->exc();
        $this->repo = $repo->peerReview()->criteriaFile();
    }

    public function deliverFileOfReview(int $giver_id, int $peer_id, int $criteria_id): void
    {
        $this->repo->deliverFileOfReview($this->ass_id, $giver_id, $peer_id, $criteria_id);
    }

    public function getStream(string $rid): FileStream
    {
        $this->repo->getStream($rid);
    }

    public function getFile(
        int $giver_id,
        int $peer_id,
        int $citeria_id
    ): ?CriteriaFile {
        return $this->repo->getFile(
            $this->ass_id,
            $giver_id,
            $peer_id,
            $citeria_id
        );
    }

    public function addFromLegacyUpload(
        array $file,
        int $giver_id,
        int $peer_id,
        int $criteria_id
    ): void {
        $this->repo->addFromLegacyUpload(
            $this->ass_id,
            $file,
            $this->stakeholder,
            $giver_id,
            $peer_id,
            $criteria_id
        );
    }

    public function delete(
        int $giver_id,
        int $peer_id,
        int $criteria_id
    ): void {
        $this->repo->delete(
            $this->ass_id,
            $this->stakeholder,
            $giver_id,
            $peer_id,
            $criteria_id
        );
    }
}
