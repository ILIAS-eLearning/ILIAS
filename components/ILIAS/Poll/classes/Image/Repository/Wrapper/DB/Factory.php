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

namespace ILIAS\Poll\Image\Repository\Wrapper\DB;

use ilDBInterface;
use ILIAS\Poll\Image\I\Repository\FactoryInterface as ilPollImageRepositoryFactoryInterface;
use ILIAS\Poll\Image\I\Repository\Wrapper\DB\FactoryInterface as ilPollImageRepositoryDBWrapperFactoryInterface;
use ILIAS\Poll\Image\I\Repository\Wrapper\DB\HandlerInterface as ilPollImageRepositoryWrapperDBInterface;
use ILIAS\Poll\Image\Repository\Wrapper\DB\Handler as ilPollImageRepositoryWrapperDB;

class Factory implements ilPollImageRepositoryDBWrapperFactoryInterface
{
    protected ilDBInterface $db;
    protected ilPollImageRepositoryFactoryInterface $repository;

    public function __construct(
        ilDBInterface $db,
        ilPollImageRepositoryFactoryInterface $repository
    ) {
        $this->db = $db;
        $this->repository = $repository;
    }

    public function handler(): ilPollImageRepositoryWrapperDBInterface
    {
        return new ilPollImageRepositoryWrapperDB(
            $this->repository,
            $this->db
        );
    }
}
