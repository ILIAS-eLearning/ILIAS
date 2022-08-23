<?php declare(strict_types=1);

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
 */

namespace ILIAS\TA\Questions;

/**
 * a suggested solution with text
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class assSuggestedSolutionText extends assQuestionSuggestedSolution
{
    protected string $value;

    public function __construct(
        int $id,
        int $question_id,
        int $subquestion_index,
        string $import_id,
        \DateTimeImmutable $last_update,
        string $type,
        string $value
    ) {
        parent::__construct($id, $question_id, $subquestion_index, $import_id, $last_update);
        $this->value = $value;
    }

    public function getType() : string
    {
        return parent::TYPE_TEXT;
    }

    public function getStorableValue() : string
    {
        return $this->getValue();
    }

    public function getValue() : string
    {
        return $this->value;
    }
    public function withValue(string $value) : static
    {
        $clone = clone $this;
        $clone->value = $value;
        return $clone;
    }
}
