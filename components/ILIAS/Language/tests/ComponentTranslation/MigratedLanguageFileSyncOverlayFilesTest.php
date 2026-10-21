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
 * sync() and the overlay files: no-op guard, self-healing .mo, cache invalidation, value "0", multi-line originals.
 */
class MigratedLanguageFileSyncOverlayFilesTest extends MigratedLanguageFileSyncTestCase
{
    /**
     * No-op guard: an unchanged `.po` next to a `.mo` equal to what TranslationCatalog::toMoString() compiles writes
     * nothing: no file is replaced (AtomicFileWriter renames, so a rewrite changes the inode) and the read cache is not
     * invalidated.
     */
    #[DataProvider('refreshFlags')]
    public function testAnUnchangedOverlayWithACorrectMoWritesNothing(bool $refresh): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->sync(['greeting' => 'Servus']);
        $po_before = file_get_contents($this->overlayBase() . '.po');
        $mo_before = file_get_contents(MigratedPoFixture::overlayMo($this->overlayBase()));
        $po_inode = fileinode($this->overlayBase() . '.po');
        $mo_inode = fileinode(MigratedPoFixture::overlayMo($this->overlayBase()));
        $cache = new ReflectionProperty(\ILIAS\Language\ComponentTranslation\TranslationGettextRepository::class, 'states');
        $cache->setValue(null, [self::MODULE . '|de' => ['greeting' => 'cached']]);

