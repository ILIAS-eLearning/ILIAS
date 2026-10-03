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

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class LanguageFileDirectoryManager
{
    /**
     * @var LanguageFileDirectory[]
     */
    private array $directories;

    public function __construct(
        private readonly LanguageFileDirectory $local_directory,
        LanguageFileDirectory ...$global_directories
    ) {
        $this->directories = $global_directories;
        $this->check();
    }

    private function check(): void
    {
        if ($this->local_directory instanceof CustomizingLanguageFileDirectory) {
            if (!$this->local_directory->getPath()) {
                throw new \InvalidArgumentException(
                    "CustomizingLanguageFileDirectory must have a non-empty path"
                );
            }
            if ($this->local_directory->getPrefix() !== '') {
                throw new \InvalidArgumentException(
                    "CustomizingLanguageFileDirectory must have an empty prefix"
                );
            }
        }
        $main_files = 0;
        $prefixes = [];
        foreach ($this->directories as $d) {
            switch (true) {
                case $d instanceof MainLanguageFileDirectory:
                    $main_files++;
                    if ($main_files > 1) {
                        throw new \InvalidArgumentException(
                            "There must not be more than one MainLanguageFileDirectory"
                        );
                    }
                    break;
                case $d instanceof ComponentLanguageFileDirectory:
                    if (empty($d->getPrefix())) {
                        throw new \InvalidArgumentException(
                            "ComponentLanguageFileDirectory must have a non-empty prefix"
                        );
                    }
                    if (isset($prefixes[$d->getPrefix()])) {
                        throw new \InvalidArgumentException(
                            "There must not be two ComponentLanguageFileDirectory with the same prefix"
                        );
                    }
                    $prefixes[$d->getPrefix()] = true;
                    break;
                default:
                    if (empty($d->getPrefix())) {
                        throw new \InvalidArgumentException("LanguageFileDirectory must have a non-empty prefix");
                    }
                    break;
            }
        }
    }

    /**
     * @return \Generator|LanguageFileDirectory[]
     */
    public function getDirectories(): \Generator
    {
        yield from $this->directories;
    }

    /**
     * @return \Generator|LanguageFileDirectory[]
     */
    public function getCustomizingDirectories(): \Generator
    {
        yield $this->local_directory;
    }

    /**
     * @return \Generator|LanguageFileDirectory[]
     */
    public function getAllDirectories(): \Generator
    {
        yield from $this->getDirectories();
        yield from $this->getCustomizingDirectories();
    }
}
