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

namespace ILIAS\MetaData\DataHelper;

class Constants
{
    /**
     * This monstrosity makes sure durations conform to the format given by LOM,
     * and picks out the relevant numbers.
     * match 1: years, 2: months, 3: days, 4: hours, 5: minutes, 6: seconds
     */
    public const DURATION_REGEX = '/^P(?:(\d+)Y)?(?:(\d+)M)?(?:(\d+)D)' .
    '?(?:T(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)(?:.\d+)?S)?)?$/';

    /**
     * This monstrosity makes sure datetimes conform to the format given by LOM,
     * and picks out the relevant numbers.
     * match 1: YYYY, 2: MM, 3: DD, 4: hh, 5: mm, 6: ss, 7: s (arbitrary many
     * digits for decimal fractions of seconds), 8: timezone, either Z for
     * UTC or +- hh:mm (mm is optional)
     */
    protected const DATETIME_REGEX = '/^(\d{4})(?:-(\d{2})(?:-(\d{2})' .
    '(?:T(\d{2})(?::(\d{2})(?::(\d{2})(?:\.(\d+)(Z|[+\-]' .
    '\d{2}(?::\d{2})?)?)?)?)?)?)?)?$/';

    /**
     * Note that 'xx' should be translated to 'none'
     */
    protected const LANGUAGES = [
        "aa",
        "ab",
        "af",
        "am",
        "ar",
        "as",
        "ay",
        "az",
        "ba",
        "be",
        "bg",
        "bh",
        "bi",
        "bn",
        "bo",
        "br",
        "ca",
        "co",
        "cs",
        "cy",
        "da",
        "de",
        "dz",
        "el",
        "en",
        "eo",
        "es",
        "et",
        "eu",
        "fa",
        "fi",
        "fj",
        "fo",
        "fr",
        "fy",
        "ga",
        "gd",
        "gl",
        "gn",
        "gu",
        "ha",
        "he",
        "hi",
        "hr",
        "hu",
        "hy",
        "ia",
        "ie",
        "ik",
        "id",
        "is",
        "it",
        "iu",
        "ja",
        "jv",
        "ka",
        "kk",
        "kl",
        "km",
        "kn",
        "ko",
        "ks",
        "ku",
        "ky",
        "la",
        "ln",
        "lo",
        "lt",
        "lv",
        "mg",
        "mi",
        "mk",
        "ml",
        "mn",
        "mo",
        "mr",
        "ms",
        "mt",
        "my",
        "na",
        "ne",
        "nl",
        "no",
        "oc",
        "om",
        "or",
        "pa",
        "pl",
        "ps",
        "pt",
        "qu",
        "rm",
        "rn",
        "ro",
        "ru",
        "rw",
        "sa",
        "sd",
        "sg",
        "sh",
        "si",
        "sk",
        "sl",
        "sm",
        "sn",
        "so",
        "sq",
        "sr",
        "ss",
        "st",
        "su",
        "sv",
        "sw",
        "ta",
        "te",
        "tg",
        "th",
        "ti",
        "tk",
        "tl",
        "tn",
        "to",
        "tr",
        "ts",
        "tt",
        "tw",
        "ug",
        "uk",
        "ur",
        "uz",
        "vi",
        "vo",
        "wo",
        "xh",
        "yi",
        "yo",
        "za",
        "zh",
        "zu",
        "xx"
    ];
}
