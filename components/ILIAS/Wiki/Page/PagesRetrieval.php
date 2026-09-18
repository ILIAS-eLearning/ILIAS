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

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Wiki\InternalDomainService;

class PagesRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected InternalDomainService $domain,
        protected int $ref_id,
        protected string $mode,
        protected int $page_id,
        protected string $lang
    ) {
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        $data = $this->collectData();
        $order ??= new Order($this->getDefaultOrderField(), $this->getDefaultOrderDirection());
        $data = $this->applyOrder($data, $order);
        $data = $this->applyRange($data, $range);

        foreach ($data as $row) {
            yield $row;
        }
    }

    public function count(
        array $filter = [],
        array $parameters = []
    ): int {
        return count($this->collectData());
    }

    public function isFieldNumeric(string $field): bool
    {
        return $field === "cnt";
    }

    protected function collectData(): array
    {
        $pm = $this->domain->page()->page($this->ref_id);
        $data = [];

        switch ($this->mode) {
            case PagesTableBuilder::MODE_WHAT_LINKS_HERE:
                foreach ($this->domain->links($this->ref_id)->getLinksToPage($this->page_id, $this->lang) as $page) {
                    $data[] = [
                        "date" => $page->getLastChange(),
                        "id" => $page->getId(),
                        "user" => $page->getLastChangedUser(),
                        "title" => $page->getTitle(),
                        "lang" => $page->getLanguage()
                    ];
                }
                break;

            case PagesTableBuilder::MODE_ALL_PAGES:
                foreach ($pm->getAllPagesInfo() as $page) {
                    $data[] = [
                        "date" => $page->getLastChange(),
                        "id" => $page->getId(),
                        "user" => $page->getLastChangedUser(),
                        "title" => $page->getTitle()
                    ];
                }
                break;

            case PagesTableBuilder::MODE_NEW_PAGES:
                foreach ($pm->getNewPages() as $page) {
                    $data[] = [
                        "created" => $page->getCreated(),
                        "id" => $page->getId(),
                        "user" => $page->getCreateUser(),
                        "title" => $page->getTitle(),
                        "lang" => $page->getLanguage()
                    ];
                }
                break;

            case PagesTableBuilder::MODE_POPULAR_PAGES:
                foreach ($pm->getPopularPages() as $page) {
                    $data[] = [
                        "id" => $page->getId(),
                        "title" => $page->getTitle(),
                        "lang" => $page->getLanguage(),
                        "cnt" => $page->getViewCnt()
                    ];
                }
                break;

            case PagesTableBuilder::MODE_ORPHANED_PAGES:
                foreach ($pm->getOrphanedPages() as $page) {
                    $data[] = [
                        "id" => $page->getId(),
                        "title" => $page->getTitle(),
                        "date" => $page->getLastChange()
                    ];
                }
                break;
        }

        foreach ($data as &$row) {
            if (isset($row["user"])) {
                $row["user_sort"] = \ilUserUtil::getNamePresentation($row["user"], false, false);
            }
        }
        unset($row);

        return $data;
    }

    protected function getDefaultOrderField(): string
    {
        return match ($this->mode) {
            PagesTableBuilder::MODE_NEW_PAGES => "created",
            PagesTableBuilder::MODE_POPULAR_PAGES => "cnt",
            default => "title"
        };
    }

    protected function getDefaultOrderDirection(): string
    {
        return in_array(
            $this->mode,
            [PagesTableBuilder::MODE_NEW_PAGES, PagesTableBuilder::MODE_POPULAR_PAGES],
            true
        )
            ? Order::DESC
            : Order::ASC;
    }
}
