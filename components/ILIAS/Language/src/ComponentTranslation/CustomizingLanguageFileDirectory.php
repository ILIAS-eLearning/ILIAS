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
 * @internal Only the Language component shall provide an instance of this.
 */
class CustomizingLanguageFileDirectory implements LanguageFileDirectory
{
    public function getPrefix(): string
    {
        return '';
    }

    public function getPath(): string
    {
        return 'lang/customizing/';
    }

    public function getSuffix(): string
    {
        return '.local';
    }

    public function isLocal(): bool
    {
        return true;
    }
}
