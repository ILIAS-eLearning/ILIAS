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

namespace ILIAS\Poll\Image\Repository\Element\Wrapper;

use ILIAS\Poll\Image\I\Repository\Element\Wrapper\FactoryInterface as ilPollImageRepositoryElementWrapperFactoryInterface;
use ILIAS\Poll\Image\I\Repository\Element\Wrapper\IRSS\FactoryInterface as ilPollImageRepositoryElementIRSSWrapperFactoryInterface;
use ILIAS\Poll\Image\I\Repository\FactoryInterface as ilPollImageRepositoryFactoryInterface;
use ILIAS\Poll\Image\Repository\Element\Wrapper\IRSS\Factory as ilPollImageRepositoryElementIRSSWrapperFactory;
use ILIAS\ResourceStorage\Services as ILIASResourceStorageService;

class Factory implements ilPollImageRepositoryElementWrapperFactoryInterface
{
    protected ILIASResourceStorageService $irss;
    protected ilPollImageRepositoryFactoryInterface $repository;

    public function __construct(
        ILIASResourceStorageService $irss,
        ilPollImageRepositoryFactoryInterface $repository
    ) {
        $this->irss = $irss;
        $this->repository = $repository;
    }

    public function irss(): ilPollImageRepositoryElementIRSSWrapperFactoryInterface
    {
        return new ilPollImageRepositoryElementIRSSWrapperFactory(
            $this->irss,
            $this->repository
        );
    }
}
