<?php

/* Copyright (c) 1998-2023 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace ILIAS\COPage\PC\Question;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\Repository\RetrievalBase;
use ILIAS\TestQuestionPool\Questions\PublicInterface as QuestionInterface;
use ilObjQuestionPool;
use ilAssQuestionList;
use ILIAS\DI\Container;
use ILIAS\COPage\InternalDomainService;

/**
 * Retrieval for self assessment questions from a pool
 * @author Alexander Killing <killing@leifos.de>
 */
class SelfAssQuestionRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected InternalDomainService $domain,
        protected int $pool_ref_id,
        protected int $pool_obj_id
    ) {
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        $data = $this->collectData();

        // Apply ordering if specified
        $data = $this->applyOrder($data, $order);

        // Apply range (pagination) if specified
        $data = $this->applyRange($data, $range);

        foreach ($data as $row) {
            yield $row;
        }
    }

    public function count(
        array $filter,
        array $parameters
    ): int {
        return count($this->collectData());
    }

    protected function collectData(): array
    {
        $access = $this->domain->access();

        $all_types = ilObjQuestionPool::_getSelfAssessmentQuestionTypes();
        $all_ids = array();
        foreach ($all_types as $k => $v) {
            $all_ids[] = $v["question_type_id"];
        }

        $questions = array();
        if ($access->checkAccess("read", "", $this->pool_ref_id)) {
            $questionList = new ilAssQuestionList(
                $this->domain->database(),
                $this->domain->lng(),
                $this->domain->refinery(),
                $this->domain->componentRepository()
            );
            $questionList->setParentObjId($this->pool_obj_id);
            $questionList->load();

            $data = $questionList->getQuestionDataArray();

            foreach ($data as $d) {
                // list only self assessment question types
                if (in_array($d["question_type_fi"], $all_ids)) {
                    $d["id"] = $d["question_id"];
                    $questions[] = $d;
                }
            }
        }
        return $questions;
    }

    public function isFieldNumeric(string $field): bool
    {
        return $field === "question_id";
    }
}
