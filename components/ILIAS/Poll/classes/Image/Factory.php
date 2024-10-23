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

namespace ILIAS\Poll\Image;

use ilDBInterface;
use ILIAS\Poll\Image\Handler as ilPollImage;
use ILIAS\Poll\Image\I\FactoryInterface as ilPollImageFactoryInterface;
use ILIAS\Poll\Image\I\HandlerInterface as ilPollImageInterface;
use ILIAS\Poll\Image\I\Repository\FactoryInterface as ilPollImageRepositoryFactoryInterface;
use ILIAS\Poll\Image\Repository\Factory as ilPollImageRepositoryFactory;
use ILIAS\ResourceStorage\Services as ilResourceStorageServices;

class Factory implements ilPollImageFactoryInterface
{
    protected ilDBInterface $db;
    protected ilResourceStorageServices $irss;

    public function __construct()
    {
        global $DIC;
        $this->db = $DIC->database();
        $this->irss = $DIC->resourceStorage();
    }

    public function repository(): ilPollImageRepositoryFactoryInterface
    {
        return new ilPollImageRepositoryFactory(
            $this->db,
            $this->irss
        );
    }

    public function handler(): ilPollImageInterface
    {
        return new ilPollImage(
            $this->irss,
            $this->repository()
        );
    }
}
