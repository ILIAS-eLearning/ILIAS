<?php

declare(strict_types=1);

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

/**
 * A PostCondition does restrict the progression of a user through the learning sequence.
 * Thus, instead of saying "You may only _visit_ this object if you did this",
 * a PostCondition says "you may only _leave_ this object if you did this".
 *
 * LSPostConditions are being applied by the LearningSequenceConditionController.
 */
class ilLSPostCondition
{
    protected int $ref_id;
    protected string $operator;
    protected ?int $value;

    public function __construct(
        int $ref_id,
        string $operator,
        ?int $value = null
    ) {
        $this->ref_id = $ref_id;
        $this->operator = $operator;
        $this->value = $value;
    }

    public function getRefId(): int
    {
        return $this->ref_id;
    }

    public function getConditionOperator(): string
    {
        return $this->operator;
    }

    public function withConditionOperator(string $operator): ilLSPostCondition
    {
        $clone = clone $this;
        $clone->operator = $operator;
        return $clone;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function withValue(int $value): ilLSPostCondition
    {
        $clone = clone $this;
        $clone->value = $value;
        return $clone;
    }
}
