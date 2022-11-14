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
        Repositories $repositories,
    ) {
        $this->resource_repository = $repositories->getResourceRepository();
        $this->revision_repository = $repositories->getRevisionRepository();
        $this->information_repository = $repositories->getInformationRepository();
        $this->stakeholder_repository = $repositories->getStakeholderRepository();
    }

    public function preload(array $identification_strings): void
    {
        $this->resource_repository->preload($identification_strings);
        $this->revision_repository->preload($identification_strings);
        $this->information_repository->preload($identification_strings);
        $this->stakeholder_repository->preload($identification_strings);
    }
}
