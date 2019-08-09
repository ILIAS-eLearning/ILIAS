<?php
declare(strict_types=1);

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Common;

interface AssessmentEntityId
{

    public function new() : AssessmentEntityId;


    /**
     * @param string $uuid
     *
     * @return AssessmentEntityId
     */
    public function fromString(string $uuid) : AssessmentEntityId;
}