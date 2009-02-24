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
* Class ilLikeUserDefinedFieldSearch
*
* Performs Mysql Like search in table usr_defined_data
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @package ilias-search
*
*/
include_once 'Services/Search/classes/class.ilUserDefinedFieldSearch.php';

class ilLikeUserDefinedFieldSearch extends ilUserDefinedFieldSearch
{

	/**
	* Constructor
	* @access public
	*/
	function ilLikeUserDefinedFieldSearch(&$qp_obj)
	{
		parent::ilUserDefinedFieldSearch($qp_obj);
	}
	
	/**
	 * 
	 * @param
	 * @return
	 */
	public function setFields($a_fields)
	{
		foreach($a_fields as $field)
		{
			$fields[] = 'f_'.$field;
		}
		parent::setFields($fields ? $fields : array());
	}
	

	function __createWhereCondition()
	{
		$fields = $this->getFields();
		$field = $fields[0];

		$and = "  WHERE ( ";
		$counter = 0;
		foreach($this->query_parser->getQuotedWords() as $word)
		{
			if($counter++)
			{
				$and .= " OR ";
			}
			$and .= ('`'.$field.'` ');

			if(strpos($word,'^') === 0)
			{
				$and .= ("LIKE ('".substr($word,1)."%')");
			}
			else
			{
				$and .= ("LIKE ('%".$word."%')");
			}
		}
		return $and.") ";
	}
}
?>
