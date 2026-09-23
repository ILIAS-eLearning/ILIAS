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

namespace ILIAS\MediaObjects\SubTitles;

use ILIAS\MediaObjects\InternalDomainService;
use ILIAS\MediaObjects\InternalGUIService;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class TableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected \ilObjMediaObject $media_object,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd, false);
    }

    protected function getId(): string
    {
        return "mob_subtitles";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("mob_subtitle_files");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->subTitlesRetrieval($this->media_object);
    }

    protected function transformRow(array $data_row): array
    {
        return [
            "id" => $data_row["id"],
            "file" => $data_row["file"],
            "language" => $this->domain->lng()->txt("meta_l_" . $data_row["language"])
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        return $table
            ->textColumn("file", $lng->txt("mob_file"))
            ->textColumn("language", $lng->txt("mob_language"))
            ->singleAction("confirmSrtDeletion", $lng->txt("delete"), true);
    }
}
