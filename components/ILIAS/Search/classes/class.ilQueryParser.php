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

/**
* Class ilQueryParser
*
* Class for parsing search queries. An instance of this object is required for every Search class (MetaSearch ...)
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @package ilias-search
*
*/
class ilQueryParser
{
    /**
     * Minimum of characters required for search
     */
    public const MIN_WORD_LENGTH = 3;
    public const QP_COMBINATION_AND = 'and';
    public const QP_COMBINATION_OR = 'or';

    protected ilLanguage $lng;
    protected ilSearchSettings $settings;

    private int $min_word_length = 0;
    private int $global_min_length = 0;

    private string $query_str;
    private array $quoted_words = array();
    private string $message; // Translated error message
    private string $combination = ''; // combiniation of search words e.g 'and' or 'or'
    private bool $wildcards_allowed; // [bool]
    /**
     * @var string[]
     */
    private array $words;


    public function __construct(string $a_query_str)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->settings = ilSearchSettings::getInstance();

        if (!defined('MIN_WORD_LENGTH')) {
            define('MIN_WORD_LENGTH', self::MIN_WORD_LENGTH);
        }


        $this->query_str = $a_query_str;
        $this->message = '';

        $this->setMinWordLength(1);

        $this->setAllowedWildcards(false);
    }

    public function setMinWordLength(int $a_length): void
    {
        $this->min_word_length = $a_length;
    }

    public function getMinWordLength(): int
    {
        return $this->min_word_length;
    }

    public function setGlobalMinLength(int $a_value): void
    {
        if ($a_value < 1) {
            return;
        }

        $this->global_min_length = $a_value;
    }

    public function getGlobalMinLength(): int
    {
        return $this->global_min_length;
    }

    public function setAllowedWildcards(bool $a_value): void
    {
        $this->wildcards_allowed = $a_value;
    }

    public function getAllowedWildcards(): bool
    {
        return $this->wildcards_allowed;
    }

    public function setMessage(string $a_msg): void
    {
        $this->message = $a_msg;
    }
    public function getMessage(): string
    {
        return $this->message;
    }
    public function appendMessage(string $a_msg): void
    {
        if (strlen($this->getMessage())) {
            $this->message .= '<br />';
        }
        $this->message .= $a_msg;
    }

    public function setCombination(string $a_combination): void
    {
        $this->combination = $a_combination;
    }
    public function getCombination(): string
    {
        return $this->combination;
    }

    public function getQueryString(): string
    {
        return trim($this->query_str);
    }

    /**
     * @return string[]
     */
    public function getWords(): array
    {
        return $this->words ?? array();
    }

    /**
     * @return string[]
     */
    public function getQuotedWords(bool $with_quotation = false): array
    {
        if ($with_quotation) {
            return $this->quoted_words ?: [];
        }

        foreach ($this->quoted_words as $word) {
            $tmp_word[] = str_replace("\"", '', $word);
        }
        return $tmp_word ?? [];
    }

    public function getLuceneQueryString(): string
    {
        $counter = 0;
        $tmp_str = "";
        foreach ($this->getQuotedWords(true) as $word) {
            if ($counter++) {
                $tmp_str .= (" " . strtoupper($this->getCombination()) . " ");
            }
            $tmp_str .= $word;
        }
        return $tmp_str;
    }
    public function parse(): bool
    {
        $this->words = array();


        $words = explode(' ', trim($this->getQueryString()));
        foreach ($words as $word) {
            if (!strlen(trim($word))) {
                continue;
            }

            if (strlen(trim($word)) < $this->getMinWordLength()) {
                $this->setMessage(sprintf($this->lng->txt('search_minimum_info'), $this->getMinWordLength()));
                continue;
            }

            $this->words[] = $word;
        }

        $fullstr = trim($this->getQueryString());
        if (!in_array($fullstr, $this->words)) {
            $this->words[] = $fullstr;
        }

        if (!$this->getAllowedWildcards()) {
            // #14768
            foreach ($this->words as $idx => $word) {
                if (!stristr($word, '\\')) {
                    $word = str_replace('%', '\%', $word);
                    $word = str_replace('_', '\_', $word);
                }
                $this->words[$idx] = $word;
            }
        }

        // Parse strings like && 'A "B C D" E' as 'A' && 'B C D' && 'E'
        // Can be used in LIKE search or fulltext search > MYSQL 4.0
        $this->__parseQuotation();

        return true;
    }

    public function __parseQuotation(): bool
    {
        if (!strlen($this->getQueryString())) {
            $this->quoted_words[] = '';
            return false;
        }
        $query_str = $this->getQueryString();
        while (preg_match("/\".*?\"/", $query_str, $matches)) {
            $query_str = str_replace($matches[0], '', $query_str);
            $this->quoted_words[] = $matches[0];
        }

        // Parse the rest
        $words = explode(' ', trim($query_str));
        foreach ($words as $word) {
            if (!strlen(trim($word))) {
                continue;
            }

            $this->quoted_words[] = $word;
        }

        if (!$this->getAllowedWildcards()) {
            // #14768
            foreach ($this->quoted_words as $idx => $word) {
                if (!stristr($word, '\\')) {
                    $word = str_replace('%', '\%', $word);
                    $word = str_replace('_', '\_', $word);
                }
                $this->quoted_words[$idx] = $word;
            }
        }
        return true;
    }

    public function validate(): bool
    {
        // Words with less than 3 characters
        if (strlen($this->getMessage())) {
            return false;
        }
        // No search string given
        if ($this->getMinWordLength() and !count($this->getWords())) {
            $this->setMessage($this->lng->txt('msg_no_search_string'));
            return false;
        }
        // No search string given
        if ($this->getGlobalMinLength() and strlen(str_replace('"', '', $this->getQueryString())) < $this->getGlobalMinLength()) {
            $this->setMessage(sprintf($this->lng->txt('search_minimum_info'), $this->getGlobalMinLength()));
            return false;
        }

        return true;
    }
}
