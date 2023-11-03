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

namespace ILIAS\Filesystem\Util;

use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\FilesystemsAware;
use ILIAS\FileUpload\Location;

/**
 * The legacy path helper provides convenient functions for the integration of the filesystem service within legacy components.
 * This class should be deprecated with ILIAS 5.5 or earlier.
 *
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 * @author                 Fabian Schmid <fabian@sr.solutions>
 */
final class LegacyPathHelper
{
    use FilesystemsAware;

    public static function deriveLocationFrom(string $absolute_path): int
    {
        [
            $web,
            $webRelativeWithLeadingDot,
            $webRelativeWithoutLeadingDot,
            $storage,
            $customizing,
            $customizingRelativeWithLeadingDot,
            $libs,
            $libsRelativeWithLeadingDot,
            $temp,
            $nodeModules,
            $nodeModulesWithLeadingDot
        ] = self::listPaths();

        return match (true) {
            self::checkPossiblePath($temp, $absolute_path) => Location::TEMPORARY,
            self::checkPossiblePath($web, $absolute_path), self::checkPossiblePath(
                $webRelativeWithLeadingDot,
                $absolute_path
            ), self::checkPossiblePath($webRelativeWithoutLeadingDot, $absolute_path) => Location::WEB,
            self::checkPossiblePath($storage, $absolute_path) => Location::STORAGE,
            self::checkPossiblePath($customizing, $absolute_path), self::checkPossiblePath(
                $customizingRelativeWithLeadingDot,
                $absolute_path
            ) => Location::CUSTOMIZING,
            default => throw new \InvalidArgumentException(
                "Invalid path supplied. Path must start with the web, storage, temp, customizing or libs storage location. Path given: '{$absolute_path}'"
            ),
        };
    }

    /**
     * Tries to fetch the filesystem responsible for the absolute path.
     * Please note that the function is case sensitive.
     *
     * Relative paths are also detected for the ILIAS web storage like './data/default'
     *
     *
     * @param string $absolute_path The absolute used for the filesystem search.
     *
     * @return Filesystem                   The responsible filesystem for the given path.
     *
     * @throws \InvalidArgumentException    Thrown if no filesystem is responsible for the given path.
     */
    public static function deriveFilesystemFrom(string $absolute_path): Filesystem
    {
        [
            $web,
            $webRelativeWithLeadingDot,
            $webRelativeWithoutLeadingDot,
            $storage,
            $customizing,
            $customizingRelativeWithLeadingDot,
            $libs,
            $libsRelativeWithLeadingDot,
            $temp,
            $nodeModules,
            $nodeModulesWithLeadingDot
        ] = self::listPaths();

        return match (true) {
            self::checkPossiblePath($temp, $absolute_path) => self::filesystems()->temp(),
            self::checkPossiblePath($web, $absolute_path) => self::filesystems()->web(),
            self::checkPossiblePath($webRelativeWithLeadingDot, $absolute_path) => self::filesystems()->web(),
            self::checkPossiblePath($webRelativeWithoutLeadingDot, $absolute_path) => self::filesystems()->web(),
            self::checkPossiblePath($storage, $absolute_path) => self::filesystems()->storage(),
            self::checkPossiblePath($customizing, $absolute_path) => self::filesystems()->customizing(),
            self::checkPossiblePath($customizingRelativeWithLeadingDot, $absolute_path) => self::filesystems(
            )->customizing(),
            self::checkPossiblePath($libs, $absolute_path) => self::filesystems()->libs(),
            self::checkPossiblePath($libsRelativeWithLeadingDot, $absolute_path) => self::filesystems()->libs(),
            self::checkPossiblePath($nodeModules, $absolute_path) => self::filesystems()->nodeModules(),
            self::checkPossiblePath($nodeModulesWithLeadingDot, $absolute_path) => self::filesystems()->nodeModules(),
            default => throw new \InvalidArgumentException(
                "Invalid path supplied. Path must start with the web, storage, temp, customizing or libs storage location. Path given: '{$absolute_path}'"
            ),
        };
    }

