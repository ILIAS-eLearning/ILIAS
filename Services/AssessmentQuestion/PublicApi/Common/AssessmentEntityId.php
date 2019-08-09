<?php
declare(strict_types=1);

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Common;

class AssessmentEntityId
{

    /**
     * @var string
     */
    protected $id;

    public function __construct(string $uuid) {
        $this->id = $uuid;
    }


    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }
}