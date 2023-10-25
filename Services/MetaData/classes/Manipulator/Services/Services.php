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

namespace ILIAS\MetaData\Manipulator\Services;

use ILIAS\MetaData\Paths\Services\Services as PathServices;
use ILIAS\MetaData\Repository\Services\Services as RepositoryServices;
use ILIAS\MetaData\Manipulator\Path\PathUtilitiesFactory;
use ILIAS\MetaData\Elements\Markers\MarkerFactory;
use ILIAS\MetaData\Manipulator\ManipulatorInterface;
use ILIAS\MetaData\Manipulator\Manipulator;

class Services
{
    protected ManipulatorInterface $manipulator;
    protected PathServices $path_services;
    protected RepositoryServices $repository_services;

    public function __construct(
        PathServices $path_services,
        RepositoryServices $repository_services
    ) {
        $this->path_services = $path_services;
        $this->repository_services = $repository_services;
    }

    public function manipulator(): ManipulatorInterface
    {
        if (isset($this->manipulator)) {
            return $this->manipulator;
        }
        return $this->manipulator = new Manipulator(
            $this->repository_services->repository(),
            new MarkerFactory(),
            $this->path_services->navigatorFactory(),
            $this->path_services->pathFactory(),
            new PathUtilitiesFactory(
                $this->path_services,
            )
        );
    }
}
