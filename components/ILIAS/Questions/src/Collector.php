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

use ILIAS\Questions\Persistence\Repository;
use ILIAS\Questions\Question\Question;
use ILIAS\Data\UUID\Uuid;

class Collector
{
    private array $required_capabilities = [];

    public function __construct(
        private readonly Repository $repository
    ) {
    }

    public function withRequiredCapabilities(
        array $capability_class_names
    ): self {
        $this->checkCapabilities($capability_class_names);
        $clone = clone $this;
        $clone->required_capabilities = $capability_class_names;
        return $clone;
    }

    public function getQuestionsForId(
        Uuid $id
    ): ?Question {
        return $this->repository->getForQuestionId($id);
    }

    /**
     * Use with Care: This is going to be freakishly expensive, if you ask
     * for a lot of questions as the query will contain a huge amount of joins!
     *
     * @param list<\ILIAS\Data\Uuid> $ids
     * @return \Generator<ILIAS\Questions\Question\Question>
     */
    public function getQuestionsForIds(
        array $ids
    ): \Generator {
        yield from $this->repository->getForQuestionIds($ids);
    }
}
