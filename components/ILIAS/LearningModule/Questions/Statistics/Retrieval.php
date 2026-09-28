<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\LearningModule\Question\Statistics;

use Generator;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\TestQuestionPool\Questions\PublicInterface as QuestionInfo;
use ilLMPageObject;
use ilPageQuestionProcessor;

class Retrieval implements RetrievalInterface
{
    use RetrievalBase;

    protected ?array $question_data = null;

    public function __construct(
        protected int $lm_id,
        protected QuestionInfo $question_info
    ) {
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): Generator {
        $data = $this->getQuestionData();
        $data = $this->applyOrder($data, $order);
        $data = $this->applyRange($data, $range);

        yield from $data;
    }

    public function count(array $filter, array $parameters): int
    {
        return count($this->getQuestionData());
    }

    public function isFieldNumeric(string $field): bool
    {
        return false;
    }

    protected function getQuestionData(): array
    {
        if ($this->question_data !== null) {
            return $this->question_data;
        }

        $questions = ilLMPageObject::queryQuestionsOfLearningModule(
            $this->lm_id,
            "",
            "",
            0,
            0
        );

        $data = [];
        foreach ($questions["set"] as $question) {
            $stats = ilPageQuestionProcessor::getQuestionStatistics((int) $question["question_id"]);
            $all = (int) $stats["all"];
            $first = (int) $stats["first"];
            $second = (int) $stats["second"];
            $third_or_more = (int) $stats["third_or_more"];

            $data[] = [
                "id" => (int) $question["question_id"],
                "page" => \ilLMObject::_lookupTitle((int) $question["page_id"]),
                "question" => $this->question_info
                    ->getGeneralQuestionProperties((int) $question["question_id"])
                    ->getQuestionText(),
                "answered" => $all,
                "correct_first" => $this->formatPercentage($first, $all),
                "correct_second" => $this->formatPercentage($second, $all),
                "third_and_more" => $this->formatPercentage($third_or_more, $all),
                "never" => $this->formatPercentage($all - $first - $second - $third_or_more, $all)
            ];
        }

        return $this->question_data = $data;
    }

    protected function formatPercentage(int $value, int $all): string|int
    {
        if ($all === 0) {
            return 0;
        }

        return $value . " (" . (100 / $all * $value) . " %)";
    }
}
