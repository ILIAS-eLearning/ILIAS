<?php

/* Copyright (c) 1998-2023 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace ILIAS\COPage\PC\Question;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\COPage\InternalDomainService;
use ILIAS\COPage\InternalGUIService;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;
use ilObject;

/**
 * Table builder for self assessment questions from a pool
 * @author Alexander Killing <killing@leifos.de>
 */
class CopySelfAssQuestionTableBuilder extends CommonTableBuilder
{
    protected \ILIAS\TestQuestionPool\Questions\PublicInterface $test_question;

    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected int $pool_ref_id,
        protected int $pool_obj_id,
        object $parent_gui,
        string $parent_cmd
    ) {
        $this->test_question = $domain->testQuestion();
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "cont_qpl";
    }

    protected function getTitle(): string
    {
        return ilObject::_lookupTitle($this->pool_obj_id);
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->pc()->selfAssQuestionRetrieval($this->pool_ref_id, $this->pool_obj_id);
    }

    protected function transformRow(array $data_row): array
    {
        $lng = $this->domain->lng();

        return [
            "question_id" => $data_row["question_id"],
            "title" => $data_row["title"],
            "ttype" => $this->test_question->getGeneralQuestionProperties($data_row["question_id"])->getTypeName($lng)
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        $table = $table
            ->textColumn("title", $lng->txt("title"), true)
            ->textColumn("ttype", $lng->txt("cont_question_type"), true)
            ->singleAction("copyQuestion", $lng->txt("cont_copy_question_into_page"));

        return $table;
    }
}