    /**
     * Creates a relative path from an absolute path which starts with a valid storage location.
     * The primary use case for this method is to trim the path after the filesystem was fetch via the deriveFilesystemFrom method.
     *
     * @param string $absolute_path The path which should be trimmed.
     *
     * @return string                       The trimmed relative path.
     *
     * @throws \InvalidArgumentException    Thrown if the path does not start with a valid storage location.
     *
     * @see LegacyPathHelper::deriveFilesystemFrom()
     */
    public static function createRelativePath(string $absolute_path): string
    {
        [
            $web,
            $webRelativeWithLeadingDot,
            $webRelativeWithoutLeadingDot,
            $storage,
            $customizing,
            $customizingRelativeWithLeadingDot,
            $libs,
            $libsRelativeWithLeadingDot,
            $temp,
            $nodeModules,
            $nodeModulesWithLeadingDot
        ] = self::listPaths();

        return match (true) {
            self::checkPossiblePath($webRelativeWithoutLeadingDot, $absolute_path) => self::resolveRelativePath(
                $webRelativeWithoutLeadingDot,
                $absolute_path
            ),
            self::checkPossiblePath($webRelativeWithLeadingDot, $absolute_path) => self::resolveRelativePath(
                $webRelativeWithLeadingDot,
                $absolute_path
            ),
            self::checkPossiblePath($web, $absolute_path) => self::resolveRelativePath($web, $absolute_path),
            self::checkPossiblePath($temp, $absolute_path) => self::resolveRelativePath($temp, $absolute_path),
            self::checkPossiblePath($storage, $absolute_path) => self::resolveRelativePath($storage, $absolute_path),
            self::checkPossiblePath($customizing, $absolute_path) => self::resolveRelativePath(
                $customizing,
                $absolute_path
            ),
            self::checkPossiblePath($customizingRelativeWithLeadingDot, $absolute_path) => self::resolveRelativePath(
                $customizingRelativeWithLeadingDot,
                $absolute_path
            ),
            self::checkPossiblePath($libs, $absolute_path), self::checkPossiblePath(
                $libsRelativeWithLeadingDot,
                $absolute_path
            ) => self::resolveRelativePath($libsRelativeWithLeadingDot, $absolute_path),
            self::checkPossiblePath($nodeModules, $absolute_path), self::checkPossiblePath(
                $nodeModulesWithLeadingDot,
                $absolute_path
            ) => self::resolveRelativePath($nodeModulesWithLeadingDot, $absolute_path),
            default => throw new \InvalidArgumentException(
                "Invalid path supplied. Path must start with the web, storage, temp, customizing or libs storage location. Path given: '{$absolute_path}'"
            ),
        };
    }

    private static function resolveRelativePath(string $possible_path, string $absolute_path): string
    {
        $real_possible_path = realpath($possible_path);

        return match (true) {
            $possible_path === $absolute_path, $real_possible_path === $absolute_path => "",
            str_starts_with($absolute_path, $possible_path) => substr(
                $absolute_path,
                strlen($possible_path) + 1
            ),
            str_starts_with($absolute_path, $real_possible_path) => substr(
                $absolute_path,
                strlen($real_possible_path) + 1
            ),
            default => throw new \InvalidArgumentException(
                "Invalid path supplied. Path must start with the web, storage, temp, customizing or libs storage location. Path given: '{$absolute_path}'"
            ),
        };
    }

    private static function checkPossiblePath(string $possible_path, string $absolute_path): bool
    {
        $real_possible_path = realpath($possible_path);

        return match (true) {
            $possible_path === $absolute_path => true,
            $real_possible_path === $absolute_path => true,
            is_string($possible_path) && str_starts_with($absolute_path, $possible_path) => true,
            is_string($real_possible_path) && str_starts_with($absolute_path, $real_possible_path) => true,
            default => false,
        };
    }

    /**
     * @return mixed[]
     */
    private static function listPaths(): array
    {
        $web = CLIENT_WEB_DIR;
        $webRelativeWithLeadingDot = './' . ILIAS_WEB_DIR . '/' . CLIENT_ID;
        $webRelativeWithoutLeadingDot = ILIAS_WEB_DIR . '/' . CLIENT_ID;
        $storage = CLIENT_DATA_DIR;
        $customizing = ILIAS_ABSOLUTE_PATH . '/Customizing';
        $customizingRelativeWithLeadingDot = './Customizing';
        $libs = ILIAS_ABSOLUTE_PATH . '/libs';
        $libsRelativeWithLeadingDot = "./libs";
        $temp = CLIENT_DATA_DIR . "/temp";
        $nodeModules = ILIAS_ABSOLUTE_PATH . '/node_modules';
        $nodeModulesWithLeadingDot = './node_modules';

        return [
            $web,
            $webRelativeWithLeadingDot,
            $webRelativeWithoutLeadingDot,
            $storage,
            $customizing,
            $customizingRelativeWithLeadingDot,
            $libs,
            $libsRelativeWithLeadingDot,
            $temp,
            $nodeModules,
            $nodeModulesWithLeadingDot
        ];
    }
}
