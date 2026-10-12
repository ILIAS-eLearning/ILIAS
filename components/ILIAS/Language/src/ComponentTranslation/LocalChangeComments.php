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

use DateTimeImmutable;
use ILIAS\Language\ComponentTranslation\Catalog\TranslationEntry;

/**
 * Tracks per overlay entry whether the current value still matches the value of the shipped `.po` (the
 * distinction of `lng_data.local_change`), carried on the entry itself.
 *
 * Stored as gettext translator comments (`# ...`): the compiled `.mo` has no comment section, so this
 * metadata is `.po`-only (like `lng_data` has metadata and `lng_modules` has none).
 *
 * - "original" is written once by MigratedLanguageFileSync::sync() when the overlay is first created, from
 *   the shipped `.po` value of that moment. Ordinary writes never touch it, so an admin edit cannot move the
 *   baseline - except via sync()'s $refresh_original_from_shipped, for callers that vouch that $entries
 *   reflect the current shipped content: it is updated if the shipped value changed.
 * - "local_change" is maintained solely by refresh() (called by sync() on every write): present with the
 *   write's timestamp whenever the value differs from "original" (or there is none, e.g. a key added after
 *   migration), absent otherwise. Saving the original value again therefore clears it, like
 *   "local_change IS NULL" in _deleteLangData().
 * - A plural message keeps one "original" per form (`original[<form>]: `, or `original_escaped[<form>]: `)
 *   and one "local_change" for the whole message, present if any form differs (see refreshForms()).
 * - "remark" is the administrator's remark (counterpart of lng_data.remarks, one per identifier, also for
 *   plural messages), stored like "original": verbatim as `remark: `, or encoded as `remark_escaped: ` if
 *   it contains a line break or a backslash.
 */
final class LocalChangeComments
{
    private const string ORIGINAL_PREFIX = 'original: ';
    private const string ESCAPED_ORIGINAL_PREFIX = 'original_escaped: ';
    private const string LOCAL_CHANGE_PREFIX = 'local_change: ';
    private const string REMARK_PREFIX = 'remark: ';
    private const string ESCAPED_REMARK_PREFIX = 'remark_escaped: ';
    private const string MERGED_COMMENT = 'merged_into_shipped';

    /**
     * Does not touch "local_change": refresh() recomputes it right after.
     *
     * A comment is one PO line, so a value with a line break or a backslash is stored escaped
     * ("original_escaped: ", see escape()); every other value verbatim under "original: ", as earlier
     * versions wrote it.
     */
    public static function setOriginal(TranslationEntry $entry, string $value, ?int $form = null): void
    {
        self::setEscapable($entry, self::originalPrefix(false, $form), self::originalPrefix(true, $form), $value);
    }

    /**
     * With $form `null` the "original" of a singular message, otherwise the one of that plural form.
     */
    public static function removeOriginal(TranslationEntry $entry, ?int $form = null): void
    {
        self::replace($entry, self::originalPrefix(false, $form), null);
        self::replace($entry, self::originalPrefix(true, $form), null);
    }

    /**
     * Removes the "original" of every plural form (`original[<form>]: `) - e.g. when a plural
     * message becomes a singular one.
     */
    public static function removeFormOriginals(TranslationEntry $entry): void
    {
        foreach ($entry->getTranslatorComments() as $comment) {
            if (preg_match('/\Aoriginal(?:_escaped)?\[\d+\]: /', $comment, $matches) === 1) {
                $entry->removeTranslatorCommentsStartingWith($matches[0]);
            }
        }
    }

    /**
     * The decoded "original" value as passed to setOriginal(). One written by an earlier version that
     * flattened line breaks into spaces is returned as stored (the loss is permanent); the next
     * reconciling sync rewrites it from the shipped value.
     */
    public static function getOriginal(TranslationEntry $entry, ?int $form = null): ?string
    {
        return self::getEscapable($entry, self::originalPrefix(false, $form), self::originalPrefix(true, $form));
    }

    /**
     * How many characters of a remark are kept - like lng_data.remarks.
     */
    public const int MAX_REMARK_LENGTH = 250;

    /**
     * Sets the remark of $entry - `null` or '' removes it; cut to MAX_REMARK_LENGTH characters.
     *
     * @throws \InvalidArgumentException for a remark that is not valid UTF-8 (the overlay would
     *         become unreadable)
     */
    public static function setRemark(TranslationEntry $entry, ?string $remark): void
    {
        if ($remark !== null && !mb_check_encoding($remark, 'UTF-8')) {
            throw new \InvalidArgumentException(sprintf('The remark of "%s" is not valid UTF-8.', $entry->getId()));
        }
        $remark = $remark === null ? null : mb_substr($remark, 0, self::MAX_REMARK_LENGTH);
        if ($remark === null || $remark === '') {
            self::replace($entry, self::REMARK_PREFIX, null);
            self::replace($entry, self::ESCAPED_REMARK_PREFIX, null);
            return;
        }
        self::setEscapable($entry, self::REMARK_PREFIX, self::ESCAPED_REMARK_PREFIX, $remark);
    }

    /**
     * Marks $entry as taken over into the shipped `.po` by "merge" (see ShippedPoMerger) - or removes
     * the mark. While the build does not serve that `.po` yet, MigratedLanguageFileSync::sync()
     * keeps a marked entry in the overlay even though its value equals the shipped one.
     */
    public static function setMergedIntoShipped(TranslationEntry $entry, bool $merged): void
    {
        $entry->removeTranslatorCommentsStartingWith(self::MERGED_COMMENT);
        if ($merged) {
            $entry->addTranslatorComment(self::MERGED_COMMENT);
        }
    }

    public static function isMergedIntoShipped(TranslationEntry $entry): bool
    {
        return in_array(self::MERGED_COMMENT, $entry->getTranslatorComments(), true);
    }

