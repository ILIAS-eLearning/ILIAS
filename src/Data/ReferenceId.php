<?php

declare(strict_types=1);

namespace ILIAS\Data;

use ilObject2;

/**
 * Class ReferenceId
 *
 * @package ILIAS\Data
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class ReferenceId
{
    private int $ref_id;

    public function __construct(int $ref_id)
    {
        $this->ref_id = $ref_id;
    }

    public function toInt(): int
    {
        return $this->ref_id;
    }

    public function toObjectId(): ObjectId
    {
        return new ObjectId(ilObject2::_lookupObjectId($this->ref_id));
    }
}
