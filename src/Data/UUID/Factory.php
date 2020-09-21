<?php

namespace ILIAS\Data\UUID;

use Exception;
use Ramsey\Uuid\UuidFactory;

/**
 * Class Factory
 * @package ILIAS\Data\UUID
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class Factory
{


    /**
     * @var UuidFactory
     */
    private $uuid_factory;

    /**
     * Factory constructor.
     */
    public function __construct()
    {
        $this->uuid_factory = new UuidFactory();
    }

    /**
     * @return Uuid
     * @throws Exception
     */
    public function uuid4() : Uuid
    {
        return new RamseyUuidWrapper($this->uuid_factory->uuid4());
    }

    /**
     * @return string
     * @throws Exception
     */
    public function uuid4AsString() : string
    {
        return $this->uuid4()->toString();
    }

    /**
     * @param string $uuid
     * @return Uuid
     */
    public function fromString(string $uuid) : Uuid
    {
        return new RamseyUuidWrapper($this->uuid_factory->fromString($uuid));
    }
}
