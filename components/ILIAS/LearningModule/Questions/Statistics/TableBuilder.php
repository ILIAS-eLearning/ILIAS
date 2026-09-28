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

use ILIAS\LearningModule\InternalDomainService;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class TableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected int $lm_id,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "lm_question_statistics";
    }

    protected function getTitle(): string
    {
        return "";
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->questionStatisticsRetrieval($this->lm_id);
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        return $table
            ->textColumn("page", $lng->txt("pg"))
            ->textColumn("question", $lng->txt("question"))
            ->textColumn("answered", $lng->txt("cont_users_answered"))
            ->textColumn("correct_first", $lng->txt("cont_correct_after_first"))
            ->textColumn("correct_second", $lng->txt("cont_second"))
            ->textColumn("third_and_more", $lng->txt("cont_third_and_more"))
            ->textColumn("never", $lng->txt("cont_never"));
    }
}
