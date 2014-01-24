<?php
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

include_once './Services/Search/classes/Lucene/class.ilLuceneQueryParserException.php';

/** 
* Lucene query parser
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesSearch
*/
class ilLuceneQueryParser
{
	protected $query_string;
	protected $parsed_query;
	

	/**
	 * Constructor 
	 * @param string query string
	 * @return
	 */
	public function __construct($a_query_string)
	{
		$this->query_string = $a_query_string;
	}
	
	/**
	 * parse query string 
	 * @return
	 */
	public function parse()
	{
		$this->parsed_query = preg_replace_callback('/(owner:)\s?([A-Za-z0-9_\.\+\*\@!\$\%\~\-]+)/',array($this,'replaceOwnerCallback'),$this->query_string);
	}
	
	/**
	 * get query 
	 * @return
	 */
	public function getQuery()
	{
		return $this->parsed_query;	 
	}
	
	/**
	 * Replace owner callback (preg_replace_callback)
	 */
	protected function replaceOwnerCallback($matches)
	{
		if(isset($matches[2]))
		{
			if($usr_id = ilObjUser::_loginExists($matches[2]))
			{
				return $matches[1].$usr_id;
			}	
		}
		return $matches[0];
	}
	
	
	/**
	 * @throws LuceneQueryParserException
	 */
	public static function validateQuery($a_query)
	{
		// TODO
		// First replace all quote characters

		
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
	 * @throws LuceneQueryParserException
	 */
	protected static function checkAllowedCharacters($query)
	{
		if(preg_match('/[^\pL0-9_+\-:.()\"*?&§€|!{}\[\]\^~\\@#\/$%\'= ]/u',$query) != 0)
		{
			throw new ilLuceneQueryParserException('lucene_err_allowed_characters');
		}
		return true;
	}
	
	/**
	 * Check asterisk
	 * @throws LuceneQueryParserException
	 */
	protected static function checkAsterisk($query)
	{
		if(preg_match('/^[\*]*$|[\s]\*|^\*[^\s]/',$query) != 0)
		{
			throw new ilLuceneQueryParserException('lucene_err_asterisk');
		}
		return true;
	} 
	
	/**
	 * Check ampersands
	 * @throws LuceneQueryParserException
	 */
	protected static function checkAmpersands($query)
	{
		if(preg_match('/[&]{2}/',$query) > 0)
		{
			if(preg_match('/^([\pL0-9_+\-:.()\"*?&|!{}\[\]\^~\\@#\/$%\'=]+( && )?[\pL0-9_+\-:.()\"*?|!{}\[\]\^~\\@#\/$%\'=]+[ ]*)+$/u',$query) == 0)
			{
				throw new ilLuceneQueryParserException('lucene_err_ampersand');
			}
		}
		return true;
	} 

	/**
	 * Check carets
	 * @throws LuceneQueryParserException
	 */
	protected static function checkCaret($query)
	{
		if(preg_match('/[^\\\]\^([^\s]*[^0-9.]+)|[^\\\]\^$/',$query) != 0)
		{
			throw new ilLuceneQueryParserException('lucene_err_caret');
		}
		return true;
	} 

	/**
	 * Check squiggles
	 * @throws LuceneQueryParserException
	 */
	protected static function checkSquiggle($query)
	{
		if(preg_match('/[^\\\]*~[^\s]*[^0-9\s]+/',$query,$matches) != 0)
		{
			throw new ilLuceneQueryParserException('lucene_err_squiggle');
		}
		return true;
	} 

	/**
	 * Check exclamation marks (replacement for NOT)
	 * @throws LuceneQueryParserException
	 */
	protected static function checkExclamationMark($query)
	{
		if(preg_match('/^[^!]*$|^([\pL0-9_+\-:.()\"*?&|!{}\[\]\^~\\@#\/$%\'=]+( ! )?[\pL0-9_+\-:.()\"*?&|!{}\[\]\^~\\@#\/$%\'=]+[ ]*)+$/u',$query,$matches) == 0)
		{
			throw new ilLuceneQueryParserException('lucene_err_exclamation_mark');
		}
		return true;
	} 

	/**
	 * Check question mark (wild card single character)
	 * @throws LuceneQueryParserException
	 */
	protected static function checkQuestionMark($query)
	{
		if(preg_match('/^(\?)|([^\pL0-9_+\-:.()\"*?&|!{}\[\]\^~\\@#\/$%\'=]\?+)/u',$query,$matches) != 0)
		{
			throw new ilLuceneQueryParserException('lucene_err_question_mark');
		}
		return true;
	}
	
	/**
	 * Check parenthesis
	 * @throws LuceneQueryParserException
	 */
	protected static function checkParenthesis($a_query)
	{
		$hasLft = false;
		$hasRgt = false;
		
		$matchLft = 0;
		$matchRgt = 0;
		
		$tmp = array();
		
		if(($matchLft = preg_match_all('/[(]/',$a_query,$tmp)) > 0)
		{
			$hasLft = true;
		}
		if(($matchRgt = preg_match_all('/[)]/',$a_query,$tmp)) > 0)
		{
			$hasRgt = true;
		}
		
		if(!$hasLft || !$hasRgt)
		{
			return true;
		}
		
		
		if(($hasLft && !$hasRgt) || ($hasRgt && !$hasLft))
		{
			throw new ilLuceneQueryParserException('lucene_err_parenthesis_not_closed');
		}
		
		if($matchLft !== $matchRgt)
		{
			throw new ilLuceneQueryParserException('lucene_err_parenthesis_not_closed');
		}
		
		if(preg_match('/\(\s*\)/',$a_query) > 0)
		{
			throw new ilLuceneQueryParserException('lucene_err_parenthesis_empty');
		}
		return true;		
	}  

	/**
	 * Check plus minus
	 * @throws LuceneQueryParserException
	 * 
	 */
	protected static function checkPlusMinus($a_query)
	{
		if(preg_match('/^[^\n+\-]*$|^([+-]?\s*[\pL0-9_:.()\"*?&|!{}\[\]\^~\\@#\/$%\'=]+[ ]?)+$/u',$a_query) == 0)
		{
			throw new ilLuceneQueryParserException('lucene_err_plus_minus');
		}
		return true;
	}

	/**
	 * Check AND OR NOT
	 * @throws LuceneQueryParserException
	 * 
	 */
	protected static function checkANDORNOT($a_query)
	{
		return true;
		
		if(preg_match('/^([\pL0-9_+\-:.()\"*?&|!{}\[\]\^~\\@\/#$%\'=]+\s*((AND )|(OR )|(AND NOT )|(NOT ))?[\pL0-9_+\-:.()\"*?&|!{}\[\]\^~\\@\/#$%\'=]+[ ]*)+$/u',$a_query) == 0)
		{
			throw new ilLuceneQueryParserException('lucene_err_and_or_not');
		}
		return true;
	}

	/**
	 * Check quotes
	 * @throws LuceneQueryParserException
	 * 
	 */
	protected static function checkQuotes($a_query)
	{
		$matches = preg_match_all('/"/',$a_query,$tmp);
		
		if($matches == 0)
		{
			return true;
		}
		
		if(($matches % 2) > 0)
		{
			throw new ilLuceneQueryParserException('lucene_err_quotes');
		}

		if(preg_match('/"\s*"/',$a_query) > 0)
		{
			throw new ilLuceneQueryParserException('lucene_err_quotes_not_empty');
		}
		return true;
	}


	/**
	 * Check colon
	 * @throws LuceneQueryParserException
	 * 
	 */
	protected static function checkColon($a_query)
	{
		if(preg_match('/[^\\\\s]:[\s]|[^\\\\s]:$|[\s][^\\]?:|^[^\\\\s]?:/',$a_query) != 0)
		{
			throw new ilLuceneQueryParserException('lucene_err_colon');
		}
		return true;
	}
}
?>