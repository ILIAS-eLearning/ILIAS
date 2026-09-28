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

namespace ILIAS\LearningModule\Editing\ExportIds;

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
        return "lm_export_ids";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("cont_html_export_ids");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->exportIdsRetrieval($this->lm_id);
    }

    protected function transformRow(array $data_row): array
    {
        $export_id = $data_row["export_id"];
        if ($data_row["duplicate"]) {
            $export_id .= " (" . $this->domain->lng()->txt("cont_exp_id_used_multiple") . ")";
        }

        return [
            "id" => $data_row["id"],
            "title" => $data_row["title"],
            "export_id" => $export_id
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        return $table
            ->textColumn("title", $lng->txt("pg"), true)
            ->textColumn("export_id", $lng->txt("cont_export_id"))
            ->singleAction("editExportId", $lng->txt("cont_export_id"), true);
    }
}
