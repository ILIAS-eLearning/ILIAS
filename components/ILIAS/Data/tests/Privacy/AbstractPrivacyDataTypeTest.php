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

namespace ILIAS\Data\Privacy;

use ILIAS\Data\Privacy\Fixtures\ConcretePrivacyDataType;
use ILIAS\Data\Privacy\Fixtures\PrivacyDataTypeAssertions;
use ILIAS\Data\Privacy\TechnicalRequirement\TechnicalProcessing;
use ILIAS\Data\Privacy\Source\UserInput;
use PHPUnit\Framework\TestCase;

class AbstractPrivacyDataTypeTest extends TestCase
{
    use PrivacyDataTypeAssertions;

    private const string RAW_VALUE = 'sensitive-raw-value';

    private ConcretePrivacyDataType $type;

    protected function setUp(): void
    {
        $this->type = new ConcretePrivacyDataType(
            self::RAW_VALUE,
            new UserInput('some_form')
        );
    }

    public function testResolveReturnsTheRawValue(): void
    {
        $this->assertResolvesTo(
            $this->type,
            self::RAW_VALUE,
            new TechnicalProcessing('comparison')
        );
    }

    public function testResolveCanBeRepeated(): void
    {
        $this->assertSame(self::RAW_VALUE, $this->type->resolve(new TechnicalProcessing('first')));
        $this->assertSame(self::RAW_VALUE, $this->type->resolve(new TechnicalProcessing('second')));
    }

    public function testToStringDoesNotExposeValue(): void
    {
        $this->assertToStringDoesNotExposeValue($this->type, self::RAW_VALUE);
    }

    public function testToStringContainsClassName(): void
    {
        $this->assertStringContainsString(
            ConcretePrivacyDataType::class,
            (string) $this->type
        );
    }

    public function testToStringContainsSourceDescription(): void
    {
        $this->assertStringContainsString(
            'user_input:some_form',
            (string) $this->type
        );
    }

    public function testGetSourceReturnsCorrectSource(): void
    {
        $this->assertSourceDescribes($this->type, 'user_input:some_form');
    }
}
