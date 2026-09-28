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

namespace ILIAS\MediaPool\PageUsage;

use ILIAS\MediaPool\InternalDomainService;
use ILIAS\MediaPool\InternalGUIService;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;
use ILIAS\UI\Component\Link\Link;

class TableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected \ilMediaPoolPage $page,
        protected bool $include_hist,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd, false);
    }

    protected function getId(): string
    {
        return "mep_page_usages";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("cont_mob_usages");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->mediaPoolPageUsagesRetrieval(
            $this->page,
            $this->include_hist
        );
    }

    protected function transformRow(array $data_row): array
    {
        return [
            "id" => $data_row["id"],
            "object" => $this->buildObjectLink($data_row),
            "type" => $data_row["type"],
            "versions" => $data_row["versions"]
        ];
    }

    protected function buildObjectLink(array $data_row): Link
    {
        $link = $this->gui->ui()->factory()->link()->standard(
            $data_row["object"],
            $data_row["object_link"]
        );
        if ($data_row["object_link"] === "") {
            $link = $link->withDisabled();
        }
        return $link;
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        return $table
            ->linkColumn("object", $lng->txt("mob_object"))
            ->textColumn("type", $lng->txt("type"))
            ->textColumn("versions", $lng->txt("cont_versions"));
    }
}
