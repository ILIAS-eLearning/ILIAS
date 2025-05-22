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

namespace ILIAS\MetaData\Vocabularies;

use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;

class Vocabulary implements VocabularyInterface
{
    protected SlotIdentifier $slot;
    protected Type $type;
    protected string $id;
    protected string $source;
    protected bool $is_active;
    protected bool $allows_custom_inputs;

    /**
     * @var string[]
     */
    protected array $values;

    public function __construct(
        SlotIdentifier $slot,
        Type $type,
        string $id,
        string $source,
        bool $is_active = true,
        bool $allows_custom_inputs = false,
        string ...$values
    ) {
        $this->slot = $slot;
        $this->type = $type;
        $this->id = $id;
        $this->source = $source;
        $this->values = $values;
        $this->is_active = $is_active;
        $this->allows_custom_inputs = $allows_custom_inputs;
    }

    public function slot(): SlotIdentifier
    {
        return $this->slot;
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function source(): string
    {
        return $this->source;
    }

    /**
     * @return string[]
     */
    public function values(): \Generator
    {
        foreach ($this->values as $value) {
            yield $value;
        }
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function allowsCustomInputs(): bool
    {
        return $this->allows_custom_inputs;
    }
}
