<?php

declare(strict_types=1);
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/


/**
* Lucene query parser
*
* @author Stefan Meyer <meyer@leifos.com>
*
*
* @ingroup ServicesSearch
*/
class ilLuceneQueryParser
{
    protected string $query_string;
    protected string $parsed_query = '';


    /**
     * Constructor
     * @param string query string
     */
    public function __construct($a_query_string)
    {
        $this->query_string = $a_query_string;
    }

    /**
     * parse query string
     * @return void
     */
    public function parse(): void
    {
        $this->parsed_query = (string) preg_replace_callback(
            '/(owner:)\s?([A-Za-z0-9_\.\+\*\@!\$\%\~\-]+)/',
            array($this,'replaceOwnerCallback'),
            $this->query_string
        );
    }

    /**
     * Append asterisk for remote search from global search form field
     */
    public function parseAutoWildcard(): void
    {
        $this->parsed_query = trim($this->query_string);
        if (stristr($this->parsed_query, '*')) {
            return;
        }
        if (substr($this->parsed_query, -1) !== '"') {
            $this->parsed_query .= '*';
        }
    }

    public function getQuery(): string
    {
        return $this->parsed_query;
    }

    /**
     * Replace owner callback (preg_replace_callback)
     */
    protected function replaceOwnerCallback(array $matches): string
    {
        if (isset($matches[2])) {
            if ($usr_id = ilObjUser::_loginExists($matches[2])) {
                return $matches[1] . $usr_id;
            }
        }
        return $matches[0];
    }


    /**
     * @throws ilLuceneQueryParserException
     * @todo add multi byte query validation.
     */
    public static function validateQuery($a_query): bool
    {
        #ilLuceneQueryParser::checkAllowedCharacters($a_query);
        #ilLuceneQueryParser::checkAsterisk($a_query);
        #ilLuceneQueryParser::checkAmpersands($a_query);
        ilLuceneQueryParser::checkCaret($a_query);
        ilLuceneQueryParser::checkSquiggle($a_query);
        #ilLuceneQueryParser::checkExclamationMark($a_query);
        #ilLuceneQueryParser::checkQuestionMark($a_query);
        ilLuceneQueryParser::checkParenthesis($a_query);
        #ilLuceneQueryParser::checkPlusMinus($a_query);
        #ilLuceneQueryParser::checkANDORNOT($a_query);
        ilLuceneQueryParser::checkQuotes($a_query);
        #ilLuceneQueryParser::checkColon($a_query);
        return true;
    }

    /**
     * Check allowed characters
     * @throws ilLuceneQueryParserException
     */
    protected static function checkAllowedCharacters(string $query): bool
    {
        if (preg_match('/[^\pL0-9_+\-:.()\"*?&§€|!{}\[\]\^~\\@#\/$%\'= ]/u', $query) != 0) {
            throw new ilLuceneQueryParserException('lucene_err_allowed_characters');
        }
        return true;
    }

    /**
     * Check asterisk
     * @throws ilLuceneQueryParserException
     */
    protected static function checkAsterisk(string $query): bool
    {
        if (preg_match('/^[\*]*$|[\s]\*|^\*[^\s]/', $query) != 0) {
            throw new ilLuceneQueryParserException('lucene_err_asterisk');
        }
        return true;
    }

    /**
     * Check ampersands
     * @throws ilLuceneQueryParserException
     */
    protected static function checkAmpersands(string $query): bool
    {
        if (preg_match('/[&]{2}/', $query) > 0) {
            if (preg_match('/^([\pL0-9_+\-:.()\"*?&|!{}\[\]\^~\\@#\/$%\'=]+( && )?[\pL0-9_+\-:.()\"*?|!{}\[\]\^~\\@#\/$%\'=]+[ ]*)+$/u', $query) == 0) {
                throw new ilLuceneQueryParserException('lucene_err_ampersand');
            }
        }
        return true;
    }

    /**
     * Check carets
     * @throws ilLuceneQueryParserException
     */
    protected static function checkCaret(string $query): bool
    {
        if (preg_match('/[^\\\]\^([^\s]*[^0-9.]+)|[^\\\]\^$/', $query) != 0) {
            throw new ilLuceneQueryParserException('lucene_err_caret');
        }
        return true;
    }

    /**
     * Check squiggles
     * @throws ilLuceneQueryParserException
     */
    protected static function checkSquiggle(string $query): bool
    {
        if (preg_match('/[^\\\]*~[^\s]*[^0-9\s]+/', $query, $matches) != 0) {
            throw new ilLuceneQueryParserException('lucene_err_squiggle');
        }
        return true;
    }

