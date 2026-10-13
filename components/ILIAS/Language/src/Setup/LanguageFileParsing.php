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

namespace ILIAS\Language\Setup;

use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;

/**
 * Side-effect-free *.lang helpers shared by InstalledLanguageDatabaseRepository and
 * LanguageInstallationManager; a trait so that neither depends on the other.
 */
trait LanguageFileParsing
{
    private function absoluteDirectoryPath(string $absolute_path, LanguageFileDirectory $directory): string
    {
        return rtrim($absolute_path, '/') . '/' . ltrim($directory->getPath(), '/');
    }

    /**
     * @param string[] $content
     * @return bool|string[] the entries after the header marker, false if there is none
     */
    private function cutHeader(array $content)
    {
        foreach ($content as $key => $val) {
            if (trim($val) === "<!-- language file start -->") {
                return array_slice($content, $key + 1);
            }
        }
        return false;
    }
}
