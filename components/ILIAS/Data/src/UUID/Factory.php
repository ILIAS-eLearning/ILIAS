<?php

declare(strict_types=1);

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
    private UuidFactory $uuid_factory;

    public function __construct()
    {
        $this->uuid_factory = new UuidFactory();
    }

    public function uuid4(): Uuid
    {
        return new RamseyUuidWrapper($this->uuid_factory->uuid4());
    }

    public function uuid4AsString(): string
    {
        return $this->uuid4()->toString();
    }

    public function fromString(string $uuid): Uuid
    {
        return new RamseyUuidWrapper($this->uuid_factory->fromString($uuid));
    }
}
