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

use ilSearchSettings;

/**
 * @phpstan-type AutoCompleteUserItem array{label: string, value: string}
 * @phpstan-type AutoCompleteResult array{items: list<AutoCompleteUserItem>, hasMoreResults: bool}
 */
class LegacyAutocompleteSearchResult implements SearchResult
{
    final public const int MODE_STOP_ON_MAX_ENTRIES = 1;
    final public const int MODE_FETCH_ALL = 2;
    final public const int MAX_RESULT_ENTRIES = 1000;

    /** @var array<string, bool> */
    private array $handled_recipients = [];
    private int $mode = self::MODE_STOP_ON_MAX_ENTRIES;
    private int $max_entries;
    /** @var AutoCompleteResult */
    public array $result = [
        'items' => [],
        'hasMoreResults' => false
    ];

    public function __construct(int $mode)
    {
        $this->max_entries = ilSearchSettings::getInstance()->getAutoCompleteLength();

        $this->initMode($mode);
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function initMode(int $mode): void
    {
        if (!\in_array($mode, [self::MODE_FETCH_ALL, self::MODE_STOP_ON_MAX_ENTRIES], true)) {
            throw new \InvalidArgumentException('Wrong mode passed!');
        }
        $this->mode = $mode;
    }

    private function isResultAddable(): bool
    {
        if ($this->mode === self::MODE_STOP_ON_MAX_ENTRIES &&
            $this->max_entries >= 0 && \count($this->result['items']) >= $this->max_entries) {
            return false;
        }

        if ($this->mode === self::MODE_FETCH_ALL &&
            \count($this->result['items']) >= self::MAX_RESULT_ENTRIES) {
            return false;
        }

        return true;
    }

    public function markMoreResultsAvailable(): void
    {
        $this->result['hasMoreResults'] = true;
    }

    public function addResult(string $identifier, string $firstname, string $lastname): SearchResultStatus
    {
        if (!$this->isResultAddable()) {
            throw new \DomainException('Search result is not addable!');
        }

        if ($identifier === '') {
            return SearchResultStatus::IGNORED;
        }

        if (!isset($this->handled_recipients[$identifier])) {
            $recipient = [];
            $recipient['value'] = $identifier;

            $label = $identifier;
            if ($firstname && $lastname) {
                $label .= ' [' . $firstname . ', ' . $lastname . ']';
            }
            $recipient['label'] = $label;

            $this->result['items'][] = $recipient;
            $this->handled_recipients[$identifier] = true;

            if (!$this->isResultAddable()) {
                return SearchResultStatus::LIMIT_REACHED;
            }
        }

        return SearchResultStatus::DUPLICATE;
    }

    /**
     * @return AutoCompleteResult
     */
    public function getItems(): array
    {
        return $this->result;
    }
}
