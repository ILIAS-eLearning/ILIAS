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
 * sync() guards: no-ops, missing shipped .po, corrupt files, write failures, concurrent installation.
 */
class MigratedLanguageFileSyncGuardsTest extends MigratedLanguageFileSyncTestCase
{
    public function testIsANoOpWhenTheModuleHasNoContributedDirectory(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);

        MigratedLanguageFileSync::sync($this->manager, $this->ilias_root, 'de', 'other_module', ['x' => 'y'], $this->client_data_dir);
        MigratedLanguageFileSync::sync($this->manager, $this->ilias_root, 'de', '', ['x' => 'y'], $this->client_data_dir, true);

        $this->assertDirectoryDoesNotExist($this->client_data_dir . '/lang');
    }

    public function testIsANoOpWhenNoShippedPoExistsForTheLanguage(): void
    {
        $this->seedShipped(['greeting' => 'Hello'], 'en');

        $this->sync(['greeting' => 'Hallo'], true);

        $this->assertDirectoryDoesNotExist($this->client_data_dir . '/lang');
    }

    /**
     * Un-migration: the shipped `.po` of a migrated module was removed after its overlay was compiled - sync() leaves
     * the overlay files untouched.
     */
    public function testAnExistingOverlayIsLeftUntouchedWhenTheShippedPoNoLongerExists(): void
    {
        $this->seedShipped(['greeting' => 'Hello']);
        $this->seedOverlay(['greeting' => 'Hallo']);
        $po_before = file_get_contents($this->overlayBase() . '.po');
        $mo_before = file_get_contents(MigratedPoFixture::overlayMo($this->overlayBase()));
        unlink($this->shippedPo());

        $this->sync(['greeting' => 'Hallo']);

        $this->assertSame($po_before, file_get_contents($this->overlayBase() . '.po'));
        $this->assertSame($mo_before, file_get_contents(MigratedPoFixture::overlayMo($this->overlayBase())));
    }

    /**
     * Readers must not serve a frozen overlay while its shipped `.po` is missing:
     * getMigratedModules()/loadModuleTranslations() (and ilLanguage) fall back to lng_modules/lng_data as if no overlay
     * existed.
     */
    public function testReadersIgnoreAnExistingOverlayWhileTheShippedPoIsMissing(): void
    {
        $this->seedShipped(['greeting' => 'Hello']);
        $this->seedOverlay(['greeting' => 'Hallo']);
        unlink($this->shippedPo());

        $this->assertNull(MigratedLanguageFileSync::loadModuleTranslations(
            $this->manager,
            'de',
            self::MODULE,
            $this->client_data_dir,
            $this->ilias_root
        ));
        $this->assertSame(
            [],
            MigratedLanguageFileSync::getMigratedModules($this->manager, 'de', $this->client_data_dir, $this->ilias_root)
        );
    }

    /**
     * sync() writes exactly the entries it is given; the shipped-vs-local decision once the `.po` returns is
     * LanguageInstallationManager::resolveMigratedModule()'s, based on the "original" the overlay preserved during the
     * outage.
     */
    public function testARefreshAfterTheShippedPoComesBackStillMovesTheOriginalForwardCorrectly(): void
    {
        $this->seedShipped(['greeting' => 'Hello']);
        // the "original" of this local change must survive the whole gap below untouched
        $this->seedOverlay(['greeting' => ['value' => 'Hallo', 'original' => 'Hello', 'local_change' => '2020-01-01T00:00:00Z']]);
        unlink($this->shippedPo());

        // asserted here so a regression touching the overlay's "original"/local_change bookkeeping while the `.po` is
        // missing fails here, not only once it is back ($refresh_original_from_shipped would then mask the loss)
        $this->sync(['greeting' => 'Hallo']);
        // untouched, so still as seeded: with the module as msgctxt
        $preserved_during_the_gap = $this->overlayPo()->find(self::MODULE, 'greeting');
        $this->assertSame('Hello', LocalChangeComments::getOriginal($preserved_during_the_gap), 'original survives the gap');
        $this->assertSame('2020-01-01T00:00:00Z', LocalChangeComments::getLocalChange($preserved_during_the_gap));

        // the module is migrated again - the shipped `.po` is back, with a changed value
        $this->seedShipped(['greeting' => 'Servus']);

        // an unmodified local value ("Hallo" is still tracked against the preserved original
        // "Hello") is refreshed to the new shipped value, exactly like an ordinary update
        $this->sync(['greeting' => 'Servus'], true);

        // the value is now the shipped one: served from the shipped .po, the overlay is removed
        $this->assertFileDoesNotExist($this->overlayBase() . '.po');
        $this->assertSame(
            ['value' => 'Servus', 'local_change' => false, 'local_change_date' => null, 'original' => 'Servus'],
            MigratedLanguageFileSync::loadModuleTranslations($this->manager, 'de', self::MODULE, $this->client_data_dir)['greeting']
        );
    }

    public function testIsANoOpWhenTheClientDataDirIsNull(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $shipped_before = file_get_contents($this->shippedPo());

        $this->sync(['greeting' => 'Servus'], true, null);

        $this->assertSame($shipped_before, file_get_contents($this->shippedPo()));
        $this->assertSame(['stest_de.po'], array_values(array_diff(scandir($this->fixture_directory), ['.', '..'])));
    }

    /**
     * A corrupt overlay `.po` is rebuilt like a missing one (original/local_change recomputed against the shipped file)
     * instead of blocking every further write.
     */
    #[DataProvider('refreshFlags')]
    public function testACorruptOverlayPoIsRebuiltFromEntriesAndTheShippedPo(bool $refresh): void
    {
        $this->seedShipped(['greeting' => 'Hallo', 'farewell' => 'Tschüss']);
        $this->seedOverlay(['greeting' => ['value' => 'Hallo', 'original' => 'Hallo']]);
        file_put_contents($this->overlayBase() . '.po', "msgid \"broken\nmsgstr \"\"\n");

        $this->sync(['greeting' => 'Hallo', 'farewell' => 'Servus'], $refresh);

        // "greeting" carries the shipped value and is not written
        $overlay = $this->overlayPo();
        $this->assertSame(['farewell'], $this->overlayIdentifiers());
        $farewell = $overlay->find(null, 'farewell');
        $this->assertSame('Tschüss', LocalChangeComments::getOriginal($farewell));
        $this->assertNotNull(LocalChangeComments::getLocalChange($farewell));
        $this->assertSame(['farewell' => 'Servus'], MigratedPoFixture::readMo(MigratedPoFixture::overlayMo($this->overlayBase())));
    }

    /**
     * StrictPoLoader rejects more than the old parser (e.g. a duplicate or a message without msgstr): such an overlay
     * is rebuilt, too.
     */
    #[DataProvider('overlaysOnlyAStrictParserRejects')]
    public function testAnOverlayPoTheStrictParserRejectsIsRebuilt(string $content): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $this->seedOverlay(['greeting' => ['value' => 'Hallo', 'original' => 'Hallo']]);
        file_put_contents($this->overlayBase() . '.po', $content);

        $this->sync(['greeting' => 'Servus']);

        $greeting = $this->overlayPo()->find(null, 'greeting');
        $this->assertSame('Servus', $greeting?->getTranslation());
        $this->assertSame('Hallo', LocalChangeComments::getOriginal($greeting));
        $this->assertNotNull(LocalChangeComments::getLocalChange($greeting));
        $this->assertSame(['greeting' => 'Servus'], MigratedPoFixture::readMo(MigratedPoFixture::overlayMo($this->overlayBase())));
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function overlaysOnlyAStrictParserRejects(): array
    {
        $message = "msgctxt \"" . self::MODULE . "\"\nmsgid \"greeting\"\nmsgstr \"Hallo\"\n";

        return [
            'duplicate message' => [$message . "\n" . $message],
            'message without msgstr' => ["msgctxt \"" . self::MODULE . "\"\nmsgid \"greeting\"\n"],
            'truncated inside a string' => [substr($message, 0, -4)],
            'gap in the plural forms' => ["msgid \"a\"\nmsgid_plural \"as\"\nmsgstr[0] \"x\"\nmsgstr[2] \"z\"\n"],
            'binary data' => ["\x00\x01\x02\xde\x12\x04\x95"],
            'header value with a line break' => ["msgid \"\"\nmsgstr \"\"\n\"X-A: a\\rb\\n\"\n\n" . $message],
        ];
    }

    public function testACorruptShippedPoThrows(): void
    {
        file_put_contents($this->shippedPo(), "msgid \"broken\n");

        $this->expectException(RuntimeException::class);
        $this->sync(['greeting' => 'Hallo']);
    }

    /**
     * A shipped `.po` whose header keeps a line break is broken (checkLanguage() refuses the language): sync() throws a
     * RuntimeException before writing, not the InvalidArgumentException setHeader() would raise.
     */
    public function testAShippedPoWithALineBreakInAHeaderValueThrowsARuntimeExceptionAndWritesNothing(): void
    {
        file_put_contents(
            $this->shippedPo(),
            "msgid \"\"\nmsgstr \"\"\n\"X-A: a\\rb\\n\"\n\nmsgctxt \"" . self::MODULE . "\"\nmsgid \"greeting\"\nmsgstr \"Hallo\"\n"
        );

        try {
            $this->sync(['greeting' => 'Hallo']);
            $this->fail('Expected a RuntimeException');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('contains a line break', $e->getMessage());
        }

        $this->assertFileDoesNotExist($this->overlayBase() . '.po');
        $this->assertFileDoesNotExist(MigratedPoFixture::overlayMo($this->overlayBase()));
    }

    /**
     * Root-proof write failure: a regular file where the overlay's "lang" directory must be created - mkdir() fails for
     * every user.
     */
    public function testThrowsWhenTheOverlayDirectoryCannotBeCreated(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        file_put_contents($this->client_data_dir . '/lang', 'not a directory');

        set_error_handler(static fn(): bool => true, E_WARNING);
        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessageMatches('/Could not create directory/');
            $this->sync(['greeting' => 'Servus']);
        } finally {
            restore_error_handler();
        }
    }

    /**
     * acquireLock() logs a warning when the lock file cannot be opened (e.g. missing permission), and the callback
     * still runs unlocked. Only the lock file's permission is removed, so the overlay can still be written.
     */
    public function testAcquireLockLogsAWarningWhenTheLockFileCannotBeOpenedButTheWriteStillSucceeds(): void
    {
        if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
            $this->markTestSkipped('An unopenable file cannot be forced for root.');
        }
        $this->seedShipped(['greeting' => 'Hallo']);
        // a first sync creates the lock file (and the overlay) so its own permission can be revoked
        $this->sync(['greeting' => 'Servus']);
        $lock_file = MigratedPoFixture::overlayLock($this->overlayBase());
        $this->assertFileExists($lock_file);
        chmod($lock_file, 0000);

        $this->expectErrorLog();
        $this->sync(['greeting' => 'Nochmal geändert']);

        $this->assertStringContainsString('Could not open the lock file', $this->errorLog());
        $this->assertSame(['greeting' => 'Nochmal geändert'], MigratedPoFixture::readMo(MigratedPoFixture::overlayMo($this->overlayBase())));
    }

    /**
     * @return string what error_log() wrote so far - PHPUnit redirects it to a per-test capture file
     *         (see expectErrorLog()).
     */
    private function errorLog(): string
    {
        $file = (string) ini_get('error_log');
        return $file !== '' && is_file($file) ? (string) file_get_contents($file) : '';
    }

    /**
     * Root-proof write failure of the file itself: a directory at the overlay `.po` path, so the final rename() fails.
     * The temporary file must not be left behind.
     */
    public function testThrowsWhenTheOverlayPoCannotBeReplacedAndLeavesNoTemporaryFile(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        mkdir($this->overlayBase() . '.po', 0775, true);

        set_error_handler(static fn(): bool => true, E_WARNING);
        try {
            $this->sync(['greeting' => 'Servus']);
            $this->fail('Expected a RuntimeException');
        } catch (RuntimeException $e) {
            // refused before any write is attempted: the .po path is no regular file
            $this->assertStringContainsString('no regular file', $e->getMessage());
        } finally {
            restore_error_handler();
        }

        $this->assertSame(
            // the lock file (see MigratedLanguageFileSync::withOverlayLock()) is never removed
            ['lock', 'stest_de.po'],
            array_values(array_diff(scandir(dirname($this->overlayBase())), ['.', '..'])),
            'neither a temporary file nor a .mo may be left behind'
        );
    }

    /**
     * A symlinked client data directory (or parent), e.g. a mounted data volume: tempnam() resolves symlinks, which
     * made AtomicFileWriter's "same directory" check reject every write (regression).
     */
    public function testWritesTheOverlayWhenTheClientDataDirIsASymlink(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $link = $this->client_data_dir . '-link';
        symlink($this->client_data_dir, $link);

        try {
            MigratedLanguageFileSync::sync($this->manager, $this->ilias_root, 'de', self::MODULE, ['greeting' => 'Servus'], $link);
        } finally {
            unlink($link);
        }

        $this->assertSame(['greeting' => 'Servus'], MigratedPoFixture::readMo(MigratedPoFixture::overlayMo($this->overlayBase())));
    }

    /**
     * Race: an installation decides on $entries before a concurrent admin edit adds a local change and completes its
     * own sync() - all before the installation's sync() reaches its lock.
     */
    public function testAnInstallationDoesNotDiscardAnOverlayCreatedConcurrentlyAfterItsDecision(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);

        // The concurrent admin edit completes first and creates a genuine local change.
        $this->sync(['greeting' => 'Servus']);
        $this->assertFileExists($this->overlayBase() . '.po', 'precondition: the admin edit created an overlay');

        // The installation's snapshot is from BEFORE that edit: no overlay, only the shipped value.
        try {
            MigratedLanguageFileSync::sync($this->manager, $this->ilias_root, 'de', self::MODULE, ['greeting' => 'Hallo'], $this->client_data_dir, true, false);
            $this->fail('Expected a RuntimeException');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('was created after', $e->getMessage());
        }

        $this->assertSame(['greeting' => 'Servus'], MigratedPoFixture::readMo(MigratedPoFixture::overlayMo($this->overlayBase())));
        $this->assertTrue(
            MigratedLanguageFileSync::loadModuleTranslations($this->manager, 'de', self::MODULE, $this->client_data_dir)['greeting']['local_change']
        );
    }
}
