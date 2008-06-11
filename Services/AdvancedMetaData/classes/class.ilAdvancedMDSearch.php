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
* @ingroup ServicesAdvancedMetaData 
*/

include_once 'Services/Search/classes/class.ilAbstractSearch.php';

class ilAdvancedMDSearch extends ilAbstractSearch
{
	protected $definition;
	
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
	 * perform search
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function performSearch()
	{
	 	$this->setFields(array('value'));
	 	
		$and = '';
	 	if(count($this->getFilter()))
	 	{
	 		$and = "AND type IN ('".implode("','",$this->getFilter())."')";
	 	}
	 	
	 	
	 	switch($this->getDefinition()->getFieldType())
	 	{
			case ilAdvancedMDFieldDefinition::TYPE_DATE:
			case ilAdvancedMDFieldDefinition::TYPE_DATETIME:
				$query = "SELECT amv.obj_id,type ".
					"FROM adv_md_values AS amv ".
					"JOIN object_data USING (obj_id) ".
					"WHERE value >= ".(int) $this->range_start." ".
					"AND value <= ".(int) $this->range_end." ".
					"AND field_id = ".$this->db->quote($this->getDefinition()->getFieldId())." ".
					$and;
				break;
			
			case ilAdvancedMDFieldDefinition::TYPE_SELECT:
	 		case ilAdvancedMDFieldDefinition::TYPE_TEXT:
	 			$where = $this->__createWhereCondition();
				$locate = $this->__createLocateString();
				
				$query = "SELECT amv.obj_id,type ".
					$locate.
					"FROM adv_md_values as amv ".
					"JOIN object_data USING(obj_id) ".
					$where.
					"AND field_id = ".$this->db->quote($this->getDefinition()->getFieldId())." ".
					$and;
				break;
			
	 	}
		
		if($query)
		{
			$res = $this->db->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$this->search_result->addEntry($row->obj_id,$row->type,$this->__prepareFound($row));
			}
		}
		return $this->search_result;
	}
	
	/**
	 * set time range
	 *
	 * @access public
	 * @param int unix start time
	 * @param int unix end time
	 * 
	 */
	public function setTimeRange($start,$end)
	{
	 	$this->range_start = $start;
	 	$this->range_end = $end;
	}
	
}

?>