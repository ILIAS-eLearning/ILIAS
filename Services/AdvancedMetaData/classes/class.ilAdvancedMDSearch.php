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
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ServicesAdvancedMetaData 
*/

include_once 'Services/Search/classes/class.ilAbstractSearch.php';

class ilAdvancedMDSearch extends ilAbstractSearch
{
	protected $definition;
	protected $adt;
	
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
	 * set Definition
	 *
	 * @access public
	 * @param obj field definition object
	 * 
	 */
	public function setDefinition($a_def)
	{
	 	$this->definition = $a_def;
	}
	
	/**
	 * get definition
	 *
	 * @access public
	 * 
	 */
	public function getDefinition()
	{
	 	return $this->definition;
	}
	
	/**
	 * set search element
	 *
	 * @access public
	 * @param ilADTSearchBridge 
	 * 
	 */
	public function setSearchElement($a_adt)
	{
	 	$this->adt = $a_adt;
	}
	
	/**
	 * get search element
	 *
	 * @access public
	 * @return ilADTSearchBridge
	 */
	public function getSearchElement()
	{
	 	return $this->adt;
	}
	
	/**
	 * perform search
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function performSearch()
	{	 		
		$this->query_parser->parse();
		
		$locate = null;
		$parser_value = $this->getDefinition()->getSearchQueryParserValue($this->getSearchElement());
		if($parser_value)
		{		
			$this->setFields(array("value"));		
			$locate = $this->__createLocateString();			
		}
		
		$search_type = strtolower(substr(get_class($this), 12, -6));
		
		$res_field = $this->getDefinition()->searchObjects($this->getSearchElement(), $this->query_parser, $this->getFilter(), $locate, $search_type);							 	
		if(is_array($res_field))
		{			
			foreach($res_field as $row)
			{				
				$found = is_array($row["found"]) ? $row["found"] : array();				
				$this->search_result->addEntry($row["obj_id"],$row["type"],$found);
			}
			return $this->search_result;
		}		
	}	
}

?>