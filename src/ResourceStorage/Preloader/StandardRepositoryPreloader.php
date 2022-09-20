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
 *********************************************************************/

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
    protected \ILIAS\ResourceStorage\Resource\Repository\ResourceRepository $resource_repository;
    protected \ILIAS\ResourceStorage\Revision\Repository\RevisionRepository $revision_repository;
    protected \ILIAS\ResourceStorage\Information\Repository\InformationRepository $information_repository;
    protected \ILIAS\ResourceStorage\Stakeholder\Repository\StakeholderRepository $stakeholder_repository;

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

    public function preload(array $identification_strings): void
    {
        $this->resource_repository->preload($identification_strings);
        $this->revision_repository->preload($identification_strings);
        $this->information_repository->preload($identification_strings);
        $this->stakeholder_repository->preload($identification_strings);
    }
}
