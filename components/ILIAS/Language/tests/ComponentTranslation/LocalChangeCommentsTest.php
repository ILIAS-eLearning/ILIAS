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

use ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog;
use ILIAS\Language\ComponentTranslation\Catalog\TranslationEntry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The PO/MO pilot's "locally changed" tracking: lng_data.local_change/remarks reimplemented as gettext translator
 * comments, so it works without lng_data. Unit tests of the comment bookkeeping, isolated from
 * ilObjLanguage::syncMigratedLanguageFile().
 */
class LocalChangeCommentsTest extends TestCase
{
    public function testANewlyMigratedEntryHasNoLocalChangeRightAfterOriginalIsSet(): void
    {
        $translation = new TranslationEntry('demo', 'greeting');
        LocalChangeComments::setOriginal($translation, 'Hallo');

        $this->assertSame('Hallo', LocalChangeComments::getOriginal($translation));
        $this->assertNull(LocalChangeComments::getLocalChange($translation));
    }

    public function testRefreshSetsALocalChangeTimestampWhenTheValueDiffersFromTheOriginal(): void
    {
        $translation = new TranslationEntry('demo', 'greeting');
        LocalChangeComments::setOriginal($translation, 'Hallo');

        $now = new \DateTimeImmutable('2026-09-21T10:00:00Z');
        LocalChangeComments::refresh($translation, 'Hallo', 'Hallo, geändert', $now);

        $this->assertSame('2026-09-21T10:00:00Z', LocalChangeComments::getLocalChange($translation));
    }

    public function testRefreshClearsTheLocalChangeWhenTheValueIsSetBackToTheOriginal(): void
    {
        $translation = new TranslationEntry('demo', 'greeting');
        LocalChangeComments::setOriginal($translation, 'Hallo');
        LocalChangeComments::refresh(
            $translation,
            'Hallo',
            'Hallo, geändert',
            new \DateTimeImmutable('2026-09-21T10:00:00Z')
        );

        LocalChangeComments::refresh(
            $translation,
            'Hallo, geändert',
            'Hallo',
            new \DateTimeImmutable('2026-09-21T14:00:00Z')
        );

        $this->assertNull(LocalChangeComments::getLocalChange($translation));
        // "original" itself must never be touched by refresh()
        $this->assertSame('Hallo', LocalChangeComments::getOriginal($translation));
    }

    public function testRefreshDoesNotBumpAnAlreadyCurrentTimestampOnAnIdempotentResave(): void
    {
        $translation = new TranslationEntry('demo', 'greeting');
        LocalChangeComments::setOriginal($translation, 'Hallo');
        LocalChangeComments::refresh(
            $translation,
            'Hallo',
            'Hallo, geändert',
            new \DateTimeImmutable('2026-09-21T10:00:00Z')
        );

        // same value written again, e.g. an unrelated key in the same module was edited too
        LocalChangeComments::refresh(
            $translation,
            'Hallo, geändert',
            'Hallo, geändert',
            new \DateTimeImmutable('2026-09-21T12:00:00Z')
        );

        $this->assertSame('2026-09-21T10:00:00Z', LocalChangeComments::getLocalChange($translation));
    }

    public function testRefreshUpdatesAnExistingTimestampWhenTheValueChangesAgain(): void
    {
        $translation = new TranslationEntry('demo', 'greeting');
        LocalChangeComments::setOriginal($translation, 'Hallo');
        LocalChangeComments::refresh(
            $translation,
            'Hallo',
            'Hallo, geändert',
            new \DateTimeImmutable('2026-09-21T10:00:00Z')
        );

        LocalChangeComments::refresh(
            $translation,
            'Hallo, geändert',
            'Hallo, nochmal geändert',
            new \DateTimeImmutable('2026-09-21T12:00:00Z')
        );

        $this->assertSame('2026-09-21T12:00:00Z', LocalChangeComments::getLocalChange($translation));
    }

