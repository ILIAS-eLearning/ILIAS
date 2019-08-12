<?php

use ILIAS\Data\UUID\Factory as UUID;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;

class entityIdBuilder {

    public function new() : AssessmentEntityId
    {
        global $DIC;

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