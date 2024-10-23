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

use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;
use ILIAS\MetaData\Vocabularies\Type;
use ILIAS\MetaData\Vocabularies\NullVocabulary;
use ILIAS\MetaData\Vocabularies\VocabularyInterface;

class Factory implements FactoryInterface
{
    public function standard(SlotIdentifier $slot, string ...$values): BuilderInterface
    {
        return new Builder(
            $slot,
            Type::STANDARD,
            $slot->value,
            FactoryInterface::STANDARD_SOURCE,
            ...$values
        );
    }

    public function controlledString(
        SlotIdentifier $slot,
        string $id,
        string $source,
        string ...$values
    ): BuilderInterface {
        return new Builder($slot, Type::CONTROLLED_STRING, $id, $source, ...$values);
    }

    public function controlledVocabValue(
        SlotIdentifier $slot,
        string $id,
        string $source,
        string ...$values
    ): BuilderInterface {
        return new Builder($slot, Type::CONTROLLED_VOCAB_VALUE, $id, $source, ...$values);
    }

    public function copyright(string ...$values): BuilderInterface
    {
        return new Builder(
            SlotIdentifier::RIGHTS_DESCRIPTION,
            Type::COPYRIGHT,
            SlotIdentifier::RIGHTS_DESCRIPTION->value,
            FactoryInterface::COPYRIGHT_SOURCE,
            ...$values
        );
    }

    public function null(): VocabularyInterface
    {
        return new NullVocabulary();
    }
}
