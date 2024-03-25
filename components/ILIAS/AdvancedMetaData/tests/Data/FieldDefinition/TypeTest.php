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

namespace ILIAS\AdvancedMetaData\Data\FieldDefinition;

use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
    public function testTryFromString(): void
    {
        $this->assertSame(Type::ADDRESS, Type::tryFromString('Address'));
        $this->assertSame(Type::DATETIME, Type::tryFromString('DateTime'));
    }

    public function testStringValue(): void
    {
        $this->assertSame('Text', Type::TEXT->stringValue());
        $this->assertSame('ExternalLink', Type::EXTERNAL_LINK->stringValue());
    }

    public function testStringValueNotEmpty(): void
    {
        foreach (Type::cases() as $case) {
            $this->assertNotEmpty($case->stringValue());
        }
    }

    public function testStringValueConsistency(): void
    {
        foreach (Type::cases() as $case) {
            $this->assertSame($case, Type::tryFromString($case->stringValue()));
        }
    }
}
