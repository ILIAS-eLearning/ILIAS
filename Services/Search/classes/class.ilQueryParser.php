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


define('QP_COMBINATION_AND', 'and');
define('QP_COMBINATION_OR', 'or');

/**
* Class ilQueryParser
*
* Class for parsing search queries. An instance of this object is required for every Search class (MetaSearch ...)
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @package ilias-search
*
*/
class ilQueryParser
{
    /**
     * Minimum of characters required for search
     */
    const MIN_WORD_LENGTH = 3;

    public $lng = null;

    public $min_word_length = 0;
    public $global_min_length = null;

    public $query_str;
    public $quoted_words = array();
    public $message; // Translated error message
    public $combination; // combiniation of search words e.g 'and' or 'or'
    protected $settings = null;
    protected $wildcards_allowed; // [bool]

    /**
    * Constructor
    * @access public
    */
    public function __construct($a_query_str)
    {
        global $DIC;

        $lng = $DIC['lng'];

        define('MIN_WORD_LENGTH', self::MIN_WORD_LENGTH);

        $this->lng = $lng;

        $this->query_str = $a_query_str;
        $this->message = '';

        include_once './Services/Search/classes/class.ilSearchSettings.php';
        $this->settings = ilSearchSettings::getInstance();

        if (!$this->setMinWordLength(1)) {
            $this->setMinWordLength(MIN_WORD_LENGTH);
        }
        
        $this->setAllowedWildcards(false);
    }

    public function setMinWordLength($a_length, $a_force = false)
    {
        // Due to a bug in mysql fulltext search queries with min_word_legth < 3
        // might freeze the system.
        // Thus min_word_length cannot be set to values < 3 if the mysql fulltext is used.
        if (!$a_force and $this->settings->enabledIndex() and $a_length < 3) {
            ilLoggerFactory::getLogger('src')->debug('Disabled min word length');
            return false;
        }
        $this->min_word_length = $a_length;
        return true;
    }
    public function getMinWordLength()
    {
        return $this->min_word_length;
    }
    
    public function setGlobalMinLength($a_value)
    {
        if ($a_value !== null) {
            $a_value = (int) $a_value;
            if ($a_value < 1) {
                return;
            }
        }
        $this->global_min_length = $a_value;
    }
    
    public function getGlobalMinLength()
    {
        return $this->global_min_length;
    }
    
    public function setAllowedWildcards($a_value)
    {
        $this->wildcards_allowed = (bool) $a_value;
    }
    
    public function getAllowedWildcards()
    {
        return $this->wildcards_allowed;
    }

    public function setMessage($a_msg)
    {
        $this->message = $a_msg;
    }
    public function getMessage()
    {
        return $this->message;
    }
    public function appendMessage($a_msg)
    {
        if (strlen($this->getMessage())) {
            $this->message .= '<br />';
        }
        $this->message .= $a_msg;
    }

    public function setCombination($a_combination)
    {
        $this->combination = $a_combination;
    }
    public function getCombination()
    {
        return $this->combination;
    }

    public function getQueryString()
    {
        return trim($this->query_str);
    }
    public function getWords()
    {
        return $this->words ? $this->words : array();
    }

    public function getQuotedWords($with_quotation = false)
    {
        if ($with_quotation) {
            return $this->quoted_words ? $this->quoted_words : array();
        } else {
            foreach ($this->quoted_words as $word) {
                $tmp_word[] = str_replace("\"", '', $word);
            }
            return $tmp_word ? $tmp_word : array();
        }
        return $tmp_word ?? [];
    }

    public function getLuceneQueryString()
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
    public function parse()
    {
        $this->words = array();

        #if(!strlen($this->getQueryString()))
        #{
        #	return false;
        #}

        $words = explode(' ', trim($this->getQueryString()));
        foreach ($words as $word) {
            if (!strlen(trim($word))) {
                continue;
            }
            
            if (strlen(trim($word)) < $this->getMinWordLength()) {
                $this->setMessage(sprintf($this->lng->txt('search_minimum_info'), $this->getMinWordLength()));
                continue;
            }
            
            $this->words[] = ilUtil::prepareDBString($word);
        }
        
        $fullstr = trim($this->getQueryString());
        if (!in_array($fullstr, $this->words)) {
            $this->words[] = ilUtil::prepareDBString($fullstr);
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

    public function __parseQuotation()
    {
        if (!strlen($this->getQueryString())) {
            $this->quoted_words[] = '';
            return false;
        }
        $query_str = $this->getQueryString();
        while (preg_match("/\".*?\"/", $query_str, $matches)) {
            $query_str = str_replace($matches[0], '', $query_str);
            $this->quoted_words[] = ilUtil::prepareDBString($matches[0]);
        }

        // Parse the rest
        $words = explode(' ', trim($query_str));
        foreach ($words as $word) {
            if (!strlen(trim($word))) {
                continue;
            }
            
            $this->quoted_words[] = ilUtil::prepareDBString($word);
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
    }

    public function validate()
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
