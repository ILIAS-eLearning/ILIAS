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

namespace ILIAS\Search\Presentation\Result;

use ilSearchResult;
use ilLuceneSearchResultFilter;
use ilLuceneHighlighterResultParser;
use ILIAS\Data\URI;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Component\Panel\Listing\Listing as ListingPanel;
use ILIAS\Search\GUI\Global\Param;
use ILIAS\Search\Presentation\Result\ViewControls\SortationInfos;
use ILIAS\Search\Presentation\Result\ViewControls\PaginationInfos;

interface ResultPresenter
{
    /**
     * @return array{0: ListingPanel, 1: Modal[]}
     */
    public function getDirectSearchResultAsPanel(
        ilSearchResult $result,
        PaginationInfos $pagination_infos,
        SortationInfos $sortation_infos
    ): array;

    /**
     * @return array{0: ListingPanel, 1: Modal[]}
     */
    public function getLuceneSearchResultAsPanel(
        ilLuceneSearchResultFilter $result,
        ?ilLuceneHighlighterResultParser $highlighter,
        PaginationInfos $pagination_infos,
        SortationInfos $sortation_infos
    ): array;

    public function getLuceneUserSearchResultAsPanel(
        PaginationInfos $pagination_infos,
        int ...$result_user_ids
    ): ListingPanel;

    public function getSortationInfos(
        Sortation $sortation,
        URI $sortation_action,
        Param $sortation_param_name
    ): SortationInfos;

    public function getPaginationInfos(
        int $current_page,
        int $max_pages,
        int $page_size,
        URI $pagination_action,
        Param $page_param_name
    ): PaginationInfos;

    public function replacePlaceholders(string $html): string;
}
