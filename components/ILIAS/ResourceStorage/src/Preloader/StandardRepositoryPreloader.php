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

namespace ILIAS\ResourceStorage\Preloader;

use ILIAS\ResourceStorage\Resource\Repository\ResourceRepository;
use ILIAS\ResourceStorage\Revision\Repository\RevisionRepository;
use ILIAS\ResourceStorage\Information\Repository\InformationRepository;
use ILIAS\ResourceStorage\Stakeholder\Repository\StakeholderRepository;
use ILIAS\ResourceStorage\Repositories;
use ILIAS\ResourceStorage\Resource\Repository\FlavourRepository;
use ILIAS\ResourceStorage\Collection\Repository\CollectionRepository;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Class StandardRepositoryPreloader
 * @author Fabian Schmid <fabian@sr.solutions.ch>
 */
class StandardRepositoryPreloader implements RepositoryPreloader
{
    protected ResourceRepository $resource_repository;
    protected RevisionRepository $revision_repository;
    protected InformationRepository $information_repository;
    protected StakeholderRepository $stakeholder_repository;
    protected FlavourRepository $flavour_repository;
    protected CollectionRepository $collection_repository;

    public function __construct(Repositories $repositories)
    {
        $this->resource_repository = $repositories->getResourceRepository();
        $this->revision_repository = $repositories->getRevisionRepository();
        $this->information_repository = $repositories->getInformationRepository();
        $this->stakeholder_repository = $repositories->getStakeholderRepository();
        $this->flavour_repository = $repositories->getFlavourRepository();
        $this->collection_repository = $repositories->getCollectionRepository();
    }

    public function preload(array $identification_strings): void
    {
        $this->resource_repository->preload($identification_strings);
        $this->revision_repository->preload($identification_strings);
        $this->information_repository->preload($identification_strings);
        $this->stakeholder_repository->preload($identification_strings);
        $this->flavour_repository->preload($identification_strings);
    }

    /**
     * @param string[] $collection_identification_strings
     */
    public function preloadCollections(array $collection_identification_strings): void
    {
        if ($collection_identification_strings === []) {
            return;
        }
        $resource_ids = $this->collection_repository->getResourceIdsForCollections($collection_identification_strings);
        $resource_ids = array_values(array_unique(
            array_map(static fn(ResourceIdentification $id) => $id->serialize(), $resource_ids)
        ));

        if ($resource_ids === []) {
            return;
        }

        $this->preload($resource_ids);
    }
}