    /**
     * A key without a baseline (never converted, e.g. added by a later admin edit) counts as always locally changed,
     * like replaceLangEntry() does for a new lng_data row.
     */
    public function testRefreshTreatsAMissingOriginalAsAlwaysLocallyChanged(): void
    {
        $translation = new TranslationEntry('demo', 'brand_new_key');

        LocalChangeComments::refresh($translation, '', 'Neuer Wert', new \DateTimeImmutable('2026-09-21T15:00:00Z'));

        $this->assertNull(LocalChangeComments::getOriginal($translation));
        $this->assertSame('2026-09-21T15:00:00Z', LocalChangeComments::getLocalChange($translation));
    }

    /**
     * A naive "add a comment" on every refresh() would leave every past timestamp sitting in the
     * file forever. Exactly one "local_change: " line must exist after multiple refreshes.
     */
    public function testRefreshNeverLeavesMoreThanOneLocalChangeCommentBehind(): void
    {
        $translation = new TranslationEntry('demo', 'greeting');
        LocalChangeComments::setOriginal($translation, 'Hallo');

        LocalChangeComments::refresh($translation, 'Hallo', 'A', new \DateTimeImmutable('2026-09-21T10:00:00Z'));
        LocalChangeComments::refresh($translation, 'A', 'B', new \DateTimeImmutable('2026-09-21T11:00:00Z'));
        LocalChangeComments::refresh($translation, 'B', 'C', new \DateTimeImmutable('2026-09-21T12:00:00Z'));

        $localChangeComments = array_values(array_filter(
            $translation->getTranslatorComments(),
            static fn(string $comment): bool => str_starts_with($comment, 'local_change: ')
        ));

        $this->assertCount(1, $localChangeComments);
        $this->assertSame('local_change: 2026-09-21T12:00:00Z', $localChangeComments[0]);
    }

    /**
     * A translator comment is a single PO line: a value with a line break or backslash is stored reversibly escaped
     * under its own prefix and read back exactly as it was set.
     */
    #[DataProvider('valuesNeedingEscaping')]
    public function testAValueWithALineBreakOrBackslashRoundTripsExactlyViaASingleEscapedComment(
        string $value,
        string $expected_comment
    ): void {
        $entry = new TranslationEntry('demo', 'greeting');
        LocalChangeComments::setOriginal($entry, $value);

        $this->assertSame([$expected_comment], $entry->getTranslatorComments());
        $this->assertSame($value, LocalChangeComments::getOriginal($entry));
    }

    public static function valuesNeedingEscaping(): array
    {
        return [
            'LF' => ["Zeile eins\nZeile zwei", 'original_escaped: Zeile eins\\nZeile zwei'],
            'CRLF' => ["Zeile eins\r\nZeile zwei", 'original_escaped: Zeile eins\\r\\nZeile zwei'],
            'lone CR' => ["a\rb", 'original_escaped: a\\rb'],
            'trailing LF' => ["Hallo\n", 'original_escaped: Hallo\\n'],
            'backslash' => ['C:\\pfad', 'original_escaped: C:\\\\pfad'],
            // A literal backslash followed by "n" must not come back as a line feed
            'literal backslash-n' => ['a\\nb', 'original_escaped: a\\\\nb'],
            'literal backslash before a real LF' => ["a\\\nb", 'original_escaped: a\\\\\\nb'],
            'double backslash' => ['a\\\\b', 'original_escaped: a\\\\\\\\b'],
            'only a line break' => ["\n", 'original_escaped: \\n'],
        ];
    }

    public static function valuesSurvivingAPoFileRoundTrip(): array
    {
        return self::valuesNeedingEscaping() + [
            'leading and trailing spaces' => ['  Hallo  ', 'original:   Hallo  '],
            'trailing tab' => ["Hallo\t", "original: Hallo\t"],
            'only a space' => [' ', 'original:  '],
            'empty' => ['', 'original: '],
            'zero' => ['0', 'original: 0'],
            'escaped with surrounding spaces' => ["  a\nb  ", 'original_escaped:   a\\nb  '],
        ];
    }

