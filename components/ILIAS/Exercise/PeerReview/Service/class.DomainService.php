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

namespace ILIAS\Exercise\PeerReview;

use ILIAS\Exercise\InternalDomainService;
use ILIAS\Exercise\PeerReview\Criteria\CriteriaFileManager;
use ILIAS\Exercise\InternalRepoService;
use ILIAS\Exercise\PeerReview\Criteria\CriteriaCatalogueRetrieval;
use ILIAS\Exercise\PeerReview\Criteria\CriteriaRetrieval;

class DomainService
{
    public function __construct(
        protected InternalRepoService $repo,
        protected InternalDomainService $domain_service
    ) {
    }

    public function criteriaFile(int $ass_id): CriteriaFileManager
    {
        return new CriteriaFileManager(
            $this->repo,
            $this->domain_service,
            new \ilExcPeerReviewFileStakeholder(),
            $ass_id
        );
    }

    public function exPeerReview(\ilExAssignment $ass): ?\ilExPeerReview
    {
        if ($ass->getPeerReview()) {
            return new \ilExPeerReview($ass);
        }
        return null;
    }

    public function criteriaCatalogueRetrieval(int $exc_id): CriteriaCatalogueRetrieval
    {
        return new CriteriaCatalogueRetrieval($this->domain_service, $exc_id);
    }

    public function criteriaRetrieval(int $cat_id): CriteriaRetrieval
    {
        return new CriteriaRetrieval($this->domain_service, $cat_id);
    }

}
