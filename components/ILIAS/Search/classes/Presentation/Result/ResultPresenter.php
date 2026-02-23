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
use ILIAS\Search\GUI\Param;

interface ResultPresenter
{
    /**
     * @return array{0: ListingPanel, 1: Modal[]}
     */
    public function getDirectSearchResultAsPanel(
        ilSearchResult $result,
        ViewControlInfos $view_control_infos
    ): array;

    /**
     * @return array{0: ListingPanel, 1: Modal[]}
     */
    public function getLuceneSearchResultAsPanel(
        ilLuceneSearchResultFilter $result,
        ilLuceneHighlighterResultParser $highlighter,
        ViewControlInfos $view_control_infos
    ): array;

    public function getViewControlInfos(
        Sortation $sortation,
        int $current_page,
        int $max_pages,
        int $page_size,
        URI $pagination_action,
        Param $page_param_name,
        URI $sortation_action,
        Param $sortation_param_name
    ): ViewControlInfos;

    public function replacePlaceholders(string $html): string;
}