    /**
     * Every other value is stored verbatim under "original: ", byte-identical to earlier versions, so existing overlays
     * are read and rewritten unchanged.
     */
    public function testAValueWithoutLineBreakOrBackslashIsStoredVerbatimUnderTheLegacyPrefix(): void
    {
        $entry = new TranslationEntry('demo', 'greeting');
        LocalChangeComments::setOriginal($entry, 'Hallo "Welt" %s #:# ###');

        $this->assertSame(['original: Hallo "Welt" %s #:# ###'], $entry->getTranslatorComments());
    }

    /**
     * An "original: " comment written by an earlier version (line breaks already flattened into
     * spaces, a backslash stored unescaped) is returned exactly as stored - never unescaped.
     */
    #[DataProvider('legacyOriginalComments')]
    public function testALegacyOriginalCommentIsReadVerbatim(string $comment, string $expected): void
    {
        $entry = new TranslationEntry('demo', 'greeting');
        $entry->addTranslatorComment($comment);

        $this->assertSame($expected, LocalChangeComments::getOriginal($entry));
    }

    public static function legacyOriginalComments(): array
    {
        return [
            'flattened line break' => ['original: a b', 'a b'],
            'unescaped backslash' => ['original: C:\\pfad', 'C:\\pfad'],
            'backslash-n stays two characters' => ['original: a\\nb', 'a\\nb'],
        ];
    }

    /**
     * A legacy comment holding a value that now needs escaping reads as unchanged (no local change),
     * and setting that same value again migrates it to exactly one escaped comment.
     */
    public function testALegacyCommentWithABackslashIsMigratedToTheEscapedPrefixWithoutALocalChange(): void
    {
        $entry = new TranslationEntry('demo', 'greeting');
        $entry->addTranslatorComment('original: C:\\pfad');

        LocalChangeComments::refresh($entry, 'C:\\pfad', 'C:\\pfad', new \DateTimeImmutable('2026-09-21T10:00:00Z'));
        $this->assertNull(LocalChangeComments::getLocalChange($entry));

        LocalChangeComments::setOriginal($entry, 'C:\\pfad');
        $this->assertSame(['original_escaped: C:\\\\pfad'], $entry->getTranslatorComments());
        $this->assertSame('C:\\pfad', LocalChangeComments::getOriginal($entry));
    }

    /**
     * Switching between a value that needs escaping and one that does not never leaves both prefixes
     * behind (getOriginal() prefers the escaped one - a stale one would win).
     */
    public function testSwitchingBetweenEscapedAndVerbatimValuesKeepsExactlyOneOriginalComment(): void
    {
        $entry = new TranslationEntry('demo', 'greeting');
        $entry->addTranslatorComment('translator note');

        LocalChangeComments::setOriginal($entry, "Zeile\nzwei");
        LocalChangeComments::setOriginal($entry, 'einzeilig');
        $this->assertSame(['translator note', 'original: einzeilig'], $entry->getTranslatorComments());
        $this->assertSame('einzeilig', LocalChangeComments::getOriginal($entry));

        LocalChangeComments::setOriginal($entry, "wieder\nmehrzeilig");
        $this->assertSame(['translator note', 'original_escaped: wieder\\nmehrzeilig'], $entry->getTranslatorComments());
        $this->assertSame("wieder\nmehrzeilig", LocalChangeComments::getOriginal($entry));
    }

    /**
     * Idempotence for escaped values, too: setting the same multi-line value again keeps the comment
     * where it is (no-op guard of MigratedLanguageFileSync::sync()).
     */
    public function testSetOriginalWithTheSameEscapedValueKeepsTheCommentOrderStable(): void
    {
        $entry = new TranslationEntry('demo', 'greeting');
        LocalChangeComments::setOriginal($entry, "Zeile\r\nzwei");
        $entry->addTranslatorComment('translator note');

        LocalChangeComments::setOriginal($entry, "Zeile\r\nzwei");

        $this->assertSame(['original_escaped: Zeile\\r\\nzwei', 'translator note'], $entry->getTranslatorComments());
    }

