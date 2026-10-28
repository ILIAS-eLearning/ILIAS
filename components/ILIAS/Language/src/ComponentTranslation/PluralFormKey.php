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
 * A form of a plural message of a migrated module in a flat identifier => value map (admin GUI rows,
 * local changes, three-way comparison, export/import): `<identifier> [<form>]`, e.g. `poll_population [1]`.
 * The database holds only the identifier with its default value, see MigratedLanguageFileSync::collapsePluralForms().
 *
 * Only counts as a form key where the identifier is a plural message (shipped or in the overlay); a
 * legacy identifier that merely looks like one stays ordinary.
 */
final class PluralFormKey
{
    public static function of(string $identifier, int $form): string
    {
        return sprintf('%s [%d]', $identifier, $form);
    }

    /**
     * @return array{0: string, 1: int}|null identifier and form of a key shaped like of(), otherwise `null`
     */
    public static function parse(string $key): ?array
    {
        if (preg_match('/\A(.+) \[(0|[1-9]\d?)\]\z/s', $key, $matches) !== 1) {
            return null;
        }

        return [$matches[1], (int) $matches[2]];
    }
}
