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

namespace ILIAS\ResourceStorage;

use ILIAS\ResourceStorage\Collection\Repository\CollectionRepository;
use ILIAS\ResourceStorage\Information\Repository\InformationRepository;
use ILIAS\ResourceStorage\Preloader\RepositoryPreloader;
use ILIAS\ResourceStorage\Resource\Repository\FlavourRepository;
use ILIAS\ResourceStorage\Resource\Repository\ResourceRepository;
use ILIAS\ResourceStorage\Revision\Repository\RevisionRepository;
use ILIAS\ResourceStorage\Stakeholder\Repository\StakeholderRepository;

/**
 * Class Repositories
 * @internal
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Repositories
{
    public function __construct(
        private RevisionRepository $revision_repository,
        private ResourceRepository $resource_repository,
        private CollectionRepository $collection_repository,
        private InformationRepository $information_repository,
        private StakeholderRepository $stakeholder_repository
    ) {
    }

    public function getRevisionRepository(): RevisionRepository
    {
        return $this->revision_repository;
    }

    public function getResourceRepository(): ResourceRepository
    {
        return $this->resource_repository;
    }

    public function getCollectionRepository(): CollectionRepository
    {
        return $this->collection_repository;
    }

    public function getInformationRepository(): InformationRepository
    {
        return $this->information_repository;
    }

    public function getStakeholderRepository(): StakeholderRepository
    {
        return $this->stakeholder_repository;
    }
}
