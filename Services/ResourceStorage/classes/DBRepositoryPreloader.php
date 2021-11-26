<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Preloader;

use ILIAS\ResourceStorage\Stakeholder\Repository\StakeholderRepository;
use ILIAS\ResourceStorage\Resource\Repository\ResourceRepository;
use ILIAS\ResourceStorage\Revision\Repository\RevisionRepository;
use ILIAS\ResourceStorage\Information\Repository\InformationRepository;

/**
 * Class DBRepositoryPreloader
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 */
class DBRepositoryPreloader extends StandardRepositoryPreloader implements RepositoryPreloader
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    protected $preloaded = [];

    public function __construct(
        \ilDBInterface $db,
        ResourceRepository $resource_repository,
        RevisionRepository $revision_repository,
        InformationRepository $information_repository,
        StakeholderRepository $stakeholder_repository
    ) {
        $this->db = $db;
        parent::__construct(
            $resource_repository,
            $revision_repository,
            $information_repository,
            $stakeholder_repository
        );
    }

    public function preload(array $identification_strings) : void
    {
        $requested = array_diff($identification_strings, $this->preloaded);
        if (count($requested) === 0) {
            return;
        }
        $r = $this->db->query(
            "SELECT * 
FROM il_resource_revision
JOIN il_resource_info ON il_resource_revision.rid = il_resource_info.rid AND il_resource_info.version_number = il_resource_revision.version_number
JOIN il_resource ON il_resource_revision.rid = il_resource.rid
JOIN il_resource_stkh_u ON il_resource_stkh_u.rid = il_resource.rid
JOIN il_resource_stkh ON il_resource_stkh_u.stakeholder_id = il_resource_stkh.id
WHERE " . $this->db->in('il_resource_revision.rid', $requested, false, 'text')
        );
        while ($d = $this->db->fetchAssoc($r)) {
            $this->resource_repository->populateFromArray($d);
            $this->revision_repository->populateFromArray($d);
            $this->information_repository->populateFromArray($d);
            $this->stakeholder_repository->populateFromArray($d);
        }
        $this->preloaded = array_merge($this->preloaded, $identification_strings);
        $this->preloaded = array_unique($this->preloaded);
    }
}
