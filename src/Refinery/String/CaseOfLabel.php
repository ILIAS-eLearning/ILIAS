<?php

declare(strict_types=1);

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
 *
 * @package ILIAS\Refinery\String
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class CaseOfLabel implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    /**
     * @var string
     */
    protected $language_key;
    /**
     * @var Factory
     */
    protected $factory;
    /**
     * @var array
     */
    protected $not_capitalize = [
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


    /**
     * CaseOfLabel constructor
     *
     * @param string  $language_key
     * @param Factory $factory
     */
    public function __construct(string $language_key, Factory $factory)
    {
        $this->language_key = $language_key;
        $this->factory = $factory;
    }


    /**
     * @inheritDoc
     *
     * @throws LogicException
     */
    public function transform($from)
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

    /**
     * @param array $words
     *
     * @return array
     */
    protected function buildPatterns(array $words) : array
    {
        return array_reduce($words, function (array $patterns, string $word) : array {
            $patterns[$this->buildPattern($word)] = [ $this, "replaceHelper" ];

            return $patterns;
        }, []);
    }


    /**
     * @param string $word
     *
     * @return string
     */
    protected function buildPattern(string $word) : string
    {
        // Before the word muss be the start of the string or a space
        // After the word muss be the end of the string or a space
        // Ignore case to include the uppercase in the first step before
        return "/(\s|^)" . preg_quote($word) . "(\s|$)/i";
    }


    /**
     * @param array $result
     *
     * @return string
     */
    protected function replaceHelper(array $result) : string
    {
        return strtolower($result[0] ?? "");
    }
}
