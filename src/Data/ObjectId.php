<?php

declare(strict_types=1);

namespace ILIAS\Data;

use ilObject2;

/**
 * Class ObjectId
 *
 * @package ILIAS\Data
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class ObjectId
{
    private int $object_id;

    /**
     * ReferenceId constructor.
     */
    public function __construct(int $object_id)
    {
        $this->object_id = $object_id;
    }

    public function toInt(): int
    {
        return $this->object_id;
    }

    /**
     * @return ReferenceId[]
     */
    public function toReferenceIds(): array
    {
        $ref_ids = [];
        foreach (ilObject2::_getAllReferences($this->object_id) as $reference) {
            $ref_ids[] = new ReferenceId((int) $reference);
        }

        return $ref_ids;
    }
}
