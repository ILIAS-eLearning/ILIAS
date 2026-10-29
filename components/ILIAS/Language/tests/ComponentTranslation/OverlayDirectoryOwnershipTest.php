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
use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFilePaths;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;
use PHPUnit\Framework\TestCase;

/**
 * MigratedLanguageFileSync::findUnwritableOverlayDirectories() with $for_user_id (private isWritableDirectoryFor(),
 * reachable only through it): writability comes from the directory's owner/group/mode bits for that user, never from
 * is_writable(), which means nothing for a Setup run as root on behalf of the web server user.
 */
class OverlayDirectoryOwnershipTest extends TestCase
{
    private const string MODULE = 'stest';

    private string $fixture_directory;
    private string $client_data_dir;
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
        if (!function_exists('posix_getpwuid') || !function_exists('posix_getgrgid')) {
            $this->markTestSkipped('Requires the POSIX extension (posix_getpwuid()/posix_getgrgid()).');
        }

        $this->fixture_directory = __DIR__ . '/tmp-ownership-fixtures-' . bin2hex(random_bytes(4));
        mkdir($this->fixture_directory, 0775, true);
        $this->client_data_dir = sys_get_temp_dir() . '/ilias_mlfs_ownership_test_' . bin2hex(random_bytes(4));
        mkdir($this->client_data_dir, 0775, true);

