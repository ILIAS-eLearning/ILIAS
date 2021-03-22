<?php

namespace ILIAS\ResourceStorage;

use ILIAS\ResourceStorage\Identification\IdentificationGenerator;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

class DummyIDGenerator implements IdentificationGenerator
{
    private $id = 'dummy';

    /**
     * DummyIDGenerator constructor.
     * @param string $id
     */
    public function __construct(string $id = 'dummy')
    {
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function getUniqueResourceIdentification() : ResourceIdentification
    {
        return new ResourceIdentification($this->id);
    }
}
