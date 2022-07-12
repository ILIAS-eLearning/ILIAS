<?php declare(strict_types=1);

namespace ILIAS\Data\UUID;

use Ramsey\Uuid\UuidInterface as RamseyUuidInterface;

/**
 * Class Uuid
 * @package ILIAS\Data\UUID
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class RamseyUuidWrapper implements Uuid
{
    private RamseyUuidInterface $wrapped_uuid;

    public function __construct(RamseyUuidInterface $wrapped_uuid)
    {
        $this->wrapped_uuid = $wrapped_uuid;
    }

    public function getWrappedUuid() : RamseyUuidInterface
    {
        return $this->wrapped_uuid;
    }

    public function compareTo(Uuid $other) : int
    {
        return $this->wrapped_uuid->compareTo($other->getWrappedUuid());
    }

    public function equals(Uuid $other) : bool
    {
        return $this->wrapped_uuid->equals($other->getWrappedUuid());
    }

    public function toString() : string
    {
        return $this->wrapped_uuid->toString();
    }

    public function __toString() : string
    {
        return $this->toString();
    }
}