    /**
     * Both "original" comments present (a hand-edited or mixed file): setOriginal() with the value of
     * one of them still cleans up the other one instead of treating the entry as unchanged.
     */
    public function testSetOriginalRemovesAStaleCommentOfTheOtherPrefix(): void
    {
        $entry = new TranslationEntry('demo', 'greeting');
        $entry->addTranslatorComment('original: Hallo');
        $entry->addTranslatorComment('original_escaped: Alt\\nZeile');

        LocalChangeComments::setOriginal($entry, 'Hallo');

        $this->assertSame(['original: Hallo'], $entry->getTranslatorComments());
        $this->assertSame('Hallo', LocalChangeComments::getOriginal($entry));
    }

    /**
     * refresh() compares against the DECODED original: an unchanged multi-line value is no local
     * change, a different one is.
     */
    public function testRefreshComparesAMultiLineValueAgainstTheDecodedOriginal(): void
    {
        $entry = new TranslationEntry('demo', 'greeting');
        LocalChangeComments::setOriginal($entry, "Zeile eins\r\nZeile zwei");

        LocalChangeComments::refresh($entry, "Zeile eins\r\nZeile zwei", "Zeile eins\r\nZeile zwei", new \DateTimeImmutable('2026-09-21T10:00:00Z'));
        $this->assertNull(LocalChangeComments::getLocalChange($entry));

        LocalChangeComments::refresh($entry, "Zeile eins\r\nZeile zwei", "Zeile eins\nZeile zwei", new \DateTimeImmutable('2026-09-21T11:00:00Z'));
        $this->assertSame('2026-09-21T11:00:00Z', LocalChangeComments::getLocalChange($entry));
    }

    /**
     * The "original" comment survives a toPoString()/fromPoString() round trip byte for byte - and
     * so does the decoded value: an escaped one (the writer would otherwise flatten a raw line break
     * into a space) as well as a verbatim one with surrounding whitespace (gettext/gettext must not
     * trim comment text - a trimmed "original" would never match the value again, and an empty one
     * would lose its prefix's trailing space and turn into "no original" = locally changed).
     */
    #[DataProvider('valuesSurvivingAPoFileRoundTrip')]
    public function testTheOriginalSurvivesAPoFileRoundTrip(string $value, string $expected_comment): void
    {
        $entry = new TranslationEntry('demo', 'greeting');
        $entry->translate($value);
        LocalChangeComments::setOriginal($entry, $value);
        $entry->addTranslatorComment('local_change: 2026-09-21T10:00:00Z');
        $catalog = new TranslationCatalog();
        $catalog->add($entry);

        $parsed = TranslationCatalog::fromPoString($catalog->toPoString())->find('demo', 'greeting');

        $this->assertNotNull($parsed);
        $this->assertSame([$expected_comment, 'local_change: 2026-09-21T10:00:00Z'], $parsed->getTranslatorComments());
        $this->assertSame($value, LocalChangeComments::getOriginal($parsed));
        $this->assertSame($value, $parsed->getTranslation());
    }

    /**
     * setOriginal() with the value already stored must not move the comment: a re-run of an
     * unchanged sync has to produce byte-identical output (no-op guard in MigratedLanguageFileSync).
     */
    public function testSetOriginalWithTheSameValueKeepsTheCommentOrderStable(): void
    {
        $entry = new TranslationEntry('demo', 'greeting');
        LocalChangeComments::setOriginal($entry, 'Hallo');
        $entry->addTranslatorComment('translator note');

        LocalChangeComments::setOriginal($entry, 'Hallo');

        $this->assertSame(['original: Hallo', 'translator note'], $entry->getTranslatorComments());
    }

    public function testSetOriginalWithADifferentValueReplacesTheOldOriginalInsteadOfAddingASecondOne(): void
    {
        $entry = new TranslationEntry('demo', 'greeting');
        LocalChangeComments::setOriginal($entry, 'Hallo');
        $entry->addTranslatorComment('translator note');

        LocalChangeComments::setOriginal($entry, 'Servus');

        $this->assertSame(['translator note', 'original: Servus'], $entry->getTranslatorComments());
        $this->assertSame('Servus', LocalChangeComments::getOriginal($entry));
    }

