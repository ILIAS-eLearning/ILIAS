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

namespace ILIAS\Taxonomy\Administration\RepositoryTaxonomies;

use ILIAS\Taxonomy\InternalDomainService;
use ILIAS\Taxonomy\InternalGUIService;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;
use ILIAS\UI\Component\Listing\Unordered;

class TableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected \ilObjTaxonomyAdministration $obj,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd, false);
    }

    protected function getId(): string
    {
        return "tax_adm_repo";
    }

    protected function getTitle(): string
    {
        return "";
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->repositoryTaxonomiesRetrieval($this->obj);
    }

    protected function transformRow(array $data_row): array
    {
        return [
            "id" => $data_row["id"],
            "tax_title" => $data_row["tax_title"],
            "status" => $data_row["tax_status"]
                ? $this->domain->lng()->txt("active")
                : $this->domain->lng()->txt("inactive"),
            "objects" => $this->buildReferenceListing(
                $data_row["obj_title"],
                $data_row["references"]
            )
        ];
    }

    protected function buildReferenceListing(string $obj_title, array $references): Unordered
    {
        $links = [];
        foreach ($references as $reference) {
            $links[] = $this->gui->ui()->factory()->link()->standard(
                $reference["path"] . " › " . $obj_title,
                $reference["url"]
            );
        }

        return $this->gui->ui()->factory()->listing()->unordered($links);
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        return $table
            ->textColumn("tax_title", $lng->txt("obj_tax"), true)
            ->textColumn("status", $lng->txt("status"))
            ->listingColumn("objects", $lng->txt("object"));
    }
}
