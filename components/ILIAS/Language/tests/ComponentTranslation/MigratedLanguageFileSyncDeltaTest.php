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

use ILIAS\Language\ComponentTranslation\CustomizingLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\LocalChangeComments;
use ILIAS\Language\ComponentTranslation\MainLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * sync() computing the delta overlay: creation, admin edit, reconciling write (refresh), replace semantics.
 */
class MigratedLanguageFileSyncDeltaTest extends MigratedLanguageFileSyncTestCase
{
    /**
     * A module whose values are all shipped ones needs no overlay: neither file, lock nor directory is created, by an
     * admin edit or a reconciling write.
     */
    #[DataProvider('refreshFlags')]
    public function testSyncingOnlyShippedValuesWritesNoOverlay(bool $refresh): void
    {
        $this->seedShipped(['greeting' => 'Hallo', 'farewell' => 'Tschüss']);
        $shipped_before = file_get_contents($this->shippedPo());

        $this->sync(['greeting' => 'Hallo', 'farewell' => 'Tschüss'], $refresh);

        $this->assertFileDoesNotExist($this->overlayBase() . '.po');
        $this->assertFileDoesNotExist(MigratedPoFixture::overlayMo($this->overlayBase()));
        $this->assertFileDoesNotExist(MigratedPoFixture::overlayLock($this->overlayBase()));
        $this->assertDirectoryDoesNotExist(dirname($this->overlayBase()));
        $this->assertSame($shipped_before, file_get_contents($this->shippedPo()), 'the shipped .po is never written');
    }

    public static function refreshFlags(): array
    {
        return ['admin edit (refresh = false)' => [false], 'reconciling write (refresh = true)' => [true]];
    }

    public function testCreatingTheOverlayWithADeviatingValueRecordsShippedOriginalAndALocalChange(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);

        $this->sync(['greeting' => 'Servus']);