    /**
     * The decoded remark of $entry, exactly as it was passed to setRemark(), `null` without one.
     */
    public static function getRemark(TranslationEntry $entry): ?string
    {
        return self::getEscapable($entry, self::REMARK_PREFIX, self::ESCAPED_REMARK_PREFIX);
    }

    /**
     * @return string|null ISO-8601 UTC ('Y-m-d\TH:i:s\Z') or null if the entry is unmodified
     */
    public static function getLocalChange(TranslationEntry $entry): ?string
    {
        return self::find($entry, self::LOCAL_CHANGE_PREFIX);
    }

    /**
     * getLocalChange() in the database's "Y-m-d H:i:s" (UTC) format, as used by
     * lng_data.local_change, or null if unmodified or unparsable.
     */
    public static function getLocalChangeAsDatabaseTimestamp(TranslationEntry $entry): ?string
    {
        $change = self::getLocalChange($entry);
        if ($change === null) {
            return null;
        }
        $date = DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s\Z', $change, new \DateTimeZone('UTC'));

        return $date === false ? null : $date->format('Y-m-d H:i:s');
    }

    /**
     * Called by MigratedLanguageFileSync::sync() for every entry it writes, after the new value was set.
     * $previous_value avoids bumping the timestamp on an idempotent re-save of an already changed value.
     */
    public static function refresh(
        TranslationEntry $entry,
        string $previous_value,
        string $value,
        DateTimeImmutable $now
    ): void {
        $original = self::getOriginal($entry);
        $is_local_change = $original === null || $original !== $value;

        if (!$is_local_change) {
            self::replace($entry, self::LOCAL_CHANGE_PREFIX, null);
            return;
        }

        if ($previous_value === $value && self::getLocalChange($entry) !== null) {
            return;
        }

        self::replace($entry, self::LOCAL_CHANGE_PREFIX, $now->format('Y-m-d\TH:i:s\Z'));
    }

    /**
     * refresh() for a plural message: $forms are its new forms, $previous_forms the ones before this
     * write. "local_change" is present whenever any form differs from its "original" (or has none).
     *
     * @param list<string> $previous_forms
     * @param list<string> $forms
     */
    public static function refreshForms(
        TranslationEntry $entry,
        array $previous_forms,
        array $forms,
        DateTimeImmutable $now
    ): void {
        $is_local_change = false;
        foreach ($forms as $form => $value) {
            $original = self::getOriginal($entry, $form);
            if ($original === null || $original !== $value) {
                $is_local_change = true;
                break;
            }
        }

        if (!$is_local_change) {
            self::replace($entry, self::LOCAL_CHANGE_PREFIX, null);
            return;
        }

        if ($previous_forms === $forms && self::getLocalChange($entry) !== null) {
            return;
        }

        self::replace($entry, self::LOCAL_CHANGE_PREFIX, $now->format('Y-m-d\TH:i:s\Z'));
    }

    /**
     * The comment prefix of an "original": "original: "/"original_escaped: " of a singular message,
     * "original[<form>]: "/"original_escaped[<form>]: " of a plural form.
     */
    private static function originalPrefix(bool $escaped, ?int $form): string
    {
        $prefix = $escaped ? self::ESCAPED_ORIGINAL_PREFIX : self::ORIGINAL_PREFIX;

        return $form === null ? $prefix : substr($prefix, 0, -2) . '[' . $form . ']: ';
    }

    /**
     * Stores $value under $prefix, or under $escaped_prefix if it needs escaping (see
     * needsEscaping()) - used by setOriginal() and setRemark(), which only differ in their prefixes.
     */
    private static function setEscapable(TranslationEntry $entry, string $prefix, string $escaped_prefix, string $value): void
    {
        [$prefix, $other_prefix, $stored] = self::needsEscaping($value)
            ? [$escaped_prefix, $prefix, self::escape($value)]
            : [$prefix, $escaped_prefix, $value];

        // Unchanged: keep the comment where it is, so re-running a sync produces identical output
        if (self::find($entry, $prefix) === $stored && self::find($entry, $other_prefix) === null) {
            return;
        }
        self::replace($entry, $other_prefix, null);
        self::replace($entry, $prefix, $stored);
    }

    /**
     * The value stored under $escaped_prefix (decoded) or $prefix, `null` if neither is present -
     * the read side of setEscapable().
     */
    private static function getEscapable(TranslationEntry $entry, string $prefix, string $escaped_prefix): ?string
    {
        $escaped = self::find($entry, $escaped_prefix);
        if ($escaped !== null) {
            return self::unescape($escaped);
        }

        return self::find($entry, $prefix);
    }

    private static function find(TranslationEntry $entry, string $prefix): ?string
    {
        foreach ($entry->getTranslatorComments() as $comment) {
            if (str_starts_with($comment, $prefix)) {
                return substr($comment, strlen($prefix));
            }
        }

        return null;
    }

    private static function replace(TranslationEntry $entry, string $prefix, ?string $value): void
    {
        $entry->removeTranslatorCommentsStartingWith($prefix);
        if ($value !== null) {
            $entry->addTranslatorComment($prefix . $value);
        }
    }

    private static function needsEscaping(string $value): bool
    {
        return strpbrk($value, "\\\n\r") !== false;
    }

    /**
     * Reversible single-line encoding: a backslash is doubled, a line feed becomes backslash + "n"
     * and a carriage return becomes backslash + "r".
     */
    private static function escape(string $value): string
    {
        return strtr($value, ['\\' => '\\\\', "\n" => '\\n', "\r" => '\\r']);
    }

    private static function unescape(string $value): string
    {
        return strtr($value, ['\\\\' => '\\', '\\n' => "\n", '\\r' => "\r"]);
    }
}
