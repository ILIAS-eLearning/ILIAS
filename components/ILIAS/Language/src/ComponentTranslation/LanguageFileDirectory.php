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
 * A LanguageFileDirectory represents a directory where language files are stored.
 * It provides the path to the directory and a prefix for the language files in that directory.
 * Please note, that this interface represents the current "way" how ILIAS handles translations. You must have a lot
 * of knowledge about how files must be named (e.g. ilias_en.lang) and how it's content has to be structured.
 *
 * There are currently two implementations of which you can use only one:
 * - \ILIAS\Language\ComponentTranslation\MainLanguageFileDirectory: Only the Language-Component shall provide this,
 * it's directory is the current `/lang` directory where all translations are stored. It has an empty prefix since the
 * language files already have the component-prefixes
 * - \ILIAS\Language\ComponentTranslation\ComponentLanguageFileDirectory: This is a directory for a specific component.
 * The prefix is the component-prefix (e.g. "file") and the path to return is a relative to the Component-Directory,
 * `lang/` in most cases. The files there must follow the same rules as the global language files exept for the prefix.
 * Global langauge variables consists of three parts, Component files only of two (like Plugins did before).
 */
interface LanguageFileDirectory
{
    public function getPrefix(): string;

    public function getPath(): string;
}
