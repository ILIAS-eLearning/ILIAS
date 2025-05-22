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

namespace ILIAS\Poll\Image\Repository\Element\Wrapper\IRSS;

use ILIAS\Poll\Image\I\Repository\Element\Wrapper\IRSS\FactoryInterface as ilPollImageRepositoryElementIRSSWrapperFactory;
use ILIAS\Poll\Image\I\Repository\Element\Wrapper\IRSS\HandlerInterface as ilPollImageRepositoryElementIRSSWrapperInterface;
use ILIAS\Poll\Image\I\Repository\FactoryInterface as ilPollImageRepositoryFactoryInterface;
use ILIAS\Poll\Image\Repository\Element\Wrapper\IRSS\Handler as ilPollImageRepositoryElementIRSSWrapper;
use ILIAS\ResourceStorage\Services as ILIASResourceStorageService;

class Factory implements ilPollImageRepositoryElementIRSSWrapperFactory
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

    public function handler(): ilPollImageRepositoryElementIRSSWrapperInterface
    {
        return new ilPollImageRepositoryElementIRSSWrapper(
            $this->irss,
            $this->repository
        );
    }
}
