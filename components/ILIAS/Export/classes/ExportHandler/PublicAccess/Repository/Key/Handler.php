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

namespace ILIAS\Export\ExportHandler\PublicAccess\Repository\Key;

use ILIAS\Data\ObjectId;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Key\HandlerInterface as ilExportHandlerPublicAccessRepositoryKeyInterface;

class Handler implements ilExportHandlerPublicAccessRepositoryKeyInterface
{
    protected ObjectId $object_id;

    public function __construct()
    {
        $this->object_id = new ObjectId(self::EMPTY_OBJECT_ID);
    }

    public function withObjectId(
        ObjectId $object_id
    ): ilExportHandlerPublicAccessRepositoryKeyInterface {
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
        return $this->object_id->toInt() !== self::EMPTY_OBJECT_ID;
    }
}