    /**
     * Check exclamation marks (replacement for NOT)
     * @throws ilLuceneQueryParserException
     */
    protected static function checkExclamationMark(string $query): bool
    {
        if (preg_match('/^[^!]*$|^([\pL0-9_+\-:.()\"*?&|!{}\[\]\^~\\@#\/$%\'=]+( ! )?[\pL0-9_+\-:.()\"*?&|!{}\[\]\^~\\@#\/$%\'=]+[ ]*)+$/u', $query, $matches) == 0) {
            throw new ilLuceneQueryParserException('lucene_err_exclamation_mark');
        }
        return true;
    }

    /**
     * Check question mark (wild card single character)
     * @throws ilLuceneQueryParserException
     */
    protected static function checkQuestionMark(string $query): bool
    {
        if (preg_match('/^(\?)|([^\pL0-9_+\-:.()\"*?&|!{}\[\]\^~\\@#\/$%\'=]\?+)/u', $query, $matches) != 0) {
            throw new ilLuceneQueryParserException('lucene_err_question_mark');
        }
        return true;
    }

    /**
     * Check parenthesis
     * @throws ilLuceneQueryParserException
     */
    protected static function checkParenthesis(string $a_query): bool
    {
        $hasLft = false;
        $hasRgt = false;

        $matchLft = 0;
        $matchRgt = 0;

        $tmp = array();

        if (($matchLft = preg_match_all('/[(]/', $a_query, $tmp)) > 0) {
            $hasLft = true;
        }
        if (($matchRgt = preg_match_all('/[)]/', $a_query, $tmp)) > 0) {
            $hasRgt = true;
        }

        if (!$hasLft || !$hasRgt) {
            return true;
        }


        if (($hasLft && !$hasRgt) || ($hasRgt && !$hasLft)) {
            throw new ilLuceneQueryParserException('lucene_err_parenthesis_not_closed');
        }

        if ($matchLft !== $matchRgt) {
            throw new ilLuceneQueryParserException('lucene_err_parenthesis_not_closed');
        }

        if (preg_match('/\(\s*\)/', $a_query) > 0) {
            throw new ilLuceneQueryParserException('lucene_err_parenthesis_empty');
        }
        return true;
    }

    /**
     * Check plus minus
     * @throws ilLuceneQueryParserException
     *
     */
    protected static function checkPlusMinus(string $a_query): bool
    {
        if (preg_match('/^[^\n+\-]*$|^([+-]?\s*[\pL0-9_:.()\"*?&|!{}\[\]\^~\\@#\/$%\'=]+[ ]?)+$/u', $a_query) == 0) {
            throw new ilLuceneQueryParserException('lucene_err_plus_minus');
        }
        return true;
    }

    /**
     * Check AND OR NOT
     * @throws ilLuceneQueryParserException
     *
     */
    protected static function checkANDORNOT(string $a_query): bool
    {
        if (preg_match('/^([\pL0-9_+\-:.()\"*?&|!{}\[\]\^~\\@\/#$%\'=]+\s*((AND )|(OR )|(AND NOT )|(NOT ))?[\pL0-9_+\-:.()\"*?&|!{}\[\]\^~\\@\/#$%\'=]+[ ]*)+$/u', $a_query) == 0) {
            throw new ilLuceneQueryParserException('lucene_err_and_or_not');
        }
        return true;
    }

    /**
     * Check quotes
     * @throws ilLuceneQueryParserException
     *
     */
    protected static function checkQuotes(string $a_query): bool
    {
        $matches = preg_match_all('/"/', $a_query, $tmp);

        if ($matches == 0) {
            return true;
        }

        if (($matches % 2) > 0) {
            throw new ilLuceneQueryParserException('lucene_err_quotes');
        }

        if (preg_match('/"\s*"/', $a_query) > 0) {
            throw new ilLuceneQueryParserException('lucene_err_quotes_not_empty');
        }
        return true;
    }


    /**
     * Check colon
     * @throws ilLuceneQueryParserException
     */
    protected static function checkColon(string $a_query): bool
    {
        if (preg_match('/[^\\\\s]:[\s]|[^\\\\s]:$|[\s][^\\]?:|^[^\\\\s]?:/', $a_query) != 0) {
            throw new ilLuceneQueryParserException('lucene_err_colon');
        }
        return true;
    }
}
