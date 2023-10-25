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

namespace ILIAS\Object\Properties\ObjectReferenceProperties;

use ILIAS\Object\Properties\ObjectReferenceProperties\ObjectAvailabilityPeriodProperty;

class ObjectReferenceProperties
{
    public function __construct(
        private ?int $object_reference_id = null,
        private ?int $obj_id = null,
        private ?\DateTimeImmutable $date_of_deletion = null,
        private ?int $deleted_by = null,
        private ObjectAvailabilityPeriodProperty $object_time_based_activation_property = new ObjectAvailabilityPeriodProperty()
    ) {
    }

    public function getPropertyAvailabilityPeriod(): ObjectAvailabilityPeriodProperty
    {
        return $this->object_time_based_activation_property;
    }

    public function getObjectReferenceId(): ?int
    {
        return $this->object_reference_id;
    }

    public function getObjectId(): ?int
    {
        return $this->obj_id;
    }

    public function getDateOfDeletion(): ?\DateTimeImmutable
    {
        return $this->date_of_deletion;
    }

    public function getDeletedBy(): ?int
    {
        return $this->deleted_by;
    }
}
