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

namespace ILIAS\Search\GUI\Global;

use ILIAS\Search\Presentation\Result\Sortation;
use ilSearchFilterGUI;
use ilUserSearchCache;
use ILIAS\Data\URI;
use ilSession;
use ILIAS\HTTP\Services as HTTP;
use ILIAS\Refinery\Factory as Refinery;

class SearchStateHandlerImpl implements SearchStateHandler
{
    public function __construct(
        protected HTTP $http,
        protected Refinery $refinery
    ) {
    }

    public function fetchMaxPage(): int
    {
        return (int) (ilSession::get(Param::MAX_PAGE->value) ?? 1);
    }

    public function resetMaxPage(): void
    {
        ilSession::clear(Param::MAX_PAGE->value);
    }

    public function updateMaxPage(int $max_page): void
    {
        ilSession::set(Param::MAX_PAGE->value, $max_page > 0 ? (string) $max_page : '1');
    }

    public function fetchRequestedPage(): int
    {
        if ($this->http->wrapper()->query()->has(Param::PAGE_NUMBER->value)) {
            // pages in the view control are 0-indexed, in search 1-indexed
            return $this->http->wrapper()->query()->retrieve(
                Param::PAGE_NUMBER->value,
                $this->refinery->kindlyTo()->int()
            ) + 1;
        }
        return 1;
    }

    public function fetchSortation(): Sortation
    {
        $sortation_string = '';
        if ($this->http->wrapper()->query()->has(Param::SORTATION->value)) {
            $sortation_string = $this->http->wrapper()->query()->retrieve(
                Param::SORTATION->value,
                $this->refinery->kindlyTo()->string()
            );
        }
        return Sortation::tryFrom($sortation_string) ?? Sortation::RELEVANCE_DESC;
    }

    /**
     * probably needs to be replaced with a completely
     * different mechanism when switching to KS
     */
    public function fetchRequestedSearchTerm(): string
    {
        if ($this->http->wrapper()->post()->has('term')) {
            return $this->http->wrapper()->post()->retrieve(
                'term',
                $this->refinery->kindlyTo()->string()
            );
        }
        return '';
    }

    /**
     * probably needs to be replaced with a completely
     * different mechanism when switching to KS
     */
    public function fetchRequestedRemoteSearchTerm(): string
    {
        if ($this->http->wrapper()->post()->has('queryString')) {
            return $this->http->wrapper()->post()->retrieve(
                'queryString',
                $this->refinery->kindlyTo()->string()
            );
        }
        return '';
    }

    /**
     * probably needs to be replaced with a completely
     * different mechanism when switching to KS
     */
    public function fetchRequestedAutoCompleteSearchTerm(): string
    {
        if ($this->http->wrapper()->post()->has('term')) {
            return $this->http->wrapper()->post()->retrieve(
                'term',
                $this->refinery->kindlyTo()->string()
            );
        }
        return '';
    }

    /**
     * probably needs to be replaced with a completely
     * different mechanism when switching to KS
     */
    public function fetchRequestedRemoteScope(): int
    {
        if ($this->http->wrapper()->post()->has('root_id')) {
            return $this->http->wrapper()->post()->retrieve(
                'root_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return ROOT_FOLDER_ID;
    }
}
