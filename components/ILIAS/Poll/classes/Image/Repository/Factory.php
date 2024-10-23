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

namespace ILIAS\Poll\Image\Repository;

use ilDBInterface;
use ILIAS\Poll\Image\I\Repository\Element\FactoryInterface as ilPollImageRepositoryElementFactoryInterface;
use ILIAS\Poll\Image\I\Repository\FactoryInterface as ilPollImageRepositoryFactoryInterface;
use ILIAS\Poll\Image\I\Repository\HandlerInterface as ilPollImageRepositoryInterface;
use ILIAS\Poll\Image\I\Repository\Key\FactoryInterface as ilPollImageRepositoryKeyFactoryInterface;
use ILIAS\Poll\Image\I\Repository\Stakeholder\FactoryInterface as ilPollImageRepositoryStakeholderFactoryInterface;
use ILIAS\Poll\Image\I\Repository\Values\FactoryInterface as ilPollImageRepositoryValuesFactoryInterface;
use ILIAS\Poll\Image\I\Repository\Wrapper\FactoryInterface as ilPollImageRepositoryWrapperFactoryInterface;
use ILIAS\Poll\Image\Repository\Element\Factory as ilPollImageRepositoryElementFactory;
use ILIAS\Poll\Image\Repository\Handler as ilPollImageRepository;
use ILIAS\Poll\Image\Repository\Key\Factory as ilPollImageRepositoryKeyFactory;
use ILIAS\Poll\Image\Repository\Stakeholder\Factory as ilPollImageRepositoryStakeholderFactory;
use ILIAS\Poll\Image\Repository\Values\Factory as ilPollImageRepositoryValuesFactory;
use ILIAS\Poll\Image\Repository\Wrapper\Factory as ilPollImageRepositoryWrapperFactory;
use ILIAS\ResourceStorage\Services as ilResourceStorageServices;

class Factory implements ilPollImageRepositoryFactoryInterface
{
    protected ilDBInterface $db;
    protected ilResourceStorageServices $irss;

    public function __construct(
        ilDBInterface $db,
        ilResourceStorageServices $irss
    ) {
        $this->db = $db;
        $this->irss = $irss;
    }

    public function element(): ilPollImageRepositoryElementFactoryInterface
    {
        return new ilPollImageRepositoryElementFactory(
            $this->irss,
            $this
        );
    }

    public function key(): ilPollImageRepositoryKeyFactoryInterface
    {
        return new ilPollImageRepositoryKeyFactory();
    }

    public function stakeholder(): ilPollImageRepositoryStakeholderFactoryInterface
    {
        return new ilPollImageRepositoryStakeholderFactory();
    }

    public function values(): ilPollImageRepositoryValuesFactoryInterface
    {
        return new ilPollImageRepositoryValuesFactory();
    }

    public function wrapper(): ilPollImageRepositoryWrapperFactoryInterface
    {
        return new ilPollImageRepositoryWrapperFactory(
            $this->db,
            $this
        );
    }

    public function handler(): ilPollImageRepositoryInterface
    {
        return new ilPollImageRepository(
            $this->wrapper()->db()->handler()
        );
    }
}
