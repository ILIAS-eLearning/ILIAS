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

namespace ILIAS\LearningModule\Editing;

use ILIAS\LearningModule\InternalDomainService;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class GlossariesTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected \ilObjLearningModule $lm,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "lm_glossaries";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("cont_auto_glossaries");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->glossariesRetrieval($this->lm);
    }

    protected function transformRow(array $data_row): array
    {
        return [
            "id" => $data_row["id"],
            "title" => $data_row["title"]
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        return $table
            ->textColumn("title", $lng->txt("title"))
            ->singleAction("confirmRemoveLMGlossary", $lng->txt("remove"), true);
    }
}