    /**
     * "original" is compared byte for byte against the value - leading/trailing whitespace is part
     * of it and must neither be trimmed on write nor lost on read.
     */
    public function testOriginalKeepsLeadingAndTrailingWhitespaceExactly(): void
    {
        $entry = new TranslationEntry('demo', 'greeting');
        LocalChangeComments::setOriginal($entry, '  Hallo ');

        $this->assertSame('  Hallo ', LocalChangeComments::getOriginal($entry));

        LocalChangeComments::refresh($entry, '  Hallo ', '  Hallo ', new \DateTimeImmutable('2026-09-21T10:00:00Z'));
        $this->assertNull(LocalChangeComments::getLocalChange($entry));
    }

    /**
     * An empty string is a valid original (an untranslated shipped entry) and must not be confused
     * with "no original at all", which would mark the entry as locally changed.
     */
    public function testAnEmptyOriginalIsDistinctFromNoOriginal(): void
    {
        $entry = new TranslationEntry('demo', 'untranslated');
        LocalChangeComments::setOriginal($entry, '');

        $this->assertSame('', LocalChangeComments::getOriginal($entry));
        LocalChangeComments::refresh($entry, '', '', new \DateTimeImmutable('2026-09-21T10:00:00Z'));
        $this->assertNull(LocalChangeComments::getLocalChange($entry));
    }

    /**
     * removeOriginal() removes both prefixes - an escaped "original" left behind would still be
     * returned by getOriginal() (e.g., when refresh() drops an entry the shipped .po no longer has).
     */
    public function testRemoveOriginalRemovesBothTheLegacyAndTheEscapedPrefix(): void
    {
        $entry = new TranslationEntry('demo', 'greeting');
        $entry->addTranslatorComment('original: Hallo');
        $entry->addTranslatorComment('translator note');
        $entry->addTranslatorComment('original_escaped: Zeile\\nzwei');

        LocalChangeComments::removeOriginal($entry);

        $this->assertNull(LocalChangeComments::getOriginal($entry));
        $this->assertSame(['translator note'], $entry->getTranslatorComments());
    }

    public function testRemoveOriginalLeavesOtherCommentsAlone(): void
    {
        $entry = new TranslationEntry('demo', 'greeting');
        $entry->addTranslatorComment('translator note');
        LocalChangeComments::setOriginal($entry, 'Hallo');
        LocalChangeComments::refresh($entry, 'Hallo', 'Hi', new \DateTimeImmutable('2026-09-21T10:00:00Z'));

        LocalChangeComments::removeOriginal($entry);

        $this->assertNull(LocalChangeComments::getOriginal($entry));
        $this->assertSame(
            ['translator note', 'local_change: 2026-09-21T10:00:00Z'],
            $entry->getTranslatorComments()
        );
    }

    public function testGetLocalChangeAsDatabaseTimestampConvertsTheIsoFormat(): void
    {
        $entry = new TranslationEntry('demo', 'greeting');
        LocalChangeComments::refresh($entry, '', 'Hi', new \DateTimeImmutable('2026-12-31T23:59:59Z'));

        $this->assertSame('2026-12-31 23:59:59', LocalChangeComments::getLocalChangeAsDatabaseTimestamp($entry));
    }

    public function testGetLocalChangeAsDatabaseTimestampIsNullWithoutALocalChange(): void
    {
        $entry = new TranslationEntry('demo', 'greeting');
        LocalChangeComments::setOriginal($entry, 'Hallo');

        $this->assertNull(LocalChangeComments::getLocalChangeAsDatabaseTimestamp($entry));
    }

    /**
     * A hand-edited or otherwise mangled timestamp must not turn into a bogus date that then wins a
     * max() comparison against lng_data.local_change.
     */
    public function testGetLocalChangeAsDatabaseTimestampIsNullForAnUnparsableTimestamp(): void
    {
        $entry = new TranslationEntry('demo', 'greeting');
        $entry->addTranslatorComment('local_change: yesterday');

        $this->assertSame('yesterday', LocalChangeComments::getLocalChange($entry));
        $this->assertNull(LocalChangeComments::getLocalChangeAsDatabaseTimestamp($entry));
    }

