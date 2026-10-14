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

namespace ILIAS\Language;

/**
 * A language identifier as an enum case (backing value = identifier), generated per module from its
 * `.pot` (see components/ILIAS/Language/tools/language-identifier-enum/). Language::txt() reads it
 * from exactly module(), regardless of which module was loaded last.
 */
interface LanguageIdentifier extends \BackedEnum
{
    public function module(): string;
}