        try {
            $this->sync(['greeting' => 'Servus'], $refresh);

            $this->assertSame([self::MODULE . '|de' => ['greeting' => 'cached']], $cache->getValue(), 'no invalidation');
        } finally {
            $cache->setValue(null, []);
        }
        clearstatcache();
        $this->assertSame($po_before, file_get_contents($this->overlayBase() . '.po'));
        $this->assertSame($mo_before, file_get_contents(MigratedPoFixture::overlayMo($this->overlayBase())));
        $this->assertSame($po_inode, fileinode($this->overlayBase() . '.po'), 'the .po was not replaced');
        $this->assertSame($mo_inode, fileinode(MigratedPoFixture::overlayMo($this->overlayBase())), 'the .mo was not replaced');
    }

    public function testAMissingMoIsRecompiledEvenWhenThePoIsUnchanged(): void
    {
        // a local change: a shipped value creates no overlay (delta)
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->sync(['greeting' => 'Servus']);
        $po_before = file_get_contents($this->overlayBase() . '.po');
        $mo_before = file_get_contents(MigratedPoFixture::overlayMo($this->overlayBase()));
        $po_inode = fileinode($this->overlayBase() . '.po');
        unlink(MigratedPoFixture::overlayMo($this->overlayBase()));

        $this->sync(['greeting' => 'Servus']);

        clearstatcache();
        $this->assertSame($po_before, file_get_contents($this->overlayBase() . '.po'));
        $this->assertSame($po_inode, fileinode($this->overlayBase() . '.po'), 'the unchanged .po is not rewritten');
        $this->assertSame(['greeting' => 'Servus'], MigratedPoFixture::readMo(MigratedPoFixture::overlayMo($this->overlayBase())));
        $this->assertSame($mo_before, file_get_contents(MigratedPoFixture::overlayMo($this->overlayBase())));
    }

    /**
     * Self-healing: a `.mo` that does not hold exactly what TranslationCatalog::toMoString() compiles from the (unchanged)
     * `.po` - garbage, truncated, empty, or the compiled content of another catalog - is rebuilt,
     * byte for byte, although the `.po` itself is left untouched. ilLanguage serves the `.mo`, so
     * without this a broken `.mo` would be served until the next actual value change.
     */
    #[DataProvider('staleMoContents')]
    public function testAStaleMoNextToAnUnchangedPoIsRebuilt(\Closure $stale_content, bool $refresh): void
    {
        $this->seedShipped(['greeting' => 'Hallo', 'farewell' => 'Tschüss']);
        $this->sync(['greeting' => 'Servus', 'farewell' => 'Tschüss']);
        $po_before = file_get_contents($this->overlayBase() . '.po');
        $po_inode = fileinode($this->overlayBase() . '.po');
        $mo_before = file_get_contents(MigratedPoFixture::overlayMo($this->overlayBase()));
        file_put_contents(MigratedPoFixture::overlayMo($this->overlayBase()), $stale_content($mo_before));
        $cache = new ReflectionProperty(\ILIAS\Language\ComponentTranslation\TranslationGettextRepository::class, 'states');
        $cache->setValue(null, [self::MODULE . '|de' => ['greeting' => 'stale']]);

        try {
            $this->sync(['greeting' => 'Servus', 'farewell' => 'Tschüss'], $refresh);

            $this->assertSame([], $cache->getValue(), 'the rebuilt .mo invalidates the read cache');
        } finally {
            $cache->setValue(null, []);
        }

        clearstatcache();
        $mo_after = file_get_contents(MigratedPoFixture::overlayMo($this->overlayBase()));
        $this->assertSame($mo_before, $mo_after);
        $this->assertSame(\ILIAS\Language\ComponentTranslation\ShippedTranslations::compileCatalog(MigratedPoFixture::readPo($this->overlayBase() . '.po'), self::MODULE, false)['mo'], $mo_after);
        // "farewell" carries the shipped value and is not in the overlay
        $this->assertSame(['greeting' => 'Servus'], MigratedPoFixture::readMo(MigratedPoFixture::overlayMo($this->overlayBase())));
        $this->assertSame($po_before, file_get_contents($this->overlayBase() . '.po'));
        $this->assertSame($po_inode, fileinode($this->overlayBase() . '.po'), 'the unchanged .po is not rewritten');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function staleMoContents(): array
    {
        return [
            'garbage' => [static fn(string $mo): string => 'SENTINEL', false],
            'truncated' => [static fn(string $mo): string => substr($mo, 0, intdiv(strlen($mo), 2)), false],
            'one byte missing' => [static fn(string $mo): string => substr($mo, 0, -1), false],
            'empty' => [static fn(string $mo): string => '', false],
            'another catalog' => [
                static fn(string $mo): string => MigratedPoFixture::catalog(self::MODULE, ['greeting' => 'Veraltet'])->toMoString(),
                false,
            ],
            'garbage, reconciling write' => [static fn(string $mo): string => 'SENTINEL', true],
        ];
    }

    public function testAChangedValueRewritesBothFiles(): void
    {
        // a first local change creates the overlay to start from
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->sync(['greeting' => 'Moin']);
        file_put_contents(MigratedPoFixture::overlayMo($this->overlayBase()), 'SENTINEL');

        $this->sync(['greeting' => 'Servus']);

        $this->assertSame(['greeting' => 'Servus'], MigratedPoFixture::readMo(MigratedPoFixture::overlayMo($this->overlayBase())));
    }

    /**
     * An entry that enters the overlay with a write that knows when it was changed locally (setup
     * update: lng_data.local_change) keeps that time - not the time of the write. An entry already in
     * the overlay keeps its own bookkeeping.
     */
    public function testANewOverlayEntryTakesItsLocalChangeDateFromTheGivenDates(): void
    {
        $this->seedShipped(['greeting' => 'Hallo', 'farewell' => 'Tschüss']);

        MigratedLanguageFileSync::sync(
            $this->manager,
            $this->ilias_root,
            'de',
            self::MODULE,
            ['greeting' => 'Servus', 'farewell' => 'Pfiat di'],
            $this->client_data_dir,
            true,
            null,
            null,
            ['greeting' => '2026-10-02 09:36:30', 'farewell' => 'not a timestamp']
        );

        $this->assertSame('2026-10-02T09:36:30Z', LocalChangeComments::getLocalChange($this->overlayPo()->find(null, 'greeting')));
        $this->assertNotNull(LocalChangeComments::getLocalChange($this->overlayPo()->find(null, 'farewell')), 'an unreadable date: the time of the write');
        $this->assertNotSame('2026-10-02T09:36:30Z', LocalChangeComments::getLocalChange($this->overlayPo()->find(null, 'farewell')));
    }

    public function testASyncInvalidatesTheIlLanguageReadCache(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $cache = new ReflectionProperty(\ILIAS\Language\ComponentTranslation\TranslationGettextRepository::class, 'states');
        $cache->setValue(null, [self::MODULE . '|de' => ['greeting' => 'stale'], 'other|de' => ['x' => 'y']]);

        try {
            $this->sync(['greeting' => 'Servus']);

            $this->assertSame(['other|de' => ['x' => 'y']], $cache->getValue());
        } finally {
            $cache->setValue(null, []);
        }
    }

    /**
     * gettext/gettext treats a falsy translation as absent; the adapter's workaround must hold on
     * the whole way through sync(): "0" (it occurs in the legacy ilias_fr.lang/ilias_pl.lang) is
     * written as `msgstr "0"`, served from the `.mo` as "0", is no local change when shipped that
     * way - and re-syncing it is a no-op (the `.mo` byte comparison must see identical output).
     */
    public function testTheValueZeroSurvivesTheSyncAndResyncingIsANoOp(): void
    {
        // a shipped "0" is not written, so the local value "0" (shipped is "1") goes through the overlay files
        $this->seedShipped(['zero' => '1', 'greeting' => 'Hallo']);

        $this->sync(['zero' => '0', 'greeting' => 'Hallo']);

        $po = file_get_contents($this->overlayBase() . '.po');
        $this->assertStringContainsString("msgid \"zero\"\nmsgstr \"0\"\n", $po);
        $this->assertSame(['zero' => '0'], MigratedPoFixture::readMo(MigratedPoFixture::overlayMo($this->overlayBase())));
        $zero = $this->overlayPo()->find(null, 'zero');
        $this->assertSame('1', LocalChangeComments::getOriginal($zero));
        $this->assertNotNull(LocalChangeComments::getLocalChange($zero));

        $po_inode = fileinode($this->overlayBase() . '.po');
        $mo_inode = fileinode(MigratedPoFixture::overlayMo($this->overlayBase()));
        $this->sync(['zero' => '0', 'greeting' => 'Hallo'], true);
        $this->sync(['zero' => '0', 'greeting' => 'Hallo']);

        clearstatcache();
        $this->assertSame($po_inode, fileinode($this->overlayBase() . '.po'), 'the .po was not replaced');
        $this->assertSame($mo_inode, fileinode(MigratedPoFixture::overlayMo($this->overlayBase())), 'the .mo was not replaced');
    }

    public function testAValueChangedToZeroIsALocalChangeAndServedAsZero(): void
    {
        $this->seedShipped(['count' => '1']);
        $this->sync(['count' => '1']);

        $this->sync(['count' => '0']);

        $count = $this->overlayPo()->find(null, 'count');
        $this->assertSame('0', $count?->getTranslation());
        $this->assertSame('1', LocalChangeComments::getOriginal($count));
        $this->assertNotNull(LocalChangeComments::getLocalChange($count));
        $this->assertSame(['count' => '0'], MigratedPoFixture::readMo(MigratedPoFixture::overlayMo($this->overlayBase())));
    }

    /**
     * A shipped value with line breaks or backslashes is stored as "original" losslessly: re-syncing it is no local
     * change, neither on creation nor on a reconciling write, and produces byte-identical files.
     */
    public function testAnUnchangedMultiLineShippedValueIsNoLocalChangeAndResyncingIsANoOp(): void
    {
        $shipped = ['lf' => "Zeile 1\nZeile 2", 'crlf' => "Zeile 1\r\nZeile 2", 'backslash' => 'C:\\pfad', 'literal' => 'a\\nb'];
        $this->seedShipped($shipped);

        $this->sync($shipped);
        $this->sync($shipped, true);
        $this->sync($shipped);

        // an unchanged multi-line value is recognised as the shipped one: no overlay is written
        $this->assertFileDoesNotExist($this->overlayBase() . '.po');
        $translations = MigratedLanguageFileSync::loadModuleTranslations($this->manager, 'de', self::MODULE, $this->client_data_dir);
        foreach ($shipped as $identifier => $value) {
            $this->assertSame($value, $translations[$identifier]['value'], $identifier);
            $this->assertFalse($translations[$identifier]['local_change'], $identifier);
        }
    }

    public function testChangingOnlyTheLineBreakOfAMultiLineValueIsALocalChange(): void
    {
        $this->seedShipped(['lf' => "Zeile 1\nZeile 2"]);
        $this->sync(['lf' => "Zeile 1\nZeile 2"]);

        $this->sync(['lf' => "Zeile 1\r\nZeile 2"]);

        $entry = $this->overlayPo()->find(null, 'lf');
        $this->assertSame("Zeile 1\nZeile 2", LocalChangeComments::getOriginal($entry));
        $this->assertNotNull(LocalChangeComments::getLocalChange($entry));
    }

    /**
     * An overlay written by an earlier version has the multi-line original flattened into spaces - a
     * reconciling write (refresh) replaces it by the losslessly escaped shipped value and clears the
     * resulting false local change.
     */
    public function testAReconcilingWriteRepairsALegacyFlattenedOriginal(): void
    {
        $this->seedShipped(['lf' => "Zeile 1\nZeile 2"]);
        $catalog = MigratedPoFixture::catalog(self::MODULE, ['lf' => ['value' => "Zeile 1\nZeile 2", 'local_change' => '2020-01-01T00:00:00Z']]);
        $catalog->find(self::MODULE, 'lf')->addTranslatorComment('original: Zeile 1 Zeile 2');
        MigratedPoFixture::writePair($this->overlayBase(), $catalog);

        $this->sync(['lf' => "Zeile 1\nZeile 2"], true);

        // the value is the shipped one, so the legacy entry with its false local change leaves the overlay instead of
        // being repaired
        $this->assertFileDoesNotExist($this->overlayBase() . '.po');
        $this->assertFalse(MigratedLanguageFileSync::loadModuleTranslations(
            $this->manager,
            'de',
            self::MODULE,
            $this->client_data_dir
        )['lf']['local_change']);
    }

    /**
     * An "original: " comment of the legacy format containing a backslash is read verbatim: an admin
     * edit of another entry keeps the untouched entry unchanged and not locally changed.
     */
    public function testALegacyOriginalWithABackslashIsReadVerbatimByAnAdminEdit(): void
    {
        // the shipped value changed since, so "path" is still part of the delta (a value equal to the shipped one would
        // leave the overlay)
        $this->seedShipped(['path' => 'C:\\neu', 'greeting' => 'Hallo']);
        $catalog = MigratedPoFixture::catalog(self::MODULE, ['path' => 'C:\\pfad', 'greeting' => ['value' => 'Hallo', 'original' => 'Hallo']]);
        $catalog->find(self::MODULE, 'path')->addTranslatorComment('original: C:\\pfad');
        MigratedPoFixture::writePair($this->overlayBase(), $catalog);

        $this->sync(['path' => 'C:\\pfad', 'greeting' => 'Servus']);

        $path = $this->overlayPo()->find(null, 'path');
        $this->assertSame('C:\\pfad', LocalChangeComments::getOriginal($path));
        $this->assertNull(LocalChangeComments::getLocalChange($path));
        $this->assertContains('original: C:\\pfad', $path->getTranslatorComments(), 'an admin edit never moves the original');
    }

    public function testLoadModuleTranslationsReturnsTheDecodedMultiLineOriginal(): void
    {
        $this->seedShipped(['lf' => 'Zeile 1']);
        $this->seedOverlay(['lf' => ['value' => "Zeile 1\nZeile 2", 'original' => "Zeile 1\nZeile 2"]]);

        $this->assertSame(
            "Zeile 1\nZeile 2",
            MigratedLanguageFileSync::loadModuleTranslations($this->manager, 'de', self::MODULE, $this->client_data_dir)['lf']['original']
        );
    }

    public function testRefreshKeepsAnEscapedOriginalOfAnEntryTheShippedPoNoLongerContains(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->seedOverlay([
            'greeting' => ['value' => 'Hallo', 'original' => 'Hallo'],
            'dropped' => ['value' => "Neu\nZeile", 'original' => "Alt\nZeile", 'local_change' => '2020-01-01T00:00:00Z'],
        ]);

        $this->sync(['greeting' => 'Hallo', 'dropped' => "Neu\nZeile"], true);

        $dropped = $this->overlayPo()->find(null, 'dropped');
        $this->assertSame("Alt\nZeile", LocalChangeComments::getOriginal($dropped));
        $this->assertSame('2020-01-01T00:00:00Z', LocalChangeComments::getLocalChange($dropped));
    }
}