    public function testGetRemarkIsNullWithoutOneSet(): void
    {
        $entry = new TranslationEntry('demo', 'greeting');

        $this->assertNull(LocalChangeComments::getRemark($entry));
    }

    public function testSetRemarkAndGetRemarkRoundTripAPlainValueVerbatim(): void
    {
        $entry = new TranslationEntry('demo', 'greeting');
        LocalChangeComments::setRemark($entry, 'Bitte prüfen');

        $this->assertSame(['remark: Bitte prüfen'], $entry->getTranslatorComments());
        $this->assertSame('Bitte prüfen', LocalChangeComments::getRemark($entry));
    }

    /**
     * Same escaping mechanism as "original" (setEscapable()), just under its own prefix - a
     * remark with a line break or backslash round-trips via a single "remark_escaped: " comment.
     */
    #[DataProvider('valuesNeedingEscaping')]
    public function testSetRemarkEscapesALineBreakOrBackslashLikeSetOriginalDoes(string $value, string $unused_original_comment): void
    {
        $entry = new TranslationEntry('demo', 'greeting');
        LocalChangeComments::setRemark($entry, $value);

        $comments = $entry->getTranslatorComments();
        $this->assertCount(1, $comments);
        $this->assertStringStartsWith('remark_escaped: ', $comments[0]);
        $this->assertSame($value, LocalChangeComments::getRemark($entry));
    }

    public function testSetRemarkKeepsLeadingAndTrailingWhitespaceExactly(): void
    {
        $entry = new TranslationEntry('demo', 'greeting');
        LocalChangeComments::setRemark($entry, '  Bemerkung  ');

        $this->assertSame('  Bemerkung  ', LocalChangeComments::getRemark($entry));
    }

    /**
     * An empty remark is treated the same as no remark at all - unlike "original", which
     * deliberately distinguishes "" from absent (see testAnEmptyOriginalIsDistinctFromNoOriginal()):
     * a remark has no such "matches the shipped value" semantics, so there is nothing to keep an
     * empty marker for.
     */
    public function testSetRemarkWithAnEmptyStringRemovesIt(): void
    {
        $entry = new TranslationEntry('demo', 'greeting');
        LocalChangeComments::setRemark($entry, 'Bitte prüfen');

        LocalChangeComments::setRemark($entry, '');

        $this->assertNull(LocalChangeComments::getRemark($entry));
        $this->assertSame([], $entry->getTranslatorComments());
    }

    public function testSetRemarkWithNullRemovesAnExistingOne(): void
    {
        $entry = new TranslationEntry('demo', 'greeting');
        LocalChangeComments::setRemark($entry, 'Bitte prüfen');

        LocalChangeComments::setRemark($entry, null);

        $this->assertNull(LocalChangeComments::getRemark($entry));
    }

    /**
     * Invalid UTF-8 would make the overlay `.po` itself unreadable (TranslationCatalog::fromPoString()
     * rejects non-UTF-8 content) - rejected here instead, before it ever reaches a comment.
     */
    public function testSetRemarkThrowsForInvalidUtf8(): void
    {
        $entry = new TranslationEntry('demo', 'greeting');

        $this->expectException(\InvalidArgumentException::class);
        LocalChangeComments::setRemark($entry, "Ung\xFCltig");
    }

