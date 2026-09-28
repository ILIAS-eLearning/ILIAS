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

class MultiSrtConfirmationTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected \ilMobMultiSrtUpload $multi_srt,
        object $parent_gui,
        string $parent_cmd
    ) {
        $this->domain->lng()->loadLanguageModule("meta");
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "mob_msrt_upload";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("cont_multi_srt_files");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->multiSrtConfirmationRetrieval($this->multi_srt);
    }

    protected function transformRow(array $data_row): array
    {
        return [
            "id" => $data_row["id"],
            "filename" => $data_row["filename"],
            "language" => $data_row["lang"] !== ""
                ? $this->domain->lng()->txt("meta_l_" . $data_row["lang"])
                : "",
            "media_object" => isset($data_row["mob"]) && $data_row["mob"] !== ""
                ? $data_row["mob_title"]
                : "-"
        ];
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        return $table
            ->textColumn("filename", $lng->txt("filename"))
            ->textColumn("language", $lng->txt("language"))
            ->textColumn("media_object", $lng->txt("mob"));
    }
}
