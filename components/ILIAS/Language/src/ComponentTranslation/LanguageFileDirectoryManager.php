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

use function ILIAS\UI\examples\Breadcrumbs\breadcrumbs;

/**
 * @internal
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class LanguageFileDirectoryManager
{

    /**
     * @var LanguageFileDirectory[]
     */
    private array $directories;

    public function __construct(
        LanguageFileDirectory ...$directory
    ) {
        $this->directories = $directory;
        $this->check();
    }

    private function check(): void
    {
        // Basic checks
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
}
