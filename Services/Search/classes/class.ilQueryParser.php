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
class ilQueryParser
{
	var $lng = null;


	var $query_str;
	var $message; // Translated error message
	var $combination; // combiniation of search words e.g 'and' or 'or'

	/**
	* Constructor
	* @access public
	*/
	function ilQueryParser($a_query_str)
	{
		global $lng;

		$this->lng =& $lng;

		$this->query_str = $a_query_str;
		$this->message = '';
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

	function parse()
	{
		$this->words = array();

		if(!strlen($this->getQueryString()))
		{
			return false;
		}

		$words = explode(' ',$this->getQueryString());
		foreach($words as $word)
		{
			if(strlen(trim($word)) < 3)
			{
				$this->setMessage($this->lng->txt('search_minimum_three'));
				continue;
			}
			$this->words[] = $word;
		}

		return true;
	}

	function validate()
	{
		// Words with less than 3 characters
		if(strlen($this->getMessage()))
		{
			return false;
		}
		// No search string given
		if(!count($this->getWords()))
		{
			$this->setMessage($this->lng->txt('msg_no_search_string'));
			return false;
		}

		return true;
	}
}
?>
