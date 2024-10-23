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

namespace ILIAS\Poll\Image\Repository\Key;

use ILIAS\Data\ObjectId;
use ILIAS\Poll\Image\I\Repository\Key\HandlerInterface as ilPollImageRepositoryKeyInterface;

class Handler implements ilPollImageRepositoryKeyInterface
{
    protected ObjectId $object_id;

    public function withObjectId(
        ObjectId $object_id
    ): ilPollImageRepositoryKeyInterface {
        $clone = clone $this;
        $clone->object_id = $object_id;
        return $clone;
    }

    public function getObjectId(): ObjectId
    {
        return $this->object_id;
    }

    public function isValid(): bool
    {
        return isset($this->object_id);
    }
}
