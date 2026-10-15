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
 * Read side of MigratedLanguageFileSync: local changes, shipped modules and entries, module translations, migrated modules, overlay removal.
 */
class MigratedLanguageFileSyncReadersTest extends MigratedLanguageFileSyncTestCase
{
    /**
     * loadLocalChanges() is loadModuleTranslations()'s pure overlay-delta half: only the shipped `.po`'s
     * EXISTENCE is checked (isShipped(), a plain is_file()) - it is never actually parsed. Proven here
     * with a shipped `.po` that exists but cannot be parsed at all: loadLocalChanges() still succeeds
     * (unlike loadModuleTranslations(), which would throw), because it never touches its content.
     */
    public function testLoadLocalChangesNeverParsesTheShippedPoOnlyChecksItExists(): void
    {
        file_put_contents($this->shippedPo(), "msgid \"kaputt\n");
        $this->seedOverlay(['greeting' => ['value' => 'Servus', 'original' => 'Hallo', 'local_change' => '2020-01-01T00:00:00Z']]);

        $result = MigratedLanguageFileSync::loadLocalChanges($this->manager, 'de', self::MODULE, $this->client_data_dir);

        $this->assertSame(
            ['value' => 'Servus', 'local_change' => true, 'local_change_date' => '2020-01-01 00:00:00', 'original' => 'Hallo'],
            $result['greeting']
        );
    }

    public function testLoadLocalChangesReturnsOnlyTheOverlayEntriesNotTheShippedOnes(): void
    {
        $this->seedShipped(['greeting' => 'Hallo', 'farewell' => 'Tschüss']);
        $this->seedOverlay(['greeting' => ['value' => 'Servus', 'original' => 'Hallo']]);

        $this->assertSame(
            ['greeting' => ['value' => 'Servus', 'local_change' => false, 'local_change_date' => null, 'original' => 'Hallo']],
            MigratedLanguageFileSync::loadLocalChanges($this->manager, 'de', self::MODULE, $this->client_data_dir)
        );
    }

    public function testLoadLocalChangesReturnsAnEmptyArrayWithoutAnyOverlay(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);