    /**
     * lng_data.remarks is limited to 250 characters (see ilObjLanguage::replaceLangEntry()) - the
     * overlay's remark is cut to the same MAX_REMARK_LENGTH, character-wise (mb_substr(), not byte-
     * wise) so a multi-byte character straddling the cut point is never split into invalid UTF-8.
     */
    public function testSetRemarkIsCutToMaxRemarkLengthCharactersNotBytes(): void
    {
        $entry = new TranslationEntry('demo', 'greeting');
        // 250 "ä" (2 bytes each in UTF-8) plus one more character right at the boundary
        $remark = str_repeat('ä', LocalChangeComments::MAX_REMARK_LENGTH) . 'X';

        LocalChangeComments::setRemark($entry, $remark);

        $stored = LocalChangeComments::getRemark($entry);
        $this->assertSame(LocalChangeComments::MAX_REMARK_LENGTH, mb_strlen((string) $stored));
        $this->assertSame(str_repeat('ä', LocalChangeComments::MAX_REMARK_LENGTH), $stored);
        $this->assertTrue(mb_check_encoding((string) $stored, 'UTF-8'), 'no character was split in half');
    }

    public function testSetRemarkAtExactlyMaxRemarkLengthIsNotCut(): void
    {
        $entry = new TranslationEntry('demo', 'greeting');
        $remark = str_repeat('x', LocalChangeComments::MAX_REMARK_LENGTH);

        LocalChangeComments::setRemark($entry, $remark);

        $this->assertSame($remark, LocalChangeComments::getRemark($entry));
    }

    /**
     * Switching from an escaped to a plain value (or back) leaves exactly one remark comment behind -
     * never both the legacy and the escaped prefix at once.
     */
    public function testSetRemarkSwitchingBetweenEscapedAndPlainKeepsExactlyOneComment(): void
    {
        $entry = new TranslationEntry('demo', 'greeting');
        LocalChangeComments::setRemark($entry, "mit\nZeilenumbruch");

        LocalChangeComments::setRemark($entry, 'ohne Umbruch');

        $this->assertSame(['remark: ohne Umbruch'], $entry->getTranslatorComments());
        $this->assertSame('ohne Umbruch', LocalChangeComments::getRemark($entry));
    }

    /**
     * "remark" coexists independently with "original"/"local_change" (a plural message's
     * "original[<form>]" too) - setting/reading one must never disturb the others, and all three can
     * be present on the very same entry at once.
     */
    public function testRemarkCoexistsWithOriginalAndLocalChangeOnTheSameEntry(): void
    {
        $entry = new TranslationEntry('demo', 'greeting');
        LocalChangeComments::setOriginal($entry, 'Hallo');
        LocalChangeComments::refresh($entry, 'Hallo', 'Servus', new \DateTimeImmutable('2026-09-21T10:00:00Z'));

        LocalChangeComments::setRemark($entry, 'Bitte prüfen');

        $this->assertSame('Hallo', LocalChangeComments::getOriginal($entry));
        $this->assertSame('2026-09-21T10:00:00Z', LocalChangeComments::getLocalChange($entry));
        $this->assertSame('Bitte prüfen', LocalChangeComments::getRemark($entry));

        LocalChangeComments::setRemark($entry, null);

        $this->assertNull(LocalChangeComments::getRemark($entry));
        $this->assertSame('Hallo', LocalChangeComments::getOriginal($entry), 'removing the remark must not touch "original"');
        $this->assertSame('2026-09-21T10:00:00Z', LocalChangeComments::getLocalChange($entry), 'nor "local_change"');
    }

    /**
     * A plural message's per-form "original[<form>]" comments must not be confused with, or removed
     * by, the (single, per-identifier) "remark".
     */
    public function testRemarkCoexistsWithPerFormOriginalsOfAPluralMessage(): void
    {
        $entry = new TranslationEntry('demo', 'greeting');
        LocalChangeComments::setOriginal($entry, 'Eintrag', 0);
        LocalChangeComments::setOriginal($entry, 'Einträge', 1);

        LocalChangeComments::setRemark($entry, 'Bitte prüfen');

        $this->assertSame('Eintrag', LocalChangeComments::getOriginal($entry, 0));
        $this->assertSame('Einträge', LocalChangeComments::getOriginal($entry, 1));
        $this->assertSame('Bitte prüfen', LocalChangeComments::getRemark($entry));

        LocalChangeComments::removeFormOriginals($entry);

        $this->assertSame('Bitte prüfen', LocalChangeComments::getRemark($entry), 'removing the form originals must not touch the remark');
    }
}
