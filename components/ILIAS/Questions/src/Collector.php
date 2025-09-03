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

namespace ILIAS\Questions;

use ILIAS\Questions\AnswerForm\Type as AnswerFormType;
use ILIAS\Questions\Question\Persistence\Repository;
use ILIAS\Questions\Question\Question;

class Collector
{
    private array $required_capabilities = [];

    public function __construct(
        private readonly Repository $repository
    ) {
    }

    public function having(string $property_class_name): self
    {
        if (in_array($property_class_name, $this->required_capabilities)) {
            return $this;
        }

        $clone = clone $this;
        $clone->required_capabilities[] = $property_class_name;
        $clone->available_answer_form_types = array_filter(
            $clone->available_answer_form_types,
            static fn(AnswerFormType $v): bool => $v->isGradable()
        );

        return $clone;
    }

    /**
     * @return array<string, \ILIAS\Questions\AnswerForm\Type>
     */
    public function getAvailableAnswerTypes(): array
    {
        return $this->repository->getAvailableAnswerTypes();
    }

    public function getQuestionsForId(int $id): Question|null
    {
        return $this->repository->getForQuestionId($id);
    }

    /**
     *
     * @param array<int> $ids
     * @return \Generator<ILIAS\Questions\Question\Question>
     */
    public function getQuestionsForIds(array $ids): \Generator
    {
        yield from $this->repository->getForQuestionIds($ids);
    }
}
