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
    private string $processed_search_term;
    private ?string $lastname_search_term = null;
    private ?string $firstname_search_term = null;

    public function __construct(
        private readonly int $required_search_term_length,
        private readonly string $search_term,
    ) {
        $this->processed_search_term = str_replace(
            ['%', '_'],
            ['\%', '\_'],
            trim($search_term)
        );

        $comma_separated = explode(',', $search_term);

        if (count($comma_separated) !== 2) {
            return;
        }

        $lastname_search_term = trim($comma_separated[0]);
        $firstname_search_term = trim($comma_separated[1]);

        if ($lastname_search_term . $firstname_search_term === '') {
            return;
        }

        $this->lastname_search_term = $lastname_search_term === '' ? null : $lastname_search_term;
        $this->firstname_search_term = $firstname_search_term === '' ? null : $firstname_search_term;
    }

    public function checkSearchTermLength(?string $search_term): bool
    {
        if ($search_term === null) {
            return false;
        }
        return mb_strlen($search_term) >= $this->required_search_term_length;
    }

    /**
     * The returned search term might contain wild cards or any other input.
     * Please make sure to process the string to avoid any privacy issues.
     */
    public function getUnprocessedSearchTerm(): string
    {
        return $this->search_term;
    }

    /**
     *
     * @return string|null The return value will be null, if it is determined
     * that a search for lastname and/or firstname is needed.
     */
    public function getSearchTermQueryString(): ?string
    {
        if ($this->lastname_search_term !== null
            || $this->firstname_search_term !== null) {
            return null;
        }

        return $this->search_term;
    }

    public function getLastnameQueryString(): ?string
    {
        if ($this->lastname_search_term !== null) {
            return $this->lastname_search_term;
        }

        if ($this->firstname_search_term === null) {
            return $this->search_term;
        }

        return null;
    }

    public function getFirstnameQueryString(): ?string
    {
        if ($this->firstname_search_term !== null) {
            return $this->firstname_search_term;
        }

        if ($this->lastname_search_term === null) {
            return $this->search_term;
        }

        return null;
    }
}
