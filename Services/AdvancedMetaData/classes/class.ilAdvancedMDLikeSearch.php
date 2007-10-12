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

/** 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ilCtrl_Calls 
* @ingroup ServicesAdvancedMetaData 
*/
include_once 'Services/AdvancedMetaData/classes/class.ilAdvancedMDSearch.php';

class ilAdvancedMDLikeSearch extends ilAdvancedMDSearch
{

	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param obj query parser
	 * 
	 */
	public function __construct($query_parser)
	{
	 	parent::__construct($query_parser);
	}
	
	/**
	 * Create where condition
	 *
	 * @access private
	 * @param
	 * 
	 */
	protected function __createWhereCondition()
	{
		$and = "  WHERE ( ";
		$counter = 0;
		foreach($this->query_parser->getQuotedWords() as $word)
		{
			if($counter++)
			{
				$and .= " OR ";
			}
			$and .= ("value LIKE ('%".$word."%')");
		}
		return $and.") ";
	}
}



?>