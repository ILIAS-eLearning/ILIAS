<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Wiki\Page;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;
use ILIAS\UI\Component\Listing\Unordered as UnorderedListing;
use ILIAS\Wiki\InternalDomainService;
use ILIAS\Wiki\InternalGUIService;

class ContributorsTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected int $wiki_id,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd);
    }

    protected function getId(): string
    {
        return "wiki_contributors";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("wiki_contributors");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->page()->contributorsRetrieval($this->wiki_id);
    }

    protected function transformRow(array $data_row): array
    {
        return [
            "id" => $data_row["id"],
            "name" => $data_row["name"],
            "pages" => $this->buildPageListing($data_row["pages"]),
            "grading" => $this->getGradingLabel(
                $data_row["status"],
                $data_row["status_date"]
            ),
            "mark" => $data_row["mark"]
        ];
    }

    protected function buildPageListing(array $pages): UnorderedListing
    {
        $f = $this->gui->ui()->factory();
        $items = [];
        arsort($pages);

        foreach ($pages as $page_id => $count) {
            if ($page_id > 0) {
                $link = $f->link()->standard(
                    \ilWikiPage::lookupTitle((int) $page_id) . " (" . $count . ")",
                    ""
                )->withDisabled();
                $items[] = $link;
            }
        }

        return $f->listing()->unordered($items);
    }

    protected function getGradingLabel(int $status, ?string $status_date): string
    {
        $lng = $this->domain->lng();
        $label = match ($status) {
            \ilWikiContributor::STATUS_PASSED => $lng->txt("wiki_passed"),
            \ilWikiContributor::STATUS_FAILED => $lng->txt("wiki_failed"),
            default => $lng->txt("wiki_notgraded")
        };

        if ($status_date !== null && $status_date !== "") {
            $label .= " (" . \ilDatePresentation::formatDate(
                new \ilDateTime($status_date, IL_CAL_DATETIME)
            ) . ")";
        }

        return $label;
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();

        return $table
            ->textColumn("name", $lng->txt("wiki_contributors"), true)
            ->linkListingColumn("pages", $lng->txt("wiki_page_changes"))
            ->textColumn("grading", $lng->txt("wiki_grading"))
            ->textColumn("mark", $lng->txt("wiki_mark"))
            ->singleAction("editGrading", $lng->txt("wiki_grading"), true)
            ->singleAction("editMark", $lng->txt("wiki_mark"), true);
    }
}