        $this->directory = MigratedPoFixture::directory(
            self::MODULE,
            'components/ILIAS/Language/tests/ComponentTranslation/' . basename($this->fixture_directory) . '/'
        );
        $this->manager = new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), $this->directory);
        MigratedPoFixture::writePo(
            $this->fixture_directory . '/' . self::MODULE . '_de.po',
            MigratedPoFixture::catalog(self::MODULE, ['greeting' => 'Hallo'])
        );
    }

    protected function tearDown(): void
    {
        if (is_dir($this->overlayDirectory())) {
            chmod($this->overlayDirectory(), 0775);
        }
        MigratedPoFixture::removeDirectory($this->fixture_directory);
        MigratedPoFixture::removeDirectory($this->client_data_dir);

        parent::tearDown();
    }

    private function overlayDirectory(): string
    {
        return dirname(MigratedPoFixture::overlayBase($this->client_data_dir, $this->directory, 'de'));
    }

    /**
     * @return list<string>
     */
    private function findUnwritable(?int $for_user_id): array
    {
        return MigratedLanguageFileSync::findUnwritableOverlayDirectories(
            $this->manager,
            ILIAS_ABSOLUTE_PATH,
            ['de'],
            $this->client_data_dir,
            $for_user_id
        );
    }

    /**
     * Only the leaf overlay directory gets $mode; parents stay 0775, since a restrictive intermediate mode would break
     * the test process's own traversal.
     */
    private function seedOverlayDirectory(int $mode): void
    {
        $overlay_directory = $this->overlayDirectory();
        if (!is_dir(dirname($overlay_directory))) {
            mkdir(dirname($overlay_directory), 0775, true);
        }
        if (!is_dir($overlay_directory)) {
            mkdir($overlay_directory, 0775);
        }
        chmod($overlay_directory, $mode); // avoid mkdir()'s own mode being masked by umask
        clearstatcache(true, $overlay_directory);
    }

    private function assertReported(int $mode, int $for_user_id, string $message): void
    {
        $this->seedOverlayDirectory($mode);

        $this->assertSame([$this->overlayDirectory()], $this->findUnwritable($for_user_id), $message);
    }

    private function assertNotReported(int $mode, int $for_user_id, string $message): void
    {
        $this->seedOverlayDirectory($mode);

        $this->assertSame([], $this->findUnwritable($for_user_id), $message);
    }

    /**
     * Mutation: the `$for_user_id === 0` short-circuit being removed - root must be considered able
     * to write regardless of mode (0000: nobody else could).
     */
    public function testRootMayAlwaysWriteRegardlessOfMode(): void
    {
        $this->assertNotReported(0000, 0, 'root (uid 0) must always be able to create/replace files');
    }

    /**
     * Mutation: the owner branch using the wrong mask (e.g. read instead of write+search, or a
     * group/other mask) - the directory's own owner needs the OWNER write+execute bits, group and
     * other bits are irrelevant to them.
     */
    public function testTheOwnerNeedsTheOwnerWriteAndSearchBitsRegardlessOfGroupOrOtherBits(): void
    {
        if (posix_getuid() === 0) {
            $this->markTestSkipped(
                'The current process is root, so its own uid would only hit the for_user_id===0 short-circuit '
                . '(see testRootMayAlwaysWriteRegardlessOfMode()) instead of the owner branch under test; '
                . 'testAnArbitraryChownedOwnerNeedsTheOwnerWriteAndSearchBits() covers the owner branch as root.'
            );
        }
        // the directory is created by this process, so its owner is the current user, as fileowner() would report
        $owner = posix_getuid();

        $this->assertReported(
            0577, // owner: r-x (no write), group+other: rwx - irrelevant, the owner still can't write
            $owner,
            'the owner lacking the write bit must be reported even though group/other have it'
        );
        $this->assertNotReported(
            0700, // owner: rwx, group+other: nothing
            $owner,
            'the owner having the write+search bits must be enough on its own'
        );
    }

    /**
     * As above, with a directory chown()ed to a non-current uid - proves $stat['uid'] is evaluated against
     * $for_user_id, not the process uid. Only possible as root.
     */
    public function testAnArbitraryChownedOwnerNeedsTheOwnerWriteAndSearchBits(): void
    {
        if (!function_exists('posix_geteuid') || posix_geteuid() !== 0) {
            $this->markTestSkipped('Requires root to chown() the overlay directory to an arbitrary owner.');
        }
        $foreign_owner = 33; // www-data, present on both the reference host and the ILIAS container
        $this->seedOverlayDirectory(0555);
        chown($this->overlayDirectory(), $foreign_owner);
        clearstatcache(true, $this->overlayDirectory());

        $this->assertSame(
            [$this->overlayDirectory()],
            $this->findUnwritable($foreign_owner),
            'the chown()ed owner lacking the write bit must be reported'
        );

        chmod($this->overlayDirectory(), 0775);
        clearstatcache(true, $this->overlayDirectory());

        $this->assertSame(
            [],
            $this->findUnwritable($foreign_owner),
            'the chown()ed owner having the write+search bits must not be reported'
        );
    }

    /**
     * Mutation: the "other" branch being skipped, or using the owner/group mask instead - a uid that
     * is neither the owner nor in its group is governed by the OTHER bits alone, however generous
     * the owner/group bits are.
     */
    public function testAForeignUidNeedsTheOtherWriteAndSearchBitsEvenWhenOwnerAndGroupHaveThem(): void
    {
        $foreign_uid = 1; // "daemon", present on both the reference host and the ILIAS container

        $this->assertReported(
            0775, // owner rwx, group rwx, other r-x (no write) - a stranger still can't write
            $foreign_uid,
            'other lacking the write bit must be reported even though owner/group have it'
        );
        $this->assertNotReported(
            0777, // other now has rwx as well
            $foreign_uid,
            'other having the write+search bits must be enough for a stranger'
        );
    }

    /**
     * Group branch: a uid that is not the owner but shares the directory's group (chgrp()'d to $for_user_id's primary
     * gid) is governed by the GROUP write+search bits. Only possible as root.
     */
    public function testAGroupMemberNeedsTheGroupWriteAndSearchBitsRegardlessOfOwnerOrOtherBits(): void
    {
        if (!function_exists('posix_geteuid') || posix_geteuid() !== 0) {
            $this->markTestSkipped('Requires root to chown()/chgrp() the overlay directory to an arbitrary owner/group.');
        }
        // www-data (33) does not exist everywhere (FreeBSD: www, 80)
        $group_member_uid = 0;
        $group_gid = 0;
        foreach (array_merge([33], range(1, 1000)) as $uid) {
            $user = posix_getpwuid($uid);
            if ($user !== false && $user['gid'] !== 0) {
                [$group_member_uid, $group_gid] = [$uid, $user['gid']];
                break;
            }
        }
        if ($group_member_uid === 0) {
            $this->markTestSkipped('No unprivileged user with an own primary group found.');
        }
        $this->seedOverlayDirectory(0750); // owner: rwx, group: r-x (no write) - not enough
        chown($this->overlayDirectory(), 0);
        chgrp($this->overlayDirectory(), $group_gid);
        clearstatcache(true, $this->overlayDirectory());

        $this->assertSame(
            [$this->overlayDirectory()],
            $this->findUnwritable($group_member_uid),
            'the group lacking the write bit must be reported even though the (different) owner has it'
        );

        chmod($this->overlayDirectory(), 0770); // group: rwx - now enough
        clearstatcache(true, $this->overlayDirectory());

        $this->assertSame(
            [],
            $this->findUnwritable($group_member_uid),
            'the group having the write+search bits must be enough for a group member who is not the owner'
        );
    }
}
