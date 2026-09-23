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

namespace ILIAS\LearningModule\HelpTooltip;

use ILIAS\LearningModule\InternalDomainService;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class TableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected string $component,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "lm_help_tooltips";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("help_tooltips");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->helpTooltipRetrieval($this->component);
    }

    protected function transformRow(array $data_row): array
    {
        return [
            "id" => $data_row["id"],
            "tt_id" => $data_row["tt_id"],
            "text" => $data_row["text"]
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        return $table
            ->textColumn("tt_id", $lng->txt("help_tooltip_id"), true)
            ->textColumn("text", $lng->txt("help_tt_text"))
            ->singleAction("editTooltip", $lng->txt("edit"), true)
            ->singleAction("deleteTooltip", $lng->txt("delete"), true);
    }
}
