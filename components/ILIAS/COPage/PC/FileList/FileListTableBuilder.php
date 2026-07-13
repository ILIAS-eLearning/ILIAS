<?php

/* Copyright (c) 1998-2024 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace ILIAS\COPage\PC\FileList;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\COPage\InternalDomainService;
use ILIAS\COPage\InternalGUIService;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;
use ilPCFileList;
use ilObjFile;
use ilLegacyFormElementsUtil;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class FileListTableBuilder extends CommonTableBuilder
{
    protected int $pos = 0;

    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected ilPCFileList $file_list,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd, false);
    }

    protected function getId(): string
    {
        return "pcfl";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("cont_files");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->pc()->fileListRetrieval($this->file_list);
    }

    protected function transformRow(array $data_row): array
    {
        return $data_row;
    }

    protected function getOrderingCommand(): string
    {
        return "savePositions";
    }


    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();
        $lng->loadLanguageModule("style");
        $lng->loadLanguageModule("copg");
        $table = $table
            ->textColumn("file_name", $lng->txt("cont_file"), false);

        if ($this->parent_gui->checkStyleSelection()) {
            $table = $table->textColumn("class", $lng->txt("sty_class"), false);
        }

        $table = $table
            ->multiAction("confirmDeletionFileItem", $lng->txt("delete"), true);

        if ($this->parent_gui->checkStyleSelection()) {
            $table = $table->singleAction("editStyleClass", $lng->txt("copg_edit_style_class"), true);
        } else {
            //$table = $table->standardAction("savePositions", $lng->txt("cont_save_positions"));
        }

        return $table;
    }
}
