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

/**
 * Replaces server-executable file endings with a neutral suffix.
 *
 * Content extracted from an archive may end up in a web-served directory, so entries with
 * executable endings must never be written under their original name. "lang" and "inc" are
 * deliberately not on the list: they are not web-executable and are part of legitimate imports.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ExecutableNeutralizer
{
    private const NEUTRAL_SUFFIX = 'sec';

    private const EXECUTABLE_SUFFIXES = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'pht', 'phtml', 'phar', 'htaccess',
    ];

    public function isExecutable(string $path): bool
    {
        if ($this->isDirectory($path)) {
            return false;
        }
        $suffix = strtolower(pathinfo($this->trimTrailingDots($path), PATHINFO_EXTENSION));

        return in_array($suffix, self::EXECUTABLE_SUFFIXES, true);
    }

    /**
     * Returns the path with an executable ending replaced by the neutral suffix, or the unchanged
     * path if it is not executable. Trailing dots and spaces are removed as well, since some
     * servers ignore them ("shell.php." is served as "shell.php").
     */
    public function neutralize(string $path): string
    {
        if (!$this->isExecutable($path)) {
            return $path;
        }
        $path = $this->trimTrailingDots($path);
        $suffix = pathinfo($path, PATHINFO_EXTENSION);

        return substr($path, 0, strlen($path) - strlen($suffix)) . self::NEUTRAL_SUFFIX;
    }

    private function trimTrailingDots(string $path): string
    {
        return rtrim($path, '. ');
    }

    private function isDirectory(string $path): bool
    {
        return str_ends_with($path, '/') || str_ends_with($path, '\\');
    }
}
