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
 * The dated "not translated yet" marker of the legacy `.lang` files ("28 08 2012 new variable") in
 * a `###` comment or a lng_data remark. The conversion to PO turns it into the `fuzzy` flag, the
 * installation does not take it over as a remark. Only the dated marker counts - a note merely
 * mentioning "new variable" is a note.
 */
final class LegacyFuzzyMarker
{
    private const string PATTERN = '/^\s*(\d{1,2}\s+\d{1,2}\s+\d{4}|\d{1,2}\s+[A-Za-z]{3}\s+\d{4}|\d{4}-\d{1,2}-\d{1,2})\b.*\bnew variable\b/i';

    public static function matches(string $comment): bool
    {
        return preg_match(self::PATTERN, $comment) === 1;
    }
}
