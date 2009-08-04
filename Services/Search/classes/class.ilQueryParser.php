<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* Class ilQueryParser
*
* Class for parsing search queries. An instance of this object is required for every Search class (MetaSearch ...)
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @package ilias-search
*
*/
define('QP_COMBINATION_AND','and');
define('QP_COMBINATION_OR','or');

class ilQueryParser
{
	var $lng = null;

	var $min_word_length = 0;


	var $query_str;
	var $quoted_words = array();
	var $message; // Translated error message
	var $combination; // combiniation of search words e.g 'and' or 'or'

	/**
	* Constructor
	* @access public
	*/
	function ilQueryParser($a_query_str)
	{
		global $lng;

		define(MIN_WORD_LENGTH,3);

		$this->lng =& $lng;

		$this->query_str = $a_query_str;
		$this->message = '';

		$this->min_word_length = MIN_WORD_LENGTH;
	}

	function setMinWordLength($a_length)
	{
		$this->min_word_length = $a_length;
	}
	function getMinWordLength()
	{
		return $this->min_word_length;
	}

	function setMessage($a_msg)
	{
		$this->message = $a_msg;
	}
	function getMessage()
	{
		return $this->message;
	}
	function appendMessage($a_msg)
	{
		if(strlen($this->getMessage()))
		{
			$this->message .= '<br />';
		}
		$this->message .= $a_msg;
	}

	function setCombination($a_combination)
	{
		$this->combination = $a_combination;
	}
	function getCombination()
	{
		return $this->combination;
	}

	function getQueryString()
	{
		return trim($this->query_str);
	}
	function getWords()
	{
		return $this->words ? $this->words : array();
	}

	function getQuotedWords($with_quotation = false)
	{
		if($with_quotation)
		{
			return $this->quoted_words ? $this->quoted_words : array();
		}
		else
		{
			foreach($this->quoted_words as $word)
			{
				$tmp_word[] = str_replace('\"','',$word);
			}
			return $tmp_word ? $tmp_word : array();
		}
	}

	function getLuceneQueryString()
	{
		$counter = 0;
		$tmp_str = "";
		foreach($this->getQuotedWords(true) as $word) {
			if($counter++)
			{
				$tmp_str .= (" ".strtoupper($this->getCombination())." ");
			}
			$tmp_str .= $word;
		}
		return $tmp_str;
	}
	function parse()
	{
		$this->words = array();

		#if(!strlen($this->getQueryString()))
		#{
		#	return false;
		#}

		$words = explode(' ',trim($this->getQueryString()));
		foreach($words as $word)
		{
			if(!strlen(trim($word)))
			{
				continue;
			}
			
			#if(strlen(trim($word)) < $this->getMinWordLength())
			#{
			#	$this->setMessage($this->lng->txt('search_minimum_three'));
			#	continue;
			#}
			$this->words[] = ilUtil::prepareDBString($word);
		}
		
		$fullstr = trim($this->getQueryString());
		if (!in_array($fullstr, $this->words))
		{
			$this->words[] = ilUtil::prepareDBString($fullstr);
		}
		
		// Parse strings like && 'A "B C D" E' as 'A' && 'B C D' && 'E'
		// Can be used in LIKE search or fulltext search > MYSQL 4.0
		$this->__parseQuotation();

		return true;
	}

	function __parseQuotation()
	{
		if(!strlen($this->getQueryString()))
		{
			$this->quoted_words[] = '';
			return false;
		}
		$query_str = $this->getQueryString();
		while(preg_match("/\".*?\"/",$query_str,$matches))
		{
			$query_str = str_replace($matches[0],'',$query_str);
			$this->quoted_words[] = ilUtil::prepareDBString($matches[0]);
		}

		// Parse the rest
		$words = explode(' ',trim($query_str));
		foreach($words as $word)
		{
			if(!strlen(trim($word)))
			{
				continue;
			}
			$this->quoted_words[] = ilUtil::prepareDBString($word);
		}
		
	}

	function validate()
	{
		// Words with less than 3 characters
		if(strlen($this->getMessage()))
		{
			return false;
		}
		// No search string given
		if($this->getMinWordLength() and !count($this->getWords()))
		{
			$this->setMessage($this->lng->txt('msg_no_search_string'));
			return false;
		}

		return true;
	}
}
?>
