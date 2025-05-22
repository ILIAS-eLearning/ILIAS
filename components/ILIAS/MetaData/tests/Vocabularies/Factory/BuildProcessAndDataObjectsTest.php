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

namespace ILIAS\MetaData\Vocabularies\Factory;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;
use ILIAS\MetaData\Vocabularies\Slots\Identifier;
use ILIAS\MetaData\Vocabularies\Type;
use ILIAS\MetaData\Vocabularies\NullVocabulary;

class BuildProcessAndDataObjectsTest extends TestCase
{
    public function testStandard(): void
    {
        $factory = new Factory();

        $vocab = $factory->standard(
            SlotIdentifier::LIFECYCLE_STATUS,
            'value 1',
            'value 2'
        )->get();

        $this->assertSame(SlotIdentifier::LIFECYCLE_STATUS, $vocab->slot());
        $this->assertSame(Type::STANDARD, $vocab->type());
        $this->assertSame(SlotIdentifier::LIFECYCLE_STATUS->value, $vocab->id());
        $this->assertSame(FactoryInterface::STANDARD_SOURCE, $vocab->source());
        $this->assertSame(['value 1', 'value 2'], iterator_to_array($vocab->values()));
        $this->assertTrue($vocab->isActive());
    }

    public function testStandardInactive(): void
    {
        $factory = new Factory();

        $vocab = $factory->standard(
            SlotIdentifier::LIFECYCLE_STATUS,
            'value 1',
            'value 2'
        )->withIsDeactivated(true)->get();

        $this->assertFalse($vocab->isActive());
    }

    public function testControlledString(): void
    {
        $factory = new Factory();

        $vocab = $factory->controlledString(
            SlotIdentifier::GENERAL_COVERAGE,
            'some identifier',
            'some source',
            'value 1',
            'value 2'
        )->get();

        $this->assertSame(SlotIdentifier::GENERAL_COVERAGE, $vocab->slot());
        $this->assertSame(Type::CONTROLLED_STRING, $vocab->type());
        $this->assertSame('some identifier', $vocab->id());
        $this->assertSame('some source', $vocab->source());
        $this->assertSame(['value 1', 'value 2'], iterator_to_array($vocab->values()));
        $this->assertTrue($vocab->isActive());
        $this->assertTrue($vocab->allowsCustomInputs());
    }

    public function testControlledStringInactive(): void
    {
        $factory = new Factory();

        $vocab = $factory->controlledString(
            SlotIdentifier::GENERAL_COVERAGE,
            'some identifier',
            'some source',
            'value 1',
            'value 2'
        )->withIsDeactivated(true)->get();

        $this->assertFalse($vocab->isActive());
    }

    public function testControlledStringCustomInputDisallowed(): void
    {
        $factory = new Factory();

        $vocab = $factory->controlledString(
            SlotIdentifier::GENERAL_COVERAGE,
            'some identifier',
            'some source',
            'value 1',
            'value 2'
        )->withDisallowsCustomInputs(true)->get();

        $this->assertFalse($vocab->allowsCustomInputs());
    }

    public function testControlledVocabValue(): void
    {
        $factory = new Factory();

        $vocab = $factory->controlledVocabValue(
            SlotIdentifier::GENERAL_COVERAGE,
            'some identifier',
            'some source',
            'value 1',
            'value 2'
        )->get();

        $this->assertSame(SlotIdentifier::GENERAL_COVERAGE, $vocab->slot());
        $this->assertSame(Type::CONTROLLED_VOCAB_VALUE, $vocab->type());
        $this->assertSame('some identifier', $vocab->id());
        $this->assertSame('some source', $vocab->source());
        $this->assertSame(['value 1', 'value 2'], iterator_to_array($vocab->values()));
        $this->assertTrue($vocab->isActive());
        $this->assertTrue($vocab->allowsCustomInputs());
    }

    public function testControlledVocabValueInactive(): void
    {
        $factory = new Factory();

        $vocab = $factory->controlledVocabValue(
            SlotIdentifier::GENERAL_COVERAGE,
            'some identifier',
            'some source',
            'value 1',
            'value 2'
        )->withIsDeactivated(true)->get();

        $this->assertFalse($vocab->isActive());
    }

    public function testCopyright(): void
    {
        $factory = new Factory();

        $vocab = $factory->copyright(
            'value 1',
            'value 2'
        )->get();

        $this->assertSame(SlotIdentifier::RIGHTS_DESCRIPTION, $vocab->slot());
        $this->assertSame(Type::COPYRIGHT, $vocab->type());
        $this->assertSame(SlotIdentifier::RIGHTS_DESCRIPTION->value, $vocab->id());
        $this->assertSame(FactoryInterface::COPYRIGHT_SOURCE, $vocab->source());
        $this->assertSame(['value 1', 'value 2'], iterator_to_array($vocab->values()));
        $this->assertTrue($vocab->isActive());
    }

    public function testNull(): void
    {
        $factory = new Factory();

        $vocab = $factory->null();

        $this->assertInstanceOf(NullVocabulary::class, $vocab);
    }
}
