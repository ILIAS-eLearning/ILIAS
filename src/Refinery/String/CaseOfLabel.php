<?php

declare(strict_types=1);

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

namespace ILIAS\Refinery\String;

use ILIAS\Data\Factory;
use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\Transformation;
use InvalidArgumentException;
use LogicException;
use ILIAS\Refinery\DeriveInvokeFromTransform;

/**
 * Class CaseOfLabel
 *
 * Format a text for the title capitalization presentation (Specification at https://docu.ilias.de/goto_docu_pg_1430_42.html)
 *
 * Throws a LogicException in the transform method, if a not supported language is passed
 */
class CaseOfLabel implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    private string $language_key;
    /** @var array<string, string[]>  */
    protected array $not_capitalize = [
        "en" => [
            // conjunctions
            "after",
            "although",
            "and",
            "as far as",
            "as how",
            "as if",
            "as long as",
            "as soon as",
            "as though",
            "as well as",
            "as",
            "because",
            "before",
            "both",
            "but",
            "either",
            "even if",
            "even though",
            "for",
            "how",
            "however",
            "if only",
            "in case",
            "in order that",
            "if",
            "neither",
            "nor",
            "once",
            "only",
            "or",
            "now",
            "provided",
            "rather",
            "than",
            "since",
            "so that",
            "so",
            "than",
            "that",
            "though",
            "till",
            "unless",
            "until",
            "when",
            "whenever",
            "where as",
            "wherever",
            "whether",
            "while",
            "where",
            "yet",
            // prepositions
            "about",
            "above",
            "according to",
            "across",
            "after",
            "against",
            "along with",
            "along",
            "among",
            "apart from",
            "around",
            "as for",
            "as",
            "at",
            "because of",
            "before",
            "behind",
            "below",
            "beneath",
            "beside",
            "between",
            "beyond",
            "but",
            "by means of",
            "by",
            "concerning",
            "despite",
            "down",
            "during",
            "except for",
            "excepting",
            "except",
            "for",
            "from",
            "in addition to",
            "in back of",
            "in case of",
            "in front of",
            "in place of",
            "inside",
            "in spite of",
            "instead of",
            "into",
            "in",
            "like",
            "near",
            "next",
            "off",
            "onto",
            "on top of",
            "out out of",
            "outside",
            "over",
            "past",
            "regarding",
            "round",
            "on",
            "of",
            "since",
            "through",
            "throughout",
            "till",
            "toward",
            "under",
            "underneath",
            "unlike",
            "until",
            "upon",
            "up to",
            "up",
            "to",
            "with",
            "within",
            "without",
            // articles
            "a",
            "an",
            "few",
            "some",
            "the",
            "one",
            "this",
            "that"
        ]
    ];

    public function __construct(string $language_key)
    {
        $this->language_key = $language_key;
    }


    /**
     * @inheritDoc
     * @throws LogicException
     */
    public function transform($from): string
    {
        if (!is_string($from)) {
            throw new InvalidArgumentException(__METHOD__ . " the argument is not a string.");
        }

        if (empty($this->language_key)) {
            throw new LogicException("Please specify a language for the title capitalization");
        }

        if (!isset($this->not_capitalize[$this->language_key])) {
            throw new LogicException("Language " . $this->language_key . " is not supported for the title capitalization");
        }

        // First write the first letter of each word to uppercase
        $to = ucwords(strtolower($from));

        // Then replace all special words and write it again to lowercase
        $to = preg_replace_callback_array($this->buildPatterns($this->not_capitalize[$this->language_key]), $to);

        // Finally the first letter of the whole string muss be always uppercase
        $to = ucfirst($to);

        return $to;
    }

    private function buildPatterns(array $words): array
    {
        return array_reduce($words, function (array $patterns, string $word): array {
            $patterns[$this->buildPattern($word)] = [ $this, "replaceHelper" ];

            return $patterns;
        }, []);
    }

    private function buildPattern(string $word): string
    {
        // Before the word muss be the start of the string or a space
        // After the word muss be the end of the string or a space
        // Ignore case to include the uppercase in the first step before
        return "/(\s|^)" . preg_quote($word, '/') . "(\s|$)/i";
    }

    private function replaceHelper(array $result): string
    {
        return strtolower($result[0] ?? "");
    }
}
