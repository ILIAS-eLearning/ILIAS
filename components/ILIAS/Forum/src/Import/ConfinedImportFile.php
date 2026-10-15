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

namespace ILIAS\Forum\Import;

use ilException;

/**
 * Resolves a path from forum import XML against one import directory.
 *
 * A missing file is absent. A path that leaves the directory, or a directory
 * that is not a sandbox, aborts the import.
 */
final readonly class ConfinedImportFile
{
    public function __construct(private string $import_directory)
    {
    }

    /**
     * @throws ilException when the import directory is missing or the path leaves it
     */
    public function resolve(string $path_from_xml): ?string
    {
        if ($this->import_directory === '') {
            throw new ilException('Resolving forum import paths requires a sandboxed import directory.');
        }

        $relative_path = $this->normalizeRelativePath($path_from_xml);
        if ($relative_path === '') {
            return null;
        }

        $base_path = realpath($this->import_directory);
        if ($base_path === false) {
            throw new ilException(\sprintf('The import directory "%s" does not exist.', $this->import_directory));
        }

        $resolved_path = realpath($base_path . DIRECTORY_SEPARATOR . $relative_path);
        if ($resolved_path === false) {
            return null;
        }

        if (!str_starts_with($resolved_path, $base_path . DIRECTORY_SEPARATOR)) {
            throw new ilException(\sprintf('The import path "%s" escapes the import directory.', $path_from_xml));
        }

        return $resolved_path;
    }

    private function normalizeRelativePath(string $path): string
    {
        $path = str_replace('\\', '/', $path);

        while (preg_match('#\p{C}+|^\./#u', $path) === 1) {
            $replaced = preg_replace('#\p{C}+|^\./#u', '', $path);
            if (!\is_string($replaced)) {
                return '';
            }
            $path = $replaced;
        }

        $parts = [];
        foreach (explode('/', $path) as $part) {
            switch ($part) {
                case '':
                case '.':
                    break;
                case '..':
                    array_pop($parts);
                    break;
                default:
                    $parts[] = $part;
                    break;
            }
        }

        return implode('/', $parts);
    }
}
