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

trait ilWebDAVCheckValidTitleTrait
{
    protected function isDAVableObjTitle(string $title): bool
    {
        return !$this->hasTitleForbiddenChars($title) && !$this->isHiddenFile($title);
    }

    protected function hasTitleForbiddenChars(string $title): bool
    {
        foreach (str_split('\\<>/:*?"|#') as $forbidden_character) {
            if (str_contains($title, $forbidden_character)) {
                return true;
            }
        }

        return false;
    }

    protected function isHiddenFile(string $title): bool
    {
        $prefix = substr($title, 0, 1);
        return $prefix === '.';
    }

    protected function hasValidFileExtension(string $title): bool
    {
        return $title === ilFileUtils::getValidFilename($title);
    }
}
