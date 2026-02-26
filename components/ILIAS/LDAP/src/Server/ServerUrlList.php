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

namespace ILIAS\LDAP\Server;

use Generator;
use ILIAS\Data\URI;

/**
 * Value object representing a list of LDAP server URLs (primary plus fallbacks).
 * Each entry is either a valid \ILIAS\Data\URI or a raw string (unparseable).
 */
class ServerUrlList implements \Stringable
{
    /** @var list<URI|string> */
    private array $entries;

    /**
     * @param list<URI|string> $entries
     */
    public function __construct(array $entries = [])
    {
        $this->entries = array_values($entries);
    }

    /**
     * Create from string representation (comma-separated, as stored in DB or form).
     * Empty string yields an empty list. Does not throw.
     */
    public static function fromString(string $stored): self
    {
        $stored = trim($stored);
        if ($stored === '') {
            return new self([]);
        }

        $parts = array_map(
            static fn(string $s): string => trim($s),
            explode(',', $stored)
        );

        $entries = [];
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            try {
                $entries[] = new URI($part);
            } catch (\Throwable) {
                $entries[] = $part;
            }
        }

        return new self($entries);
    }

    /** Returns the number of entries (valid and invalid). */
    public function count(): int
    {
        return \count($this->entries);
    }

    /**
     * Connection string for ldap_connect() at the given index (0 = primary).
     * Returns empty string if index is out of range or negative.
     */
    public function getConnectionStringAtIndex(int $index): string
    {
        if ($index < 0 || !\array_key_exists($index, $this->entries)) {
            return '';
        }

        $el = $this->entries[$index];

        return (string) $el;
    }

    /**
     * Convert to stored form (comma-separated string for database and form).
     */
    public function toString(): string
    {
        $strings = array_map(
            static fn(URI|string $el): string => $el instanceof URI ? (string) $el : (string) $el,
            $this->entries
        );

        return implode(',', $strings);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Entries that could not be parsed as URI (for GUI validation messages).
     *
     * @return list<string>
     */
    public function getInvalidParts(): array
    {
        $invalid = [];
        foreach ($this->entries as $el) {
            if (!($el instanceof URI)) {
                $invalid[] = $el;
            }
        }

        return $invalid;
    }

    /**
     * New list with first entry moved to the end (for persisted fallback rotation).
     * Returns this instance unchanged if the list has fewer than two entries.
     */
    public function rotate(): self
    {
        if (\count($this->entries) < 2) {
            return $this;
        }

        $rotated = array_merge(
            \array_slice($this->entries, 1),
            [$this->entries[0]]
        );

        return new self($rotated);
    }

    /**
     * New list with the entry at $index moved to primary (index 0).
     * Returns this instance unchanged if index is out of range or negative.
     */
    public function withPrimaryAt(int $index): self
    {
        if ($index < 0 || !\array_key_exists($index, $this->entries)) {
            return $this;
        }

        $entry = $this->entries[$index];
        $rest = array_merge(
            \array_slice($this->entries, 0, $index),
            \array_slice($this->entries, $index + 1)
        );

        return new self(array_merge([$entry], $rest));
    }

    /**
     * Iterate over valid URL entries only (invalid/raw string entries are skipped).
     * Yields index => URI so that withPrimaryAt(index) correctly moves the connected server to primary.
     *
     * @return Generator<int, URI>
     */
    public function validUrls(): Generator
    {
        foreach ($this->entries as $index => $entry) {
            if ($entry instanceof URI) {
                yield $index => $entry;
            }
        }
    }
}
