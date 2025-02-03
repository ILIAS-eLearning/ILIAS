<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

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

    public function getWrappedUuid(): RamseyUuidInterface
    {
        return $this->wrapped_uuid;
    }

    public function compareTo(Uuid $other): int
    {
        return $this->wrapped_uuid->compareTo($other->getWrappedUuid());
    }

    public function equals(Uuid $other): bool
    {
        return $this->wrapped_uuid->equals($other->getWrappedUuid());
    }

    public function toString(): string
    {
        return $this->wrapped_uuid->toString();
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
