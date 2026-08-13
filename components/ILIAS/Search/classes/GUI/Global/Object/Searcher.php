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

namespace ILIAS\Search\GUI\Global\Object;

use ilUserSearchCache;
use ILIAS\Search\Presentation\Result\ViewControls\PaginationInfos;
use ILIAS\Search\Presentation\Result\ViewControls\SortationInfos;
use ILIAS\Data\URI;
use ilSearchFilterGUI;
use ILIAS\Search\GUI\Global\SearchStateHandler;

/**
 * Until there is a unified format for search results,
 * rendering also has to be done seperately.
 */
interface Searcher
{
    /**
     * Note that the cache doubles here as an actual cache,
     * but also as a data object bundling all inputs
     * needed to search.
     * Maybe split that up in the future?
     */
    public function performSearchAndRenderResults(
        int $usr_id,
        ilUserSearchCache $cache,
        PaginationInfos $pagination_infos,
        SortationInfos $sortation_infos,
        SearchStateHandler $state_handler
    ): void;

    public function readSavedResultsAndRenderResults(
        int $usr_id,
        ilUserSearchCache $cache,
        PaginationInfos $pagination_infos,
        SortationInfos $sortation_infos
    ): void;

    public function decorateRemoteSearchTerm(string $term): string;

    public function fetchCache(int $usr_id): ilUserSearchCache;
}
