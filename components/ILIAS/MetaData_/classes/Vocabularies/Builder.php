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

use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Vocabularies\Conditions\Condition;

class Builder implements BuilderInterface
{
    protected string $source;

    /**
     * @var string[]
     */
    protected array $values;
    protected ?Condition $condition = null;

    public function __construct(
        string $source,
        string ...$values
    ) {
        $this->source = $source;
        $this->values = $values;
    }

    public function withCondition(
        string $value,
        PathInterface $path
    ): BuilderInterface {
        $clone = clone $this;
        $clone->condition = new Condition($value, $path);
        return $clone;
    }

    public function get(): VocabularyInterface
    {
        return new Vocabulary(
            $this->source,
            $this->condition,
            ...$this->values
        );
    }
}
