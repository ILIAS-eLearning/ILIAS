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

namespace ILIAS\Language\ComponentTranslation;

/**
 * Rule 2 of docs/development/language.md: an identifier is unique across all modules and spellings. An entry the
 * module already holds in this spelling stays allowed, so an old collision remains editable and importable.
 * Relies on modules maintained in PO files being written to lng_data as well.
 *
 * @internal
 */
final class IdentifierUniqueness
{
    /** Above this many identifiers lng_data is read once instead of per IN list: it has no index on identifier. */
    private const int FULL_READ_THRESHOLD = 1000;

    /** Duplicates named per entry, enough for a message; keeps the result linear in the number of entries. */
    private const int MAX_NAMED_DUPLICATES = 10;

    public function __construct(
        private readonly \ilDBInterface $db,
    ) {
    }

    /** @return list<string> "<module>: <identifier>" of the entries that would duplicate it */
    public function findDuplicatesOf(string $module, string $identifier): array
    {
        return self::duplicatesIn($this->lngDataEntriesOf($identifier), $module, $identifier);
    }

    /**
     * @param list<string> $modules
     * @param list<string|int> $identifiers
     * @return list<string> identifiers findDuplicatesOf() accepts for at least one module
     */
    public function filterAddable(array $modules, array $identifiers): array
    {
        $addable = [];
        foreach ($identifiers as $identifier) {
            // session topics are array keys, so a numeric topic arrives as int
            $identifier = (string) $identifier;
            $entries = $this->lngDataEntriesOf($identifier);
            foreach ($modules as $module) {
                if (self::duplicatesIn($entries, $module, $identifier) === []) {
                    $addable[] = $identifier;
                    break;
                }
            }
        }

        return $addable;
    }

    /**
     * findDuplicatesOf() for many entries at once (e.g. an import), also against each other. Other spellings are
     * matched by mb_strtolower(), not by the collation.
     *
     * @param array<string|int, array{module: string, identifier: string}> $entries
     * @param list<array{module: string, identifier: string}> $known existing entries lng_data may not hold (yet),
     *        e.g. of shipped files while a language is installed
     * @return array<string|int, list<string>> key of each entry with duplicates => "<module>: <identifier>" of these,
     *         at most MAX_NAMED_DUPLICATES
     */
    public function findDuplicatesAmong(array $entries, array $known = []): array
    {
        if ($entries === []) {
            return [];
        }
        // per lower-cased identifier: "<module>: <identifier>" => true, and how often each lower module/identifier exists
        $labels = [];
        $counts = [];
        $add = static function (string $module, string $identifier) use (&$labels, &$counts): void {
            $group = mb_strtolower($identifier);
            $labels[$group][$module . ': ' . $identifier] = true;
            $counts[$group][mb_strtolower($module)][$identifier] = ($counts[$group][mb_strtolower($module)][$identifier] ?? 0) + 1;
        };

        $candidates = [];
        foreach ($entries as $entry) {
            foreach (self::candidatesOf($entry['identifier']) as $candidate) {
                $candidates[mb_strtolower($candidate)] = $candidate;
            }
        }
        foreach ($this->lngDataEntriesAmong($candidates) as $entry) {
            $add($entry['module'], $entry['identifier']);
        }
        foreach ($known as $entry) {
            if (isset($candidates[mb_strtolower($entry['identifier'])])) {
                $add($entry['module'], $entry['identifier']);
            }
        }
        foreach ($entries as $entry) {
            $add($entry['module'], $entry['identifier']);
        }
        foreach ($labels as $group => $group_labels) {
            ksort($group_labels, SORT_STRING);
            $labels[$group] = array_slice(array_keys($group_labels), 0, self::MAX_NAMED_DUPLICATES + 1);
        }

        $duplicates = [];
        foreach ($entries as $key => $entry) {
            $module = mb_strtolower($entry['module']);
            $groups = [];
            $own = false;
            foreach (self::candidatesOf($entry['identifier']) as $candidate) {
                $group = mb_strtolower($candidate);
                $groups[$group] = true;
                // the entry itself is counted once
                $own = $own || ($counts[$group][$module][$candidate] ?? 0) > ($candidate === $entry['identifier'] ? 1 : 0);
            }
            if ($own) {
                continue;
            }
            $self = $entry['module'] . ': ' . $entry['identifier'];
            $found = [];
            foreach (array_keys($groups) as $group) {
                foreach ($labels[$group] ?? [] as $label) {
                    if ($label !== $self) {
                        $found[$label] = true;
                    }
                }
            }
            if ($found !== []) {
                ksort($found, SORT_STRING);
                $duplicates[$key] = array_slice(array_keys($found), 0, self::MAX_NAMED_DUPLICATES);
            }
        }

        return $duplicates;
    }

    /**
     * @param list<array{module: string, identifier: string}> $entries
     * @return list<string>
     */
    private static function duplicatesIn(array $entries, string $module, string $identifier): array
    {
        $candidates = self::candidatesOf($identifier);
        $duplicates = [];
        foreach ($entries as $entry) {
            if (strcasecmp($entry['module'], $module) === 0 && in_array($entry['identifier'], $candidates, true)) {
                return [];
            }
            $duplicates[$entry['module'] . ': ' . $entry['identifier']] = true;
        }
        $duplicates = array_keys($duplicates);
        sort($duplicates);

        return $duplicates;
    }

    /** @return list<array{module: string, identifier: string}> */
    private function lngDataEntriesOf(string $identifier): array
    {
        // the ci collation matches other spellings
        $set = $this->db->query(
            'SELECT module, identifier FROM lng_data WHERE '
            . $this->db->in('identifier', self::candidatesOf($identifier), false, 'text')
        );
        $entries = [];
        while ($row = $this->db->fetchAssoc($set)) {
            $entries[] = ['module' => (string) $row['module'], 'identifier' => (string) $row['identifier']];
        }

        return $entries;
    }

    /**
     * @param array<string, string> $candidates lower-cased identifier => identifier
     * @return list<array{module: string, identifier: string}> the entries whose lower-cased identifier is a key of $candidates
     */
    private function lngDataEntriesAmong(array $candidates): array
    {
        $where = count($candidates) > self::FULL_READ_THRESHOLD
            ? ''
            : ' WHERE ' . $this->db->in('identifier', array_values($candidates), false, 'text');
        $set = $this->db->query('SELECT DISTINCT module, identifier FROM lng_data' . $where);
        $entries = [];
        while ($row = $this->db->fetchAssoc($set)) {
            if (!isset($row['module'], $row['identifier'])) {
                continue;
            }
            $identifier = (string) $row['identifier'];
            if (isset($candidates[mb_strtolower($identifier)])) {
                $entries[] = ['module' => (string) $row['module'], 'identifier' => $identifier];
            }
        }

        return $entries;
    }

    /** @return list<string> $identifier and, for a form key, the identifier of its plural message */
    private static function candidatesOf(string $identifier): array
    {
        return array_values(array_unique([$identifier, PluralFormKey::parse($identifier)[0] ?? $identifier]));
    }
}
