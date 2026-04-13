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
class ComponentLanguageFileDirectory implements LanguageFileDirectory
{

    private string $base_directory;

    public function __construct(
        private Component $component,
        private string $prefix,
        private string $path_inside_component = 'lang/'
    ) {
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getPath(): string
    {
        $reflector = new \ReflectionClass($this->component);
        $ilias_base_dir = realpath(__DIR__ . '/../../../../../');
        $this->base_directory = str_replace($ilias_base_dir . '/', '', dirname($reflector->getFileName()));

        return $this->base_directory . '/' . $this->path_inside_component;
    }
}
