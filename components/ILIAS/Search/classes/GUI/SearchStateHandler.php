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

namespace ILIAS\Search\GUI;

use ILIAS\Search\Presentation\Result\Sortation;
use ilSearchFilterGUI;
use ilUserSearchCache;
use ILIAS\Data\URI;

interface SearchStateHandler
{
    public function fetchMaxPage(): int;

    public function resetMaxPage(): void;

    public function updateMaxPage(int $max_page): void;

    public function fetchRequestedPage(): int;

    public function fetchSortation(): Sortation;

    public function fetchRequestedSearchTerm(): string;

    public function fetchRequestedRemoteSearchTerm(): string;

    public function fetchRequestedAutoCompleteSearchTerm(): string;

    public function fetchRequestedRemoteScope(): int;

    public function fetchCache(int $usr_id): ilUserSearchCache;

    public function fetchFilter(URI $action): ilSearchFilterGUI;

    public function loadFilterToCache(ilSearchFilterGUI $filter, ilUserSearchCache $cache): void;
}
