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
use ILIAS\Language\ComponentTranslation\AtomicFileWriter;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFilePaths;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;
use PHPUnit\Framework\TestCase;

/**
 * MigratedLanguageFileSync follows no symlink below the overlay root (CWE-59, see
 * assertNoSymbolicLinkBelowOverlayRoot()): a link anywhere in the client data directory (intermediate directory or the
 * `.po`/`.mo`/`.lock` itself) must not redirect a write, lock or removal. The overlay root itself (`<client data
 * dir>/lang` and above) is exempt, being the administrator's configuration.
 *
 * Each scenario also proves the decoy the link points to stays untouched - a RuntimeException alone would miss a check-
 * then-follow mutation.
 */
class MigratedLanguageFileSyncSymlinkTest extends TestCase
{
    use RestoresProcessState;

    private const string MODULE = 'stest';

    private string $fixture_directory;
    private string $client_data_dir;
    private string $decoy_directory;
    private LanguageFileDirectory $directory;
    private LanguageFileDirectoryManager $manager;

    protected function setUp(): void
    {
        // never the build of the installation, see MigratedPoFixture::resetRuntime()
        MigratedPoFixture::resetRuntime();
        parent::setUp();

        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', realpath(__DIR__ . '/../../../../../'));
        }

        $this->fixture_directory = __DIR__ . '/tmp-symlink-fixtures-' . bin2hex(random_bytes(4));
        mkdir($this->fixture_directory, 0775, true);
        $this->client_data_dir = sys_get_temp_dir() . '/ilias_mlfs_symlink_test_' . bin2hex(random_bytes(4));
        mkdir($this->client_data_dir, 0775, true);
        $this->decoy_directory = sys_get_temp_dir() . '/ilias_mlfs_symlink_decoy_' . bin2hex(random_bytes(4));
        mkdir($this->decoy_directory, 0775, true);

