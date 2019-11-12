<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Common;

use ILIAS\Data\UUID\Factory as UUID;

class entityIdBuilder {

    public function new() : AssessmentEntityId
    {
        $uuid = new UUID();
        return new AssessmentEntityId($uuid->uuid4AsString());
    }

    /**
     * @param string $uuid
     *
     * @return AssessmentEntityId
     */
    public function fromString(string $uuid) : AssessmentEntityId
    {
        return new AssessmentEntityId($uuid);
    }

}