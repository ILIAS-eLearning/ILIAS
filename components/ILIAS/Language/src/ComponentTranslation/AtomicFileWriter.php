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

use RuntimeException;

/**
 * Writes a file so that concurrent readers only see the complete old or new content: a temporary file in
 * the SAME directory (rename() is only atomic within one filesystem) replaces the target via rename().
 * The data is fsync()ed first unless the caller opts out; an existing target keeps its file mode.
 *
 * Accepted residual risk (TOCTOU, CWE-367): PHP has no openat()/O_NOFOLLOW, so the confinement check and
 * the later operations work on path names; whoever can replace a directory component of $file by a symbolic
 * link in between (needs write access, i.e. the web server user) can redirect the write. After the rename()
 * the directory is resolved again: outside $confine_to_directory the write throws and removes $file only if
 * it is the file just written (same inode and device). Best effort. The same risk covers the overlay lock
 * file created by MigratedLanguageFileSync::acquireLock().
 */
final class AtomicFileWriter
{
    /**
     * @param string|null $confine_to_directory $file must resolve (its directory via realpath()) to this
     *        directory or below, so a symbolic link cannot redirect the write (CWE-59); checked before and after
     * @param bool $flush_to_disk `false` skips the fsync(): readers still see complete content, but after a
     *        crash the file may be lost. Only for reproducible files (e.g. build artifacts), as an fsync()
     *        per file is slow for thousands of them.
     * @throws RuntimeException if $file is a symbolic link, lies outside $confine_to_directory, or
     *         cannot be written
     */
    public static function write(
        string $file,
        string $content,
        ?string $confine_to_directory = null,
        bool $flush_to_disk = true
    ): void {
        if (is_link($file)) {
            throw new RuntimeException(sprintf('Refusing to replace the symbolic link "%s".', $file));
        }
        $directory = dirname($file);
        $resolved_root = null;
        if ($confine_to_directory !== null) {
            $resolved_root = realpath($confine_to_directory);
            if ($resolved_root === false || !self::isAtOrBelow(realpath($directory), $resolved_root)) {
                throw new RuntimeException(sprintf('"%s" does not resolve to a path below "%s".', $file, $confine_to_directory));
            }
        }
        $temporary = @tempnam($directory, '.' . basename($file) . '.');
        // tempnam() returns a resolved path - compare resolved directories, so symlinks, "//" or
        // relative paths in $file do not count as a fallback to the system temp dir
        $resolved_directory = realpath($directory);
        if (
            $temporary === false
            || $resolved_directory === false
            || realpath(dirname($temporary)) !== $resolved_directory
            || ($resolved_root !== null && !self::isAtOrBelow($resolved_directory, $resolved_root))
        ) {
            // tempnam() silently falls back to the system temp dir if $directory is not writable -
            // a rename() from there would no longer be atomic (or even possible)
            if ($temporary !== false) {
                @unlink($temporary);
            }
            throw new RuntimeException(sprintf('Could not create a temporary file next to "%s".', $file));
        }

        try {
            $written_identity = self::writeContent($temporary, $content, $flush_to_disk);
            // tempnam() creates the file with mode 0600: an existing target keeps its mode, a new
            // one gets what a plain file_put_contents() would create
            clearstatcache(true, $file);
            $existing_mode = is_file($file) ? @fileperms($file) : false;
            $mode = $existing_mode !== false ? $existing_mode & 07777 : 0666 & ~umask();
            if (!@chmod($temporary, $mode)) {
                self::acceptUnchangeableMode($temporary, $mode);
            }
            if (!@rename($temporary, $file)) {
                throw new RuntimeException(sprintf('Could not replace "%s".', $file));
            }
        } catch (\Throwable $t) {
            @unlink($temporary);
            throw $t;
        }

        // a symbolic link swapped in between the check above and the rename() (see the class
        // comment) - the written file must not stay where the link pointed to
        if ($resolved_root !== null) {
            clearstatcache(true);
            if (!self::isAtOrBelow(realpath(dirname($file)), $resolved_root)) {
                // only the file written here - a link swapped in after a correct rename() would
                // otherwise let this unlink() remove a foreign file of the same name
                $current = @lstat($file);
                $removed = $current !== false
                    && $current['ino'] === $written_identity['ino']
                    && $current['dev'] === $written_identity['dev']
                    && @unlink($file);
                throw new RuntimeException(sprintf(
                    '"%s" no longer resolves to a path below "%s" after writing it - %s.',
                    $file,
                    $confine_to_directory,
                    $removed ? 'the written file was removed' : 'nothing was removed'
                ));
            }
        }
    }

    /**
     * Whether $effective_mode (permission bits 07777 only) grants every permission of $intended_mode.
     * Decision of acceptUnchangeableMode(), public for tests only.
     *
     * @internal
     */
    public static function grantsAtLeast(int $effective_mode, int $intended_mode): bool
    {
        return (($effective_mode & 07777) & $intended_mode) === $intended_mode;
    }

    private static function isAtOrBelow(string|false $resolved_path, string $resolved_root): bool
    {
        return $resolved_path !== false
            && ($resolved_path === $resolved_root || str_starts_with($resolved_path, rtrim($resolved_root, '/') . '/'));
    }

    /**
     * Some file systems do not support chmod(): that is only an error if the resulting mode lacks a
     * permission $mode grants (e.g. the web server could no longer read a Setup-written file), else it is logged.
     */
    private static function acceptUnchangeableMode(string $file, int $mode): void
    {
        clearstatcache(true, $file);
        $effective = @fileperms($file);
        if ($effective === false || !self::grantsAtLeast($effective, $mode)) {
            throw new RuntimeException(sprintf(
                'Could not set the mode of "%s" to %o, and its mode %s is more restrictive.',
                $file,
                $mode,
                $effective === false ? 'unknown' : sprintf('%o', $effective & 07777)
            ));
        }
        error_log(sprintf(
            'Could not set the mode of "%s" to %o - keeping its mode %o, which grants at least as much.',
            $file,
            $mode,
            $effective & 07777
        ));
    }

    /**
     * Writes $content to $file and with $flush_to_disk flushes it, so the rename() cannot publish
     * content that is not persisted yet.
     *
     * @return array{ino: int, dev: int} identity of the written file (fstat() of the handle used)
     */
    private static function writeContent(string $file, string $content, bool $flush_to_disk): array
    {
        $handle = @fopen($file, 'wb');
        if ($handle === false) {
            throw new RuntimeException(sprintf('Could not write "%s".', $file));
        }
        try {
            $written = 0;
            $length = strlen($content);
            while ($written < $length) {
                $bytes = @fwrite($handle, substr($content, $written));
                if ($bytes === false || $bytes === 0) {
                    throw new RuntimeException(sprintf('Could not write "%s".', $file));
                }
                $written += $bytes;
            }
            if (!fflush($handle) || ($flush_to_disk && !fsync($handle))) {
                throw new RuntimeException(sprintf('Could not flush "%s" to disk.', $file));
            }
            $stat = fstat($handle);
            if ($stat === false) {
                throw new RuntimeException(sprintf('Could not stat "%s".', $file));
            }

            return ['ino' => $stat['ino'], 'dev' => $stat['dev']];
        } finally {
            fclose($handle);
        }
    }
}