        // an extra "sub" segment below the fixture directory, so an intermediate directory can be replaced by a symlink
        // without touching the fixture/client data directories
        $this->directory = MigratedPoFixture::directory(
            self::MODULE,
            'components/ILIAS/Language/tests/ComponentTranslation/' . basename($this->fixture_directory) . '/sub/'
        );
        $this->manager = new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), $this->directory);
    }

    protected function tearDown(): void
    {
        MigratedPoFixture::removeDirectory($this->fixture_directory);
        MigratedPoFixture::removeDirectory($this->client_data_dir);
        MigratedPoFixture::removeDirectory($this->decoy_directory);

        parent::tearDown();
    }

    private function seedShipped(string $value = 'Hallo'): void
    {
        MigratedPoFixture::writePo(
            $this->shippedPo(),
            MigratedPoFixture::catalog(self::MODULE, ['greeting' => $value])
        );
    }

    private function shippedPo(): string
    {
        return $this->fixture_directory . '/sub/' . self::MODULE . '_de.po';
    }

    private function overlayBase(): string
    {
        return MigratedPoFixture::overlayBase($this->client_data_dir, $this->directory, 'de');
    }

    private function lockFile(): string
    {
        return MigratedPoFixture::overlayLock($this->overlayBase());
    }

    private function sync(array $entries = ['greeting' => 'Hallo']): void
    {
        MigratedLanguageFileSync::sync(
            $this->manager,
            ILIAS_ABSOLUTE_PATH,
            'de',
            self::MODULE,
            $entries,
            $this->client_data_dir
        );
    }

    /**
     * @return list<string>
     */
    private function decoyListing(): array
    {
        return array_values(array_diff(scandir($this->decoy_directory) ?: [], ['.', '..']));
    }

    /**
     * Mutation: assertNoSymbolicLinkBelowOverlayRoot() not being called (or not checking every
     * intermediate component) for the `.po` path before it is written.
     */
    public function testASymlinkedIntermediateDirectoryIsRejectedAndItsTargetIsLeftUntouched(): void
    {
        $this->seedShipped();
        mkdir(dirname(dirname($this->overlayBase())), 0775, true);
        symlink($this->decoy_directory, dirname($this->overlayBase()));

        try {
            // a local change: a shipped value alone writes nothing (delta overlay)
            $this->sync(['greeting' => 'Servus']);
            $this->fail('Expected a RuntimeException');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('symbolic link', $e->getMessage());
        }

        $this->assertSame([], $this->decoyListing(), 'nothing may be written into the linked-to directory');
        $this->assertSame($this->decoy_directory, readlink(dirname($this->overlayBase())), 'the symlink itself must be left in place');
    }

    /**
     * Mutation: the `assertNoSymbolicLinkBelowOverlayRoot($client_data_dir, $overlay_po)` call being
     * removed - a `.po` path that is itself a symlink must not be replaced via that link.
     */
    public function testASymlinkedOverlayPoIsRejectedAndItsTargetIsLeftUntouched(): void
    {
        $this->seedShipped();
        mkdir(dirname($this->overlayBase()), 0775, true);
        $decoy = $this->decoy_directory . '/decoy.po';
        file_put_contents($decoy, 'DECOY');
        symlink($decoy, $this->overlayBase() . '.po');

        try {
            $this->sync();
            $this->fail('Expected a RuntimeException');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('symbolic link', $e->getMessage());
        }

        $this->assertSame('DECOY', file_get_contents($decoy), 'the file the link points to must not be modified');
        $this->assertSame($decoy, readlink($this->overlayBase() . '.po'), 'the symlink itself must not be replaced');
    }

    /**
     * Mutation: the `assertNoSymbolicLinkBelowOverlayRoot($client_data_dir, $overlay_mo)` call being
     * removed - exercised with a missing `.po` (a fresh overlay) so this is specifically the `.mo`
     * check, not the `.po` one, that rejects the write.
     */
    public function testASymlinkedOverlayCurrentFileIsRejectedAndItsTargetIsLeftUntouched(): void
    {
        $this->seedShipped();
        mkdir(dirname($this->overlayBase()), 0775, true);
        $decoy = $this->decoy_directory . '/decoy.mo';
        file_put_contents($decoy, 'DECOY');
        symlink($decoy, MigratedPoFixture::overlayCurrent($this->overlayBase()));

        try {
            $this->sync();
            $this->fail('Expected a RuntimeException');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('symbolic link', $e->getMessage());
        }

        $this->assertSame('DECOY', file_get_contents($decoy));
        $this->assertSame($decoy, readlink(MigratedPoFixture::overlayCurrent($this->overlayBase())));
        $this->assertFileDoesNotExist($this->overlayBase() . '.po', 'the .po must not have been written either');
    }

    /**
     * A symlinked lock file is no RuntimeException from sync(): acquireLock() catches it and runs $callback WITHOUT a
     * lock, like a lock file that cannot be opened. The symlink is never followed and the overlay write still succeeds.
     *
     * Mutation: acquireLock() calling fopen()/flock() on the lock path without checking it for a symlink first.
     */
    public function testASymlinkedLockFileIsNeverFollowedAndTheOverlayIsStillWrittenUnlocked(): void
    {
        $this->seedShipped();
        mkdir(dirname($this->lockFile()), 0775, true);
        $decoy = $this->decoy_directory . '/decoy.lock';
        file_put_contents($decoy, 'DECOY');
        symlink($decoy, $this->lockFile());

        $this->sync(['greeting' => 'Servus']);

        $this->assertSame('DECOY', file_get_contents($decoy), 'the lock file link target must never be opened/locked');
        $this->assertSame($decoy, readlink($this->lockFile()), 'the symlink itself must not be replaced');
        $this->assertSame(
            ['greeting' => 'Servus'],
            MigratedPoFixture::readMo(MigratedPoFixture::overlayMo($this->overlayBase())),
            'the overlay write itself must still succeed even though no lock could be taken'
        );
    }

    /**
     * Mutation: removeOverlayFiles() unlinking `$base . '.po'` without first asserting it is not a
     * symlink.
     */
    public function testRemoveOverlayRejectsASymlinkedPoAndLeavesItsTargetAndTheMoUntouched(): void
    {
        $overlay_dir = dirname($this->overlayBase());
        mkdir($overlay_dir, 0775, true);
        MigratedPoFixture::writeOverlayRevision($this->overlayBase(), MigratedPoFixture::catalog(self::MODULE, ['greeting' => 'Servus']));
        file_put_contents(MigratedPoFixture::overlayMo($this->overlayBase()), 'REAL MO');
        $decoy = $this->decoy_directory . '/decoy.po';
        file_put_contents($decoy, 'DECOY');
        symlink($decoy, $this->overlayBase() . '.po');

        try {
            MigratedLanguageFileSync::removeOverlay($this->manager, 'de', self::MODULE, $this->client_data_dir);
            $this->fail('Expected a RuntimeException');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('symbolic link', $e->getMessage());
        }

        $this->assertSame('DECOY', file_get_contents($decoy));
        $this->assertSame($decoy, readlink($this->overlayBase() . '.po'), 'the symlink must not have been unlinked');
        $this->assertSame('REAL MO', file_get_contents(MigratedPoFixture::overlayMo($this->overlayBase())), 'the .mo must not have been removed either');
    }

    /**
     * The overlay root itself ("<client data dir>/lang") may be a symlink (e.g. a mounted data volume); only what is
     * BELOW it is protected.
     */
    public function testASymlinkOnTheOverlayRootItselfIsAllowed(): void
    {
        $this->seedShipped();
        $real_lang_dir = $this->client_data_dir . '-real-lang';
        mkdir($real_lang_dir, 0775, true);
        symlink($real_lang_dir, $this->client_data_dir . '/lang');

        $this->sync(['greeting' => 'Servus']);

        $this->assertSame(
            ['greeting' => 'Servus'],
            MigratedPoFixture::readMo(MigratedPoFixture::overlayMo($this->overlayBase())),
            'the write must go through the symlinked root into the real directory'
        );

        MigratedPoFixture::removeDirectory($real_lang_dir);
    }

    /**
     * Mutation: AtomicFileWriter::write() not enforcing $confine_to_directory - a target outside of
     * it (not necessarily via a symlink; a plain path escape is enough) must be rejected before
     * anything is written.
     */
    public function testAtomicFileWriterRejectsATargetOutsideTheConfinedDirectory(): void
    {
        $outside = $this->decoy_directory . '/outside.po';

        try {
            AtomicFileWriter::write($outside, 'should not be written', $this->client_data_dir);
            $this->fail('Expected a RuntimeException');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('does not resolve to a path below', $e->getMessage());
        }

        $this->assertFileDoesNotExist($outside);
    }
}
