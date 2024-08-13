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
use ILIAS\MetaData\Vocabularies\Vocabulary;
use ILIAS\MetaData\Vocabularies\Type;
use ILIAS\MetaData\Vocabularies\VocabularyInterface;

class Builder implements BuilderInterface
{
    protected SlotIdentifier $slot;
    protected Type $type;
    protected string $id;
    protected string $source;

    /**
     * @var string[]
     */
    protected array $values;
    protected bool $is_active = true;
    protected bool $allows_custom_inputs = true;

    public function __construct(
        SlotIdentifier $slot,
        Type $type,
        string $id,
        string $source,
        string ...$values
    ) {
        $this->slot = $slot;
        $this->type = $type;
        $this->id = $id;
        $this->source = $source;
        $this->values = $values;
    }

    public function withIsDeactivated(bool $deactivated = true): BuilderInterface
    {
        $clone = clone $this;
        $clone->is_active = !$deactivated;
        return $clone;
    }

    public function withDisallowsCustomInputs(bool $no_custom_inputs = true): BuilderInterface
    {
        $clone = clone $this;
        $clone->allows_custom_inputs = !$no_custom_inputs;
        return $clone;
    }

    public function get(): VocabularyInterface
    {
        return new Vocabulary(
            $this->slot,
            $this->type,
            $this->id,
            $this->source,
            $this->is_active,
            $this->allows_custom_inputs,
            ...$this->values
        );
    }
}
