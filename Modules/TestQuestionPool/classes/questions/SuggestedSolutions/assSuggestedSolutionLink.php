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
 * a suggested solution that links to some other object/place
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class assSuggestedSolutionLink extends assQuestionSuggestedSolution
{
    protected string $type;
    protected string $internal_link;

    public function __construct(
        int $id,
        int $question_id,
        int $subquestion_index,
        string $import_id,
        \DateTimeImmutable $last_update,
        string $type,
        string $internal_link
    ) {
        parent::__construct($id, $question_id, $subquestion_index, $import_id, $last_update);
        $this->type = $type;
        $this->internal_link = $internal_link;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function getStorableValue() : string
    {
        return $this->getInternalLink();
    }

    public function getInternalLink() : string
    {
        return $this->internal_link;
    }
    public function withInternalLink(string $internal_link) : static
    {
        $clone = clone $this;
        $clone->internal_link = $internal_link;
        return $clone;
    }
}
