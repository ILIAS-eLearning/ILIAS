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

namespace ILIAS\Mail\RecipientSearch;

use ILIAS\User\Search\AutocompleteQuery;

class UserSearchAutocompleteItemResult implements SearchResult
{
    /** @var list<\ILIAS\User\Search\DefaultAutocompleteItem> */
    private array $items = [];
    /** @var array<string, bool> */
    private array $handled_recipients = [];

    public function __construct(private readonly AutocompleteQuery $query)
    {
    }

    public function addResult(string $identifier, string $firstname, string $lastname): SearchResultStatus
    {
        if (!isset($this->handled_recipients[$identifier])) {
            $this->items[] = new \ILIAS\User\Search\DefaultAutocompleteItem(
                $identifier,
                $firstname,
                $lastname,
                $this->query->getUnprocessedSearchTerm()
            );

            $this->handled_recipients[$identifier] = true;

            return SearchResultStatus::ADDED;
        }

        return SearchResultStatus::DUPLICATE;
    }

    public function markMoreResultsAvailable(): void
    {
    }

    /**
     * @return list<\ILIAS\User\Search\DefaultAutocompleteItem>
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
