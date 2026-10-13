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
 * A directory of language files with a path and a prefix for them. It models how ILIAS currently
 * handles translations: files must be named (e.g. ilias_en.lang) and structured accordingly.
 *
 * - MainLanguageFileDirectory: only for the Language component, the `/lang` directory; empty prefix, as
 *   the files already carry component prefixes (variables of three parts).
 * - ComponentLanguageFileDirectory: for a component; the prefix is the component prefix (e.g. "file"), the
 *   path relative to the component directory (usually `lang/`); entries of two parts (like plugins before).
 */
interface LanguageFileDirectory
{
    public function getPrefix(): string;

    public function getPath(): string;

    public function getSuffix(): string;

    public function isLocal(): bool;
}
