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

use PHPUnit\Framework\TestCase;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\Type;

class TypeSpecificDataTest extends TestCase
{
    protected function getData(?int $field_id = null): TypeSpecificDataImplementation
    {
        return new class ($field_id) extends TypeSpecificDataImplementation {
            public function isTypeSupported(Type $type): bool
            {
                return true;
            }

            protected function getSubData(): \Generator
            {
                yield from [];
            }
        };
    }

    public function testIsPersistedTrue(): void
    {
        $data = $this->getData(7);
        $this->assertTrue($data->isPersisted());
    }

    public function testIsPersistedFalse(): void
    {
        $data = $this->getData();
        $this->assertFalse($data->isPersisted());
    }
}
