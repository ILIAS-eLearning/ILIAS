<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Preloader;

use ILIAS\ResourceStorage\Resource\Repository\ResourceRepository;
use ILIAS\ResourceStorage\Revision\Repository\RevisionRepository;
use ILIAS\ResourceStorage\Information\Repository\InformationRepository;
use ILIAS\ResourceStorage\Stakeholder\Repository\StakeholderRepository;

/**
 * Class StandardRepositoryPreloader
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StandardRepositoryPreloader implements RepositoryPreloader
{
    /**
     * @var ResourceRepository
     */
    protected $resource_repository;
    /**
     * @var RevisionRepository
     */
    protected $revision_repository;
    /**
     * @var InformationRepository
     */
    protected $information_repository;
    /**
     * @var StakeholderRepository
     */
    protected $stakeholder_repository;

    /**
     * @param ResourceRepository    $resource_repository
     * @param RevisionRepository    $revision_repository
     * @param InformationRepository $information_repository
     * @param StakeholderRepository $stakeholder_repository
     */
    public function __construct(
        ResourceRepository $resource_repository,
        RevisionRepository $revision_repository,
        InformationRepository $information_repository,
        StakeholderRepository $stakeholder_repository
    ) {
        $this->resource_repository = $resource_repository;
        $this->revision_repository = $revision_repository;
        $this->information_repository = $information_repository;
        $this->stakeholder_repository = $stakeholder_repository;
    }

    public function preload(array $identification_strings) : void
    {
        $this->resource_repository->preload($identification_strings);
        $this->revision_repository->preload($identification_strings);
        $this->information_repository->preload($identification_strings);
        $this->stakeholder_repository->preload($identification_strings);
    }
}
