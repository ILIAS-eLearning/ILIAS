<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\LearningModule\Editing\HelpMapping;

use ILIAS\LearningModule\InternalDomainService;
use ILIAS\LearningModule\InternalGUIService;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;
use ILIAS\UI\Component\Listing\Unordered;

class TableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected \ilObjLearningModule $lm,
        protected int $chapter_id,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "lm_help_map";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("help_assign_help_ids");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->helpMappingRetrieval($this->lm, $this->chapter_id);
    }

    protected function transformRow(array $data_row): array
    {
        return [
            "id" => $data_row["id"],
            "title" => $data_row["title"],
            "screen_ids" => $this->buildScreenIdListing($data_row["screen_ids"])
        ];
    }

    protected function buildScreenIdListing(array $screen_ids): Unordered
    {
        return $this->gui->ui()->factory()->listing()->unordered($screen_ids);
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        return $table
            ->textColumn("title", $lng->txt("st"), true)
            ->listingColumn("screen_ids", $lng->txt("cont_screen_ids"))
            ->singleAction("editHelpMapping", $lng->txt("edit"), true);
    }
}
