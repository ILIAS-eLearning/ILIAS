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

use ILIAS\MetaData\Vocabularies\Conditions\ConditionInterface;

class Vocabulary implements VocabularyInterface
{
    protected string $source;

    /**
     * @var string[]
     */
    protected array $values;
    protected ?ConditionInterface $condition;

    public function __construct(
        string $source,
        ?ConditionInterface $condition = null,
        string ...$values
    ) {
        $this->source = $source;
        $this->values = $values;
        $this->condition = $condition;
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

    public function isConditional(): bool
    {
        return isset($this->condition);
    }

    public function condition(): ?ConditionInterface
    {
        return $this->condition;
    }
}
