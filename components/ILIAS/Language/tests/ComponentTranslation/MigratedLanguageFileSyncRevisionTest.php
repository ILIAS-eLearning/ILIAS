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

use MigratedPoFixture;
use PHPUnit\Framework\TestCase;

/**
 * The revisions of an overlay (`<client data dir>/lang/<module>/<lang>/r-<hash>/`, named by `current`)
 * as MigratedLanguageFileSync::sync() manages them: a new revision is served right away by the
 * running process (native gettext never reloads a catalog, so every state has a directory of its
 * own), the previous one is kept for requests still reading it, nothing older stays, and an overlay
 * that became empty serves nothing any more.
 *
 * Everything lives below temporary directories (the shipped `.po` below the "ILIAS root" $root).
 */
class MigratedLanguageFileSyncRevisionTest extends TestCase
{
    private const string MODULE = 'rvs';

    private string $root;
    private string $client_data_dir;
    private LanguageFileDirectoryManager $manager;

    protected function setUp(): void
    {
        MigratedPoFixture::resetRuntime();
        $this->root = sys_get_temp_dir() . '/ilias_overlay_revisions_' . bin2hex(random_bytes(6));
        $this->client_data_dir = $this->root . '/client';
        mkdir($this->client_data_dir, 0775, true);

        MigratedPoFixture::writePo(
            $this->root . '/rvs/rvs_de.po',
            MigratedPoFixture::catalog(self::MODULE, ['greeting' => 'Hallo', 'other' => 'Anders'])
        );
        $this->manager = new LanguageFileDirectoryManager(
            new CustomizingLanguageFileDirectory(),
            MigratedPoFixture::directory(self::MODULE, 'rvs/')
        );
        MigratedPoFixture::build($this->manager, $this->root);
    }

    protected function tearDown(): void
    {
        MigratedPoFixture::removeDirectory($this->root);
        MigratedPoFixture::resetRuntime();
    }

    /**
     * @param array<string, string> $entries
     */
    private function sync(array $entries): void
    {
        MigratedLanguageFileSync::sync($this->manager, $this->root, 'de', self::MODULE, $entries, $this->client_data_dir);
    }

    private function overlayDirectory(): string
    {
        return MigratedLanguageFilePaths::overlayDirectory($this->client_data_dir, self::MODULE, 'de');
    }

    private function served(string $key = 'greeting'): ?string
    {
        return MigratedTranslations::text(self::MODULE, 'de', $key, $this->client_data_dir);
    }

    /**
     * @return list<string> the revision directories of the overlay, sorted
     */
    private function revisions(): array
    {
        $revisions = array_values(array_filter(
            scandir($this->overlayDirectory()) ?: [],
            static fn(string $name): bool => MigratedLanguageFilePaths::isOverlayRevision($name)
        ));
        sort($revisions);

        return $revisions;
    }

    private function current(): ?string
    {
        $file = MigratedLanguageFilePaths::overlayCurrentFile($this->overlayDirectory());

        return is_file($file) ? trim((string) file_get_contents($file)) : null;
    }

    public function testEveryNewRevisionIsServedAtOnceAndOnlyTheCurrentAndThePreviousOneStay(): void
    {
        $previous = null;
        foreach (['Servus', 'Moin', 'Grüß Gott', 'Servus', 'Tach'] as $value) {
            $this->sync(['greeting' => $value]);

            $current = $this->current();
            $this->assertNotNull($current);
            $this->assertNotSame($previous, $current);
            $this->assertSame($value, $this->served(), 'visible in the process that wrote it');
            $this->assertSame('Anders', $this->served('other'), 'the shipped values stay served');
            $expected = array_values(array_filter([$current, $previous]));
            sort($expected);
            $this->assertSame($expected, $this->revisions(), 'the current revision and the one served before it - nothing older');
            $previous = $current;
        }
    }