        $greeting = $this->overlayPo()->find(null, 'greeting');
        $this->assertSame('Servus', $greeting->getTranslation());
        $this->assertSame('Hallo', LocalChangeComments::getOriginal($greeting));
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/',
            (string) LocalChangeComments::getLocalChange($greeting)
        );
    }

    public function testCreatingTheOverlayGivesAnEntryWithoutShippedCounterpartNoOriginalButALocalChange(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);

        $this->sync(['greeting' => 'Hallo', 'custom' => 'Eigener Wert']);

        $custom = $this->overlayPo()->find(null, 'custom');
        $this->assertSame('Eigener Wert', $custom->getTranslation());
        $this->assertNull(LocalChangeComments::getOriginal($custom));
        $this->assertNotNull(LocalChangeComments::getLocalChange($custom));
    }

    /**
     * A shipped "fuzzy" (an unreviewed value, usually the English fallback) must survive the very
     * first sync as long as the value is still the shipped one - and must not be copied onto a
     * value that differs from it.
     */
    public function testCreatingTheOverlayKeepsShippedFuzzyOnlyForTheUnchangedShippedValue(): void
    {
        $this->seedShipped([
            'unchanged' => ['value' => 'Hello', 'fuzzy' => true],
            'changed' => ['value' => 'Bye', 'fuzzy' => true],
            'reviewed' => ['value' => 'Hallo'],
        ]);

        $this->sync(['unchanged' => 'Hello', 'changed' => 'Tschüss', 'reviewed' => 'Hallo']);

        // the shipped values (and their fuzzy flag) stay in the shipped .po, only the changed value enters the overlay
        // - never fuzzy
        $overlay = $this->overlayPo();
        $this->assertNull($overlay->find(null, 'unchanged'));
        $this->assertFalse($overlay->find(null, 'changed')->hasFlag('fuzzy'));
        $this->assertNull($overlay->find(null, 'reviewed'));
    }

    /**
     * The delta is compared against the RAW shipped `.po` value (TranslationCatalog::fromPoFile()), not the markup-
     * cleaned value the build serves: a value with markup TranslationMarkupPolicy disallows is still the shipped value,
     * so writing it back creates no delta.
     */
    public function testTheDeltaIsComparedAgainstTheRawShippedValueNotTheMarkupCleanedOne(): void
    {
        $this->seedShipped(['greeting' => '<script>alert(1)</script>Hallo']);

        $this->sync(['greeting' => '<script>alert(1)</script>Hallo']);

        $this->assertFileDoesNotExist($this->overlayBase() . '.po');
    }

    /**
     * Extracted comments ("#. ...", context for translators) are part of the shipped file and are
     * carried into the overlay on creation/refresh.
     */
    public function testTheOverlayTakesTheExtractedCommentsOfTheShippedEntry(): void
    {
        $catalog = MigratedPoFixture::catalog(self::MODULE, ['greeting' => 'Hallo']);
        $catalog->find(self::MODULE, 'greeting')->addExtractedComment('shown on the login page');
        MigratedPoFixture::writePo($this->shippedPo(), $catalog);

        $this->sync(['greeting' => 'Servus']);

        $this->assertSame(['shown on the login page'], $this->overlayPo()->find(null, 'greeting')->getExtractedComments());
    }

    public function testTheOverlayTakesTheHeadersOfTheShippedPo(): void
    {
        $catalog = MigratedPoFixture::catalog(self::MODULE, ['greeting' => 'Hallo']);
        $catalog->setHeader('Language', 'de');
        $catalog->setHeader('Plural-Forms', 'nplurals=2; plural=(n != 1);');
        MigratedPoFixture::writePo($this->shippedPo(), $catalog);

        // a local change - with only shipped values there is no overlay (delta)
        $this->sync(['greeting' => 'Servus']);

        $this->assertSame('de', $this->overlayPo()->getHeader('Language'));
        $this->assertSame('nplurals=2; plural=(n != 1);', $this->overlayPo()->getHeader('Plural-Forms'));
    }

    /**
     * refresh = false (an ordinary admin edit): the baseline "original" must never move - even when
     * the shipped file changed in the meantime.
     */
    public function testAnAdminEditNeverMovesTheOriginal(): void
    {
        $this->seedShipped(['greeting' => 'Hallo, neu']);
        $this->seedOverlay(['greeting' => ['value' => 'Hallo', 'original' => 'Hallo']]);

        $this->sync(['greeting' => 'Servus']);

        $greeting = $this->overlayPo()->find(null, 'greeting');
        $this->assertSame('Servus', $greeting->getTranslation());
        $this->assertSame('Hallo', LocalChangeComments::getOriginal($greeting));
        $this->assertNotNull(LocalChangeComments::getLocalChange($greeting));
    }

    public function testAnAdminEditRemovesFuzzyOnlyFromTheValuesItActuallyChanges(): void
    {
        $this->seedShipped([
            'kept' => ['value' => 'Hello', 'fuzzy' => true],
            'edited' => ['value' => 'Bye', 'fuzzy' => true],
        ]);
        $this->seedOverlay([
            'kept' => ['value' => 'Hello', 'original' => 'Hello', 'fuzzy' => true],
            'edited' => ['value' => 'Bye', 'original' => 'Bye', 'fuzzy' => true],
        ]);

        $this->sync(['kept' => 'Hello', 'edited' => 'Tschüss']);

        // the kept shipped value (fuzzy in the shipped .po) leaves the overlay, the edited one is no longer fuzzy
        $overlay = $this->overlayPo();
        $this->assertNull($overlay->find(null, 'kept'));
        $this->assertFalse($overlay->find(null, 'edited')->hasFlag('fuzzy'));
    }

    public function testWritingTheOriginalValueBackClearsTheLocalChange(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->seedOverlay([
            'greeting' => ['value' => 'Servus', 'original' => 'Hallo', 'local_change' => '2020-01-01T00:00:00Z'],
        ]);

        $this->sync(['greeting' => 'Hallo']);

        // the shipped value again is no local change: the entry, and with it the now empty overlay, is removed
        $this->assertFileDoesNotExist($this->overlayBase() . '.po');
        $this->assertFileDoesNotExist(MigratedPoFixture::overlayMo($this->overlayBase()));
        $this->assertFalse(MigratedLanguageFileSync::loadModuleTranslations(
            $this->manager,
            'de',
            self::MODULE,
            $this->client_data_dir
        )['greeting']['local_change']);
    }

    /**
     * A full-replace write re-sends every value; an entry whose value did not change must keep its
     * existing local_change timestamp (seeded with a fixed, old value - no sleep()/clock needed).
     */
    public function testTheLocalChangeTimestampOfAnUntouchedEntrySurvivesAnUnrelatedWrite(): void
    {
        $this->seedShipped(['greeting' => 'Hallo', 'farewell' => 'Tschüss']);
        $this->seedOverlay([
            'greeting' => ['value' => 'Servus', 'original' => 'Hallo', 'local_change' => '2020-01-01T00:00:00Z'],
            'farewell' => ['value' => 'Tschüss', 'original' => 'Tschüss'],
        ]);

        $this->sync(['greeting' => 'Servus', 'farewell' => 'Pfiat di']);

        $overlay = $this->overlayPo();
        $this->assertSame('2020-01-01T00:00:00Z', LocalChangeComments::getLocalChange($overlay->find(null, 'greeting')));
        $farewell_change = LocalChangeComments::getLocalChange($overlay->find(null, 'farewell'));
        $this->assertNotNull($farewell_change);
        $this->assertNotSame('2020-01-01T00:00:00Z', $farewell_change);
    }

    /**
     * An overlay of an earlier version carried the module as msgctxt on every entry (see seedOverlay()); the next write
     * must migrate it to the msgctxt-less shape (TranslationEntry::withContext(null)) while preserving translation,
     * comments, flags, "local_change" and "original".
     */
    #[DataProvider('refreshFlags')]
    public function testALegacyEntryWithTheModuleAsMsgctxtIsMigratedToNoContextPreservingItsBookkeeping(bool $refresh): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $legacy_overlay = MigratedPoFixture::catalog(self::MODULE, [
            'greeting' => ['value' => 'Servus', 'original' => 'Hallo', 'local_change' => '2020-01-01T00:00:00Z'],
        ]);
        $legacy_overlay->find(self::MODULE, 'greeting')->addExtractedComment('shown on the login page');
        MigratedPoFixture::writePair($this->overlayBase(), $legacy_overlay);
        $this->assertNotNull($this->overlayPo()->find(self::MODULE, 'greeting'), 'precondition: seeded with the module as msgctxt');

        // a genuine local change is kept alive by re-sending the exact same value
        $this->sync(['greeting' => 'Servus'], $refresh);

        $this->assertNull($this->overlayPo()->find(self::MODULE, 'greeting'), 'no longer stored under the module context');
        $migrated = $this->overlayPo()->find(null, 'greeting');
        $this->assertNotNull($migrated);
        $this->assertSame('Servus', $migrated->getTranslation());
        $this->assertSame('Hallo', LocalChangeComments::getOriginal($migrated));
        $this->assertSame('2020-01-01T00:00:00Z', LocalChangeComments::getLocalChange($migrated));
        if (!$refresh) {
            // refresh=true re-baselines extracted comments from the shipped entry (which has none
            // here) - only an ordinary admin edit (refresh=false) keeps the overlay's own comment
            $this->assertSame(['shown on the login page'], $migrated->getExtractedComments());
        }
    }

    /**
     * An overlay written by the pre-delta design carried EVERY entry of the module. The next reconciling write (Setup
     * update) must shrink it to the true delta in one sync(): entries equal to the shipped value without local_change
     * are dropped, a genuine local change survives - no dedicated migration step.
     */
    public function testALegacyFullOverlayShrinksToTheDeltaOnTheNextReconcilingWrite(): void
    {
        $this->seedShipped(['unchanged_one' => 'A', 'unchanged_two' => 'B', 'changed' => 'C']);
        // A full overlay as an old version would have written it: every entry present, most of them
        // carrying exactly the shipped value with no local_change at all.
        $this->seedOverlay([
            'unchanged_one' => ['value' => 'A', 'original' => 'A'],
            'unchanged_two' => ['value' => 'B', 'original' => 'B'],
            'changed' => ['value' => 'C, lokal', 'original' => 'C', 'local_change' => '2020-01-01T00:00:00Z'],
        ]);

        $this->sync(['unchanged_one' => 'A', 'unchanged_two' => 'B', 'changed' => 'C, lokal'], true);

        $this->assertSame(['changed'], $this->overlayIdentifiers());
        $this->assertSame(
            '2020-01-01T00:00:00Z',
            LocalChangeComments::getLocalChange($this->overlayPo()->find(null, 'changed'))
        );
    }

    public function testRefreshMovesTheOriginalToTheNewShippedValueAndClearsTheLocalChangeOfAnUnmodifiedEntry(): void
    {
        $this->seedShipped(['greeting' => 'Hallo, überarbeitet']);
        $this->seedOverlay(['greeting' => ['value' => 'Hallo', 'original' => 'Hallo']]);

        $this->sync(['greeting' => 'Hallo, überarbeitet'], true);

        // the new shipped value is served from the shipped .po, the outdated overlay entry is removed
        $this->assertFileDoesNotExist($this->overlayBase() . '.po');
        $this->assertSame(
            ['value' => 'Hallo, überarbeitet', 'local_change' => false, 'local_change_date' => null, 'original' => 'Hallo, überarbeitet'],
            MigratedLanguageFileSync::loadModuleTranslations($this->manager, 'de', self::MODULE, $this->client_data_dir)['greeting']
        );
    }

    public function testRefreshMovesTheOriginalButKeepsALocalCustomizationFlagged(): void
    {
        $this->seedShipped(['greeting' => 'Hallo, überarbeitet']);
        $this->seedOverlay([
            'greeting' => ['value' => 'Servus', 'original' => 'Hallo', 'local_change' => '2020-01-01T00:00:00Z'],
        ]);

        $this->sync(['greeting' => 'Servus'], true);

        $greeting = $this->overlayPo()->find(null, 'greeting');
        $this->assertSame('Servus', $greeting->getTranslation());
        $this->assertSame('Hallo, überarbeitet', LocalChangeComments::getOriginal($greeting));
        $this->assertSame('2020-01-01T00:00:00Z', LocalChangeComments::getLocalChange($greeting));
    }

    /**
     * An entry the shipped `.po` no longer contains (kept by the caller as a real local change, see
     * LanguageInstallationManager::resolveMigratedModule()) keeps its last "original" and its
     * local_change date - so a later update can still tell it from an unchanged entry.
     */
    public function testRefreshKeepsTheOriginalOfAnEntryTheShippedPoNoLongerContains(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->seedOverlay([
            'greeting' => ['value' => 'Hallo', 'original' => 'Hallo'],
            'dropped' => ['value' => 'Neu', 'original' => 'Alt', 'local_change' => '2020-01-01T00:00:00Z'],
        ]);

        $this->sync(['greeting' => 'Hallo', 'dropped' => 'Neu'], true);

        $dropped = $this->overlayPo()->find(null, 'dropped');
        $this->assertSame('Alt', LocalChangeComments::getOriginal($dropped));
        $this->assertSame('2020-01-01T00:00:00Z', LocalChangeComments::getLocalChange($dropped));
    }

    /**
     * On refresh fuzzy is recomputed from the shipped file: set exactly when the value is the shipped
     * value and the shipped entry is fuzzy.
     *
     * @param array<string, mixed> $shipped
     * @param array<string, mixed> $overlay
     */
    #[DataProvider('refreshFuzzyCases')]
    public function testRefreshRecomputesFuzzyFromTheShippedEntry(
        array $shipped,
        array $overlay,
        string $value,
        ?bool $expected_fuzzy
    ): void {
        $this->seedShipped(['greeting' => $shipped]);
        $this->seedOverlay(['greeting' => $overlay]);

        $this->sync(['greeting' => $value], true);

        // `null`: the value is the shipped one, so the entry (and its fuzzy flag) lives in the shipped .po only and the
        // overlay is removed
        if ($expected_fuzzy === null) {
            $this->assertFileDoesNotExist($this->overlayBase() . '.po');
            return;
        }
        $this->assertSame($expected_fuzzy, $this->overlayPo()->find(null, 'greeting')->hasFlag('fuzzy'));
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function refreshFuzzyCases(): array
    {
        return [
            'shipped fuzzy, value is shipped' => [
                ['value' => 'Hello', 'fuzzy' => true], ['value' => 'Hello', 'original' => 'Hello'], 'Hello', null,
            ],
            'shipped fuzzy, value differs' => [
                ['value' => 'Hello', 'fuzzy' => true], ['value' => 'Hallo', 'original' => 'Hello', 'fuzzy' => true], 'Hallo', false,
            ],
            'shipped reviewed now, overlay still fuzzy' => [
                ['value' => 'Hallo'], ['value' => 'Hallo', 'original' => 'Hello', 'fuzzy' => true], 'Hallo', null,
            ],
        ];
    }

    public function testReplaceSemanticsRemoveEveryEntryThatIsNotPassed(): void
    {
        $this->seedShipped(['greeting' => 'Hallo', 'farewell' => 'Tschüss']);
        // local changes, since shipped values are never in the overlay
        $this->seedOverlay([
            'greeting' => ['value' => 'Servus', 'original' => 'Hallo', 'local_change' => '2020-01-01T00:00:00Z'],
            'farewell' => ['value' => 'Pfiat di', 'original' => 'Tschüss', 'local_change' => '2020-01-01T00:00:00Z'],
        ]);

        $this->sync(['greeting' => 'Servus']);

        $this->assertSame(['greeting'], $this->overlayIdentifiers());
        $this->assertSame(['greeting' => 'Servus'], MigratedPoFixture::readMo(MigratedPoFixture::overlayMo($this->overlayBase())));
    }

    /** An empty delta removes the overlay files - the shipped file and the lock file are kept. */
    public function testSyncingNoEntriesRemovesTheOverlayButKeepsTheShippedFile(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->seedOverlay(['greeting' => ['value' => 'Servus', 'original' => 'Hallo', 'local_change' => '2020-01-01T00:00:00Z']]);

        $this->sync([]);

        $this->assertFileDoesNotExist($this->overlayBase() . '.po');
        $this->assertFileDoesNotExist(MigratedPoFixture::overlayMo($this->overlayBase()));
        $this->assertFileExists(MigratedPoFixture::overlayLock($this->overlayBase()), 'the lock file is only removed on uninstall');
        $this->assertNotNull(MigratedPoFixture::readPo($this->shippedPo())->find(self::MODULE, 'greeting'));
    }

    /**
     * Entries of a different context (another module) are never taken over into this module's
     * overlay: the overlay holds exactly $entries.
     */
    public function testEntriesOfAnotherContextInTheOldOverlayAreNotCarriedOver(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->seedOverlay([
            'greeting' => ['value' => 'Hallo', 'original' => 'Hallo'],
            'foreign' => ['value' => 'Fremd', 'context' => 'other_module'],
        ]);

        // a local change keeps an overlay to look into (delta)
        $this->sync(['greeting' => 'Servus']);

        $this->assertNull($this->overlayPo()->find('other_module', 'foreign'));
    }

    /**
     * Sorted byte-wise by identifier, independent of the order of $entries - keeps the file
     * diff-stable. SORT_STRING: upper case before lower case, "10" before "9".
     */
    public function testEntriesAreWrittenSortedByIdentifier(): void
    {
        $this->seedShipped(['b' => 'B']);

        $this->sync(['b' => 'B', 'a' => 'A', 'C' => 'C', '9' => 'neun', '10' => 'zehn']);

        // "b" carries the shipped value and is not written
        $this->assertSame(['10', '9', 'C', 'a'], $this->overlayIdentifiers());
    }
}
