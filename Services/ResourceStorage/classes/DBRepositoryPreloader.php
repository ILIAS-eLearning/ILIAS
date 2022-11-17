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

use ILIAS\ResourceStorage\Repositories;
use ILIAS\ResourceStorage\Resource\Repository\FlavourMachineRepository;
use ILIAS\ResourceStorage\Resource\Repository\FlavourRepository;

/**
 * Class DBRepositoryPreloader
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 */
class DBRepositoryPreloader extends StandardRepositoryPreloader implements RepositoryPreloader
{
    protected \ilDBInterface $db;

    /**
     * @var mixed[]
     */
    protected array $preloaded = [];

    public function __construct(\ilDBInterface $db, Repositories $repositories)
    {
        $this->db = $db;
        parent::__construct($repositories);
    }

    public function preload(array $identification_strings): void
    {
        $requested = array_diff($identification_strings, $this->preloaded);
        if ($requested === []) {
            return;
        }
        $r = $this->db->query(
            "SELECT *, il_resource_revision.title AS revision_title
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