    public function testSyncingTheSameContentAgainWritesNothingNew(): void
    {
        $this->sync(['greeting' => 'Servus']);
        $current = $this->current();
        $current_file = MigratedLanguageFilePaths::overlayCurrentFile($this->overlayDirectory());
        $inode = fileinode($current_file);
        clearstatcache();

        $this->sync(['greeting' => 'Servus']);

        $this->assertSame($current, $this->current());
        $this->assertSame([$current], $this->revisions());
        $this->assertSame($inode, fileinode($current_file), '`current` was not written again');
    }

    public function testGoingBackToTheContentOfTheKeptPreviousRevisionReusesIt(): void
    {
        $this->sync(['greeting' => 'Servus']);
        $first = (string) $this->current();
        $this->sync(['greeting' => 'Moin']);
        $second = (string) $this->current();

        $this->sync(['greeting' => 'Servus']);

        $this->assertSame($first, $this->current());
        $this->assertSame('Servus', $this->served());
        $expected = [$first, $second];
        sort($expected);
        $this->assertSame($expected, $this->revisions());
    }

    public function testADamagedRevisionIsNeverReusedAndNeverChangedInPlace(): void
    {
        $this->sync(['greeting' => 'Servus']);
        $damaged = (string) $this->current();
        $this->sync(['greeting' => 'Moin']);
        $kept = (string) $this->current();
        // a running process may have loaded that revision: it is not repaired, a new one is written
        $mo = MigratedLanguageFilePaths::overlayMoFile(
            MigratedLanguageFilePaths::overlayRevisionDirectory($this->overlayDirectory(), $damaged),
            self::MODULE,
            'de'
        );
        file_put_contents($mo, 'garbage');

        $this->sync(['greeting' => 'Servus']);

        $current = (string) $this->current();
        $this->assertNotSame($damaged, $current);
        $this->assertSame('Servus', $this->served());
        $this->assertSame(
            ['greeting' => 'Servus'],
            MigratedPoFixture::readMo(MigratedLanguageFilePaths::overlayMoFile(
                MigratedLanguageFilePaths::overlayRevisionDirectory($this->overlayDirectory(), $current),
                self::MODULE,
                'de'
            ))
        );
        $expected = [$current, $kept];
        sort($expected);
        $this->assertSame($expected, $this->revisions(), 'the damaged one is gone, the previous one is kept');
    }

    public function testAnOverlayThatBecameEmptyServesTheShippedValuesAgainAndTheNextSyncRemovesItsRevisions(): void
    {
        $this->sync(['greeting' => 'Servus']);
        $this->sync(['greeting' => 'Moin']);
        $overlay_po = $this->overlayDirectory() . '/rvs_de.po';
        $this->assertFileExists($overlay_po);

        $this->sync(['greeting' => 'Hallo', 'other' => 'Anders']);

        $this->assertFileDoesNotExist($overlay_po);
        $this->assertNull($this->current(), 'nothing is named any more, so nothing of the old revisions is served');
        $this->assertSame('Hallo', $this->served());
        $this->assertFileExists(MigratedLanguageFilePaths::overlayLockFile($this->overlayDirectory()), 'the lock file is kept');

        $this->sync(['greeting' => 'Hallo', 'other' => 'Anders']);

        $this->assertSame([], $this->revisions());
    }

    public function testRevisionsWithoutAnOverlayAreRemovedByTheNextSyncOfShippedValues(): void
    {
        $this->sync(['greeting' => 'Servus']);
        unlink($this->overlayDirectory() . '/rvs_de.po');
        unlink(MigratedLanguageFilePaths::overlayCurrentFile($this->overlayDirectory()));
        $this->assertCount(1, $this->revisions(), 'precondition: what a crash or an old version leaves behind');

        $this->sync(['greeting' => 'Hallo', 'other' => 'Anders']);

        $this->assertSame([], $this->revisions());
        $this->assertSame('Hallo', $this->served());
    }
}
