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

namespace ILIAS\User\Search;

/**
 * This class provides some pre-processing for search terms provided by a user
 * when searching for users. It treats strings containing one comma as being of
 * the form lastname, firstname and parses them correspondingly.
 */
class AutocompleteQuery
{
    private readonly ?string $processed_search_term;
    private readonly ?string $lastname_search_term;
    private readonly ?string $firstname_search_term;

    public function __construct(
        private readonly string $search_term,
    ) {
        $processed_search_term = str_replace(
            ['%', '_'],
            ['\%', '\_'],
            trim($search_term)
        );

        $comma_separated = explode(',', $search_term);

        if (count($comma_separated) !== 2) {
            $this->processed_search_term = $processed_search_term;
            return;
        }

        $lastname_search_term = trim($comma_separated[0]);
        $firstname_search_term = trim($comma_separated[1]);

        if ($lastname_search_term . $firstname_search_term === '') {
            $this->processed_search_term = $processed_search_term;
            return;
        }

        $this->processed_search_term = null;
        $this->lastname_search_term = $lastname_search_term === '' ? null : $lastname_search_term;
        $this->firstname_search_term = $firstname_search_term === '' ? null : $firstname_search_term;
    }

    /**
     * The length of the term is either calculated from the full length of the
     * provided search term or if the string is seen as being of the form
     * "lastname, firstname" from the longer of the two.
     */
    public function getSearchTermLength(): int
    {
        if ($this->search_term !== null) {
            return strlen($this->search_term);
        }

        return max(
            strlen($this->lastname ?? ''),
            strlen($this->firstname ?? '')
        );
    }

    /**
     * The returned search term might contain wild cards or any other input.
     * Please make sure to process the string to avoid any privacy issues.
     */
    public function getUnprocessedSearchTerm(): string
    {
        return $this->search_term;
    }

    public function getSearchTermQueryString(): ?string
    {
        if ($this->search_term === null) {
            return null;
        }

        return "%{$this->search_term}%";
    }

    public function getLastnameQueryString(): ?string
    {
        if ($this->lastname !== null) {
            return "%{$this->lastname}%";
        }

        if ($this->search_term !== null) {
            return "%{$this->search_term}%";
        }

        return null;
    }

    public function getFirstnameQueryString(): ?string
    {
        if ($this->firstname !== null) {
            return "%{$this->firstname}%";
        }

        if ($this->search_term !== null) {
            return "%{$this->search_term}%";
        }

        return null;
    }
}
