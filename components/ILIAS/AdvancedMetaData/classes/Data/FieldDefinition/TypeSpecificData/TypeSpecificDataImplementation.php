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

namespace ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData;

use ILIAS\AdvancedMetaData\Data\FieldDefinition\Type;
use ILIAS\AdvancedMetaData\Data\PersistenceTrackingDataImplementation;

abstract class TypeSpecificDataImplementation extends PersistenceTrackingDataImplementation implements TypeSpecificData
{
    private ?int $field_id;

    public function __construct(?int $field_id = null)
    {
        $this->field_id = $field_id;
    }

    abstract public function isTypeSupported(Type $type): bool;

    final public function fieldID(): ?int
    {
        return $this->field_id;
    }

    public function isPersisted(): bool
    {
        return !is_null($this->fieldID());
    }
}
