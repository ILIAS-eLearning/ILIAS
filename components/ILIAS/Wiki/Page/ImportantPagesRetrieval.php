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

class ImportantPagesRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected InternalDomainService $domain,
        protected int $ref_id,
        protected int $wiki_id,
        protected string $start_page
    ) {
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        foreach ($this->collectData() as $row) {
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
        return in_array($field, ["id", "indentation"], true);
    }

    protected function collectData(): array
    {
        $lng = $this->domain->lng();
        $templates = new \ilWikiPageTemplate($this->wiki_id);
        $data = [[
            "id" => 0,
            "title" => $this->start_page,
            "indentation" => 0,
            "purpose" => $lng->txt("wiki_start_page")
        ]];

        foreach ($this->domain->importantPage($this->ref_id)->getList() as $page) {
            $page_id = $page->getId();
            $data[] = [
                "id" => $page_id,
                "title" => \ilWikiPage::lookupTitle($page_id),
                "indentation" => $page->getIndent(),
                "purpose" => $templates->isPageTemplate($page_id)
                    ? $lng->txt("wiki_page_template")
                    : ""
            ];
        }

        return $data;
    }
}
