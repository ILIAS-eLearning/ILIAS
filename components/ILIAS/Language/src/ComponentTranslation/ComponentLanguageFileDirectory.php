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

use ILIAS\Component\Component;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ComponentLanguageFileDirectory implements LanguageFileDirectory, NamesShippedLanguageFiles
{
    private string $base_directory;
    private readonly string $shipped_file_name_pattern;

    /**
     * @param string|null $shipped_file_name_pattern see NamesShippedLanguageFiles, e.g. "ilias_%s"; default "<prefix>_%s"
     * @throws \InvalidArgumentException for an invalid pattern, see MigratedLanguageFilePaths::assertValidShippedFileNamePattern()
     */
    public function __construct(
        private readonly Component $component,
        private readonly string $prefix,
        private readonly string $path_inside_component = 'lang/',
        ?string $shipped_file_name_pattern = null
    ) {
        $this->shipped_file_name_pattern = $shipped_file_name_pattern ?? $prefix . '_%s';
        MigratedLanguageFilePaths::assertValidShippedFileNamePattern($this->shipped_file_name_pattern);
    }

    public function getShippedFileNamePattern(): string
    {
        return $this->shipped_file_name_pattern;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getPath(): string
    {
        // cached: the location never changes and this is called repeatedly per directory loop
        if (!isset($this->base_directory)) {
            $reflector = new \ReflectionClass($this->component);
            $ilias_base_dir = (string) realpath(__DIR__ . '/../../../../../');
            $this->base_directory = str_replace($ilias_base_dir . '/', '', dirname($reflector->getFileName()));
        }

        return $this->base_directory . '/' . $this->path_inside_component;
    }

    public function getSuffix(): string
    {
        return '';
    }

    public function isLocal(): bool
    {
        return false;
    }
}
