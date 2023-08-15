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

namespace ILIAS\Filesystem\Util\Archive;

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;
use ILIAS\Filesystem\Util\LegacyPathHelper;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
trait PathHelper
{
    protected function isPathIgnored(string $path, Options $options): bool
    {
        $regex = '(' . implode('|', $options->getIgnoredPathSnippets()) . ')';
        return preg_match($regex, $path) === 1;
    }

    protected function ensureDirectorySeperator(string $path): string
    {
        return rtrim($path, "/") . "/";
    }

    /**
     * @credit https://mixable.blog/php-realpath-for-non-existing-path/
     */
    private function realpath(string $path): string
    {
        $path = array_reduce(explode('/', $path), function ($a, $b) {
            if ($a === null) {
                $a = "/";
            }
            if ($b === "" || $b === ".") {
                return $a;
            }
            if ($b === "..") {
                return dirname($a);
            }

            return preg_replace("/\/+/", "/", "$a/$b");
        });
        return trim($path, "/");
    }

    protected function normalizePath($path, $separator = '\\/'): string
    {
        // if the ZIP name ist just a ../name.zip we stop here since it's hard to generate to real path for that.
        // consumers must adopt their code
        if (str_starts_with($path, '..')) {
            throw new \InvalidArgumentException('The ZIP name must not start with ../. Please provide a real path for the output file.');
        }

        // we prepend a ./ to paths without a leading slash to make sure that the path is relative
        if (!str_starts_with($path, './')) {
            $path = './' . $path;
        }

        if (str_starts_with($path, './') && ($realpath = realpath($path)) !== false) {
            $path = $realpath;
        }

        $normalized = preg_replace('#\p{C}+|^\./#u', '', $path);
        $normalized = preg_replace('#/\.(?=/)|^\./|\./$#', '', $normalized);
        $regex = '#\/*[^/\.]+/\.\.#Uu';

        while (preg_match($regex, $normalized)) {
            $normalized = preg_replace($regex, '', $normalized);
        }

        if (preg_match('#/\.{2}|\.{2}/#', $normalized)) {
            throw new \LogicException(
                'Path is outside of the defined root, path: [' . $path . '], resolved: [' . $normalized . ']'
            );
        }

        // We prepend a ./ to paths without a leading slash to make sure that the path is relative
        if (!str_starts_with($normalized, './') && !str_starts_with($normalized, '/')) {
            $normalized = './' . $normalized;
        }

        // Check if this is a path we can use
        LegacyPathHelper::deriveLocationFrom($normalized); // Throws exception if path is invalid

        return $normalized;
    }
}