        $this->assertSame([], MigratedLanguageFileSync::loadLocalChanges($this->manager, 'de', self::MODULE, $this->client_data_dir));
    }

    public function testLoadLocalChangesReturnsNullWithoutAShippedPo(): void
    {
        $this->seedOverlay(['greeting' => 'Servus']);

        $this->assertNull(MigratedLanguageFileSync::loadLocalChanges($this->manager, 'de', self::MODULE, $this->client_data_dir));
    }

    public function testLoadLocalChangesReturnsNullForAnUnknownModuleOrWithoutClientDataDir(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);

        $this->assertNull(MigratedLanguageFileSync::loadLocalChanges($this->manager, 'de', 'other_module', $this->client_data_dir));
        $this->assertNull(MigratedLanguageFileSync::loadLocalChanges($this->manager, 'de', self::MODULE, null));
    }

    /**
     * An overlay `.mo` without its `.po` (which carries the bookkeeping) must never be treated as "no
     * overlay" - that would let the next reconciling write remove a local change it cannot actually see.
     * Every reader that consults the overlay (loadModuleTranslations(), loadLocalChanges()) throws
     * instead.
     */
    public function testAnOverlayMoWithoutItsPoThrowsInsteadOfBeingTreatedAsAbsent(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        MigratedPoFixture::writeOverlayRevision($this->overlayBase(), MigratedPoFixture::catalog(self::MODULE, ['greeting' => 'Servus']));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/has no/');
        MigratedLanguageFileSync::loadModuleTranslations($this->manager, 'de', self::MODULE, $this->client_data_dir);
    }

    /**
     * The same guard for loadLocalChanges(), which LanguageInstallationManager's loadOverlay() relies
     * on to distinguish "no overlay" from "unreadable overlay" (see its own docblock).
     */
    public function testLoadLocalChangesThrowsForAnOverlayMoWithoutItsPo(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        MigratedPoFixture::writeOverlayRevision($this->overlayBase(), MigratedPoFixture::catalog(self::MODULE, ['greeting' => 'Servus']));

        $this->expectException(RuntimeException::class);
        MigratedLanguageFileSync::loadLocalChanges($this->manager, 'de', self::MODULE, $this->client_data_dir);
    }

    /**
     * An irregular file (here: a directory) at the overlay `.po` position must not be silently skipped
     * either - it is reported instead of being read as "no overlay".
     */
    public function testAnIrregularFileAtTheOverlayPoPositionThrows(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        mkdir($this->overlayBase() . '.po', 0775, true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/no regular file/');
        MigratedLanguageFileSync::loadModuleTranslations($this->manager, 'de', self::MODULE, $this->client_data_dir);
    }

    /**
     * sync() itself must refuse to touch such an overlay too - not silently rebuild or remove it (see
     * the class docblock: "an overlay path that is no regular file, is never replaced or removed").
     */
    public function testSyncThrowsForAnOverlayMoWithoutItsPoInsteadOfRemovingOrRebuildingIt(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        MigratedPoFixture::writeOverlayRevision($this->overlayBase(), MigratedPoFixture::catalog(self::MODULE, ['greeting' => 'Servus']));

        $this->expectException(RuntimeException::class);
        $this->sync(['greeting' => 'Hallo']);
    }

    public function testLoadShippedModulesReturnsTheShippedValuesOfEveryMigratedModuleForTheLanguage(): void
    {
        $second_directory = MigratedPoFixture::directory('stwo', $this->directory->getPath());
        $not_migrated = MigratedPoFixture::directory('nomig', $this->directory->getPath());
        $manager = new LanguageFileDirectoryManager(
            new CustomizingLanguageFileDirectory(),
            new MainLanguageFileDirectory(),
            $this->directory,
            $second_directory,
            $not_migrated
        );
        $this->seedShipped([
            'greeting' => 'Hallo',
            'foreign' => ['value' => 'Fremd', 'context' => 'other_module'],
        ]);
        $this->seedShipped(['farewell' => 'Tschüss'], 'de', 'stwo');
        $this->seedShipped(['farewell' => 'Bye'], 'en', 'stwo');
        $this->seedShipped(['x' => 'only english'], 'en', 'nomig');

        $this->assertSame(
            [self::MODULE => ['greeting' => 'Hallo'], 'stwo' => ['farewell' => 'Tschüss']],
            MigratedLanguageFileSync::loadShippedModules($manager, $this->ilias_root, 'de')
        );
    }

    public function testLoadShippedModulesReturnsAnEmptyListForAModuleWhoseShippedPoIsEmpty(): void
    {
        $this->seedShipped([]);

        $this->assertSame(
            [self::MODULE => []],
            MigratedLanguageFileSync::loadShippedModules($this->manager, $this->ilias_root, 'de')
        );
    }

    /**
     * readShippedPo() caches a parsed shipped `.po` per request, keyed by its content hash - so a
     * `.po` that changes WITHIN the same request (e.g. Setup rewriting it, or - as here - a test
     * simulating that) is read fresh, never served stale from the cache.
     */
    public function testAChangedShippedPoWithinTheSameRequestIsReReadNotServedFromTheCache(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->assertSame(
            ['greeting' => 'Hallo'],
            MigratedLanguageFileSync::loadShippedModules($this->manager, $this->ilias_root, 'de')[self::MODULE]
        );

        $this->seedShipped(['greeting' => 'Servus']);

        $this->assertSame(
            ['greeting' => 'Servus'],
            MigratedLanguageFileSync::loadShippedModules($this->manager, $this->ilias_root, 'de')[self::MODULE]
        );
    }

    /**
     * The cache is bounded (SHIPPED_CATALOG_CACHE_SIZE = 512, see the class docblock) - installing all
     * shipped languages in one request must not let it grow without bound. Direct reflection access to
     * the private static cache, the only way to observe its size without instrumenting every one of
     * 513+ parses.
     */
    public function testTheShippedPoCacheNeverGrowsBeyondItsConfiguredLimit(): void
    {
        $cache = new ReflectionProperty(MigratedLanguageFileSync::class, 'shipped_catalogs');
        $cache->setValue(null, []);

        try {
            for ($i = 0; $i < 520; $i++) {
                $module = 'cachetest' . $i;
                $directory = MigratedPoFixture::directory($module, $this->directory->getPath());
                MigratedPoFixture::writePo(
                    $this->fixture_directory . '/' . $module . '_de.po',
                    MigratedPoFixture::catalog($module, ['x' => 'y'])
                );
                MigratedLanguageFileSync::loadShippedModuleEntries($this->fixture_directory . '/' . $module . '_de.po', $module);
                unset($directory);
            }

            $this->assertLessThanOrEqual(512, count($cache->getValue()));
        } finally {
            $cache->setValue(null, []);
        }
    }

    /**
     * Every module with a shipped `.po` for the language - also one whose `.po` cannot be parsed
     * (the callers need to know it is maintained in PO files to report it) - but neither a module
     * without shipped `.po` for that language nor the main directory (empty prefix).
     */
    public function testFindShippedModuleFilesListsEveryModuleWithAShippedPoForTheLanguage(): void
    {
        $corrupt = MigratedPoFixture::directory('scorrupt', $this->directory->getPath());
        $english_only = MigratedPoFixture::directory('senonly', $this->directory->getPath());
        $manager = new LanguageFileDirectoryManager(
            new CustomizingLanguageFileDirectory(),
            new MainLanguageFileDirectory(),
            $this->directory,
            $corrupt,
            $english_only
        );
        $this->seedShipped(['greeting' => 'Hallo']);
        file_put_contents($this->shippedPo('de', 'scorrupt'), "msgid \"kaputt\n");
        $this->seedShipped(['x' => 'only english'], 'en', 'senonly');

        $this->assertSame(
            [self::MODULE => $this->shippedPo(), 'scorrupt' => $this->shippedPo('de', 'scorrupt')],
            MigratedLanguageFileSync::findShippedModuleFiles($manager, $this->ilias_root, 'de')
        );
    }

    public function testHasShippedModulesOnlyForALanguageWithAShippedPo(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);

        $this->assertFalse(MigratedLanguageFileSync::hasShippedModules($this->manager, $this->ilias_root, []));
        $this->assertFalse(MigratedLanguageFileSync::hasShippedModules($this->manager, $this->ilias_root, ['en']));
        $this->assertTrue(MigratedLanguageFileSync::hasShippedModules($this->manager, $this->ilias_root, ['en', 'de']));
    }

    /**
     * The extracted comments ("#.", the counterpart of a .lang "###" comment) of an entry are joined
     * into one line; an entry without one gets `null`, entries of another context are not returned.
     */
    public function testLoadShippedModuleEntriesReturnsValueAndJoinedExtractedComment(): void
    {
        $catalog = MigratedPoFixture::catalog(self::MODULE, [
            'greeting' => "Hallo\nWelt",
            'farewell' => 'Tschüss',
            'foreign' => ['value' => 'Fremd', 'context' => 'other_module'],
        ]);
        $catalog->find(self::MODULE, 'greeting')->addExtractedComment('shown on the login page');
        $catalog->find(self::MODULE, 'greeting')->addExtractedComment('keep it short');
        $catalog->find('other_module', 'foreign')->addExtractedComment('not returned');
        MigratedPoFixture::writePo($this->shippedPo(), $catalog);

        $entries = MigratedLanguageFileSync::loadShippedModuleEntries($this->shippedPo(), self::MODULE);
        ksort($entries);

        $this->assertSame(
            [
                'farewell' => ['value' => 'Tschüss', 'comment' => null],
                'greeting' => ['value' => "Hallo\nWelt", 'comment' => 'shown on the login page keep it short'],
            ],
            $entries
        );
    }

    /** The own note (translator comment) wins over the reference note ("#."); loadShippedNotes() agrees. */
    public function testTheTranslatorCommentIsTheNoteAndBothKindsAreShippedComments(): void
    {
        $catalog = MigratedPoFixture::catalog(self::MODULE, ['greeting' => 'Hej', 'farewell' => 'Hejdå']);
        $catalog->find(self::MODULE, 'greeting')->addExtractedComment('en note');
        $catalog->find(self::MODULE, 'greeting')->addTranslatorNote('egen anteckning');
        $catalog->find(self::MODULE, 'farewell')->addExtractedComment('en note');
        MigratedPoFixture::writePo($this->shippedPo(), $catalog);

        $entries = MigratedLanguageFileSync::loadShippedModuleEntries($this->shippedPo(), self::MODULE);

        $this->assertSame('egen anteckning', $entries['greeting']['comment']);
        $this->assertSame('en note', $entries['farewell']['comment']);
        $this->assertSame(
            ['greeting' => ['egen anteckning'], 'farewell' => ['en note']],
            MigratedLanguageFileSync::loadShippedNotes($this->shippedPo(), self::MODULE)
        );
    }

    public function testLoadShippedModuleEntriesThrowsForACorruptPo(): void
    {
        file_put_contents($this->shippedPo(), "msgid \"kaputt\n");

        $this->expectException(RuntimeException::class);
        MigratedLanguageFileSync::loadShippedModuleEntries($this->shippedPo(), self::MODULE);
    }

    public function testLoadModuleTranslationsReturnsValueLocalChangeDateAndOriginalPerEntry(): void
    {
        $this->seedShipped(['changed' => 'Hallo']);
        $this->seedOverlay([
            'changed' => ['value' => 'Servus', 'original' => 'Hallo', 'local_change' => '2026-09-21T10:11:12Z'],
            'unchanged' => ['value' => 'Tschüss', 'original' => 'Tschüss'],
            'foreign' => ['value' => 'x', 'context' => 'other_module'],
        ]);

        $this->assertSame(
            [
                'changed' => [
                    'value' => 'Servus',
                    'local_change' => true,
                    'local_change_date' => '2026-09-21 10:11:12',
                    'original' => 'Hallo',
                ],
                'unchanged' => [
                    'value' => 'Tschüss',
                    'local_change' => false,
                    'local_change_date' => null,
                    'original' => 'Tschüss',
                ],
            ],
            MigratedLanguageFileSync::loadModuleTranslations($this->manager, 'de', self::MODULE, $this->client_data_dir)
        );
    }

    /** Only the overlay `.po` is read: a missing overlay `.mo` does not make the module "not migrated". */
    public function testLoadModuleTranslationsIgnoresAMissingOverlayMoAndStillReadsTheOverlayPo(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->seedOverlay(['greeting' => ['value' => 'Servus', 'original' => 'Hallo', 'local_change' => '2020-01-01T00:00:00Z']]);
        unlink(MigratedPoFixture::overlayMo($this->overlayBase()));

        $this->assertSame(
            ['value' => 'Servus', 'local_change' => true, 'local_change_date' => '2020-01-01 00:00:00', 'original' => 'Hallo'],
            MigratedLanguageFileSync::loadModuleTranslations($this->manager, 'de', self::MODULE, $this->client_data_dir)['greeting']
        );
    }

    /**
     * Without an overlay `.po` at all, loadModuleTranslations() falls back to the shipped-only result
     * (never `null`, as long as the module is migrated - i.e. has a shipped `.po`) - a lone `.mo`
     * (which sync() itself never produces, but a stray file could) changes nothing either, since only
     * the `.po` is ever consulted.
     */
    /**
     * An overlay `.mo` without its `.po` may hold a local change ilLanguage serves: treating it as "no overlay" would
     * let the next write remove it. It is reported as unreadable (callers fall back to the database), and a write
     * leaves it untouched.
     */
    public function testAnOverlayMoWithoutItsPoIsUnreadableAndNotRemovedByAWrite(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        MigratedPoFixture::writeOverlayRevision($this->overlayBase(), MigratedPoFixture::catalog(self::MODULE, ['greeting' => 'Stray']));

        try {
            MigratedLanguageFileSync::loadModuleTranslations($this->manager, 'de', self::MODULE, $this->client_data_dir);
            $this->fail('Expected a RuntimeException');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('has no', $e->getMessage());
        }
        try {
            $this->sync(['greeting' => 'Hallo']);
            $this->fail('Expected a RuntimeException');
        } catch (RuntimeException) {
        }

        $this->assertSame(['greeting' => 'Stray'], MigratedPoFixture::readMo(MigratedPoFixture::overlayMo($this->overlayBase())));
    }

    public function testGetMigratedModulesListsAModuleByItsShippedPoAloneIndependentOfAnyOverlay(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->seedOverlay(['greeting' => 'Servus']);

        $this->assertSame([self::MODULE], MigratedLanguageFileSync::getMigratedModules($this->manager, 'de', $this->client_data_dir));
        $this->assertSame([], MigratedLanguageFileSync::getMigratedModules($this->manager, 'en', $this->client_data_dir));
        $this->assertSame([], MigratedLanguageFileSync::getMigratedModules($this->manager, 'de', null));
    }

    /**
     * The essence of "migrated == has a shipped .po": a module without ANY overlay file at all (never
     * had a local change) is still listed as migrated for its shipped language.
     */
    public function testGetMigratedModulesListsAModuleWithNoOverlayFileWhatsoever(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);

        $this->assertDirectoryDoesNotExist(dirname($this->overlayBase()));
        $this->assertSame([self::MODULE], MigratedLanguageFileSync::getMigratedModules($this->manager, 'de', $this->client_data_dir));
    }

    public function testRemoveOverlayDeletesBothOverlayFilesButLeavesTheShippedPoUntouched(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->seedOverlay(['greeting' => 'Hallo']);
        $shipped_before = file_get_contents($this->shippedPo());

        MigratedLanguageFileSync::removeOverlay($this->manager, 'de', self::MODULE, $this->client_data_dir);

        $this->assertFileDoesNotExist($this->overlayBase() . '.po');
        $this->assertFileDoesNotExist(MigratedPoFixture::overlayMo($this->overlayBase()));
        $this->assertSame($shipped_before, file_get_contents($this->shippedPo()));
    }

    public function testRemoveOverlayOnlyRemovesTheRequestedLanguage(): void
    {
        $this->seedOverlay(['greeting' => 'Hallo'], 'de');
        $this->seedOverlay(['greeting' => 'Hello'], 'en');

        MigratedLanguageFileSync::removeOverlay($this->manager, 'de', self::MODULE, $this->client_data_dir);

        $this->assertFileExists($this->overlayBase('en') . '.po');
        $this->assertFileExists(MigratedPoFixture::overlayMo($this->overlayBase('en')));
    }

    public function testRemoveOverlayIsANoOpWithoutOverlayUnknownModuleOrClientDataDir(): void
    {
        MigratedLanguageFileSync::removeOverlay($this->manager, 'de', self::MODULE, $this->client_data_dir);
        $this->seedOverlay(['greeting' => 'Hallo']);

        MigratedLanguageFileSync::removeOverlay($this->manager, 'de', 'other_module', $this->client_data_dir);
        MigratedLanguageFileSync::removeOverlay($this->manager, 'de', self::MODULE, null);

        $this->assertFileExists($this->overlayBase() . '.po');
        $this->assertFileExists(MigratedPoFixture::overlayMo($this->overlayBase()));
    }

    public function testRemoveOverlayThrowsWhenAnOverlayFileCannotBeRemoved(): void
    {
        if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
            $this->markTestSkipped('A non-removable file cannot be forced for root (no root-proof variant for unlink()).');
        }
        $this->seedOverlay(['greeting' => 'Hallo']);
        $overlay_directory = dirname($this->overlayBase());
        chmod($overlay_directory, 0555);

        // the lock file cannot be created in the read-only directory either - logged (error_log)
        $this->expectErrorLog();
        set_error_handler(static fn(): bool => true, E_WARNING);
        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessageMatches('/overlay file/i');
            MigratedLanguageFileSync::removeOverlay($this->manager, 'de', self::MODULE, $this->client_data_dir);
        } finally {
            restore_error_handler();
            chmod($overlay_directory, 0775);
        }
    }
}
