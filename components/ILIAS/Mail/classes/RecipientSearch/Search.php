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

/**
 * @phpstan-import-type AutoCompleteUserRecord from RecipientSearchProvider
 */
class Search
{
    /** @var list<\Iterator<AutoCompleteUserRecord>> */
    private array $providers = [];

    public function __construct(private readonly SearchResult $result)
    {
    }

    /**
     * @param \Iterator<AutoCompleteUserRecord> $provider
     */
    public function addProvider(\Iterator $provider): void
    {
        $this->providers[] = $provider;
    }

    public function search(): void
    {
        foreach ($this->providers as $provider) {
            $status = SearchResultStatus::INITIAL;
            foreach ($provider as $row) {
                if ($status === SearchResultStatus::LIMIT_REACHED) {
                    $this->result->markMoreResultsAvailable();
                    break 2;
                }

                $status = $this->result->addResult($row['login'], $row['firstname'], $row['lastname']);
            }
        }
    }
}
