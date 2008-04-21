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
* Class ilSearchGUI
*
* Base class for all search classes
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @package ilias-search
*
*/

class ilAbstractSearch
{
	/*
	 * instance of db object
	 */
	var $db = null;
	/*
	 * instance of query parser
	 */
	var $query_parser = null;
	
	/*
	 * instance of result obj
	 */
	var $search_result = null;

	/*
	 * List of all searchable objects
	 */
	var $object_types = array('cat','dbk','crs','fold','frm','grp','lm','sahs','glo','mep','htlm','exc','file','qpl','tst','svy','spl',
						 'chat','icrs','icla','webr','mcst','sess');


	/**
	* Constructor
	* @access public
	*/
	function ilAbstractSearch(&$qp_obj)
	{
		global $ilDB;

		$this->query_parser =& $qp_obj;
		$this->db =& $ilDB;

		include_once 'Services/Search/classes/class.ilSearchResult.php';

		$this->search_result = new ilSearchResult();
	}

	/**
	* Set fields to search 
	* @param array Array of table field (e.g array('title','description'))
	* @access public
	*/
	function setFields($a_fields)
	{
		$this->fields = $a_fields;
	}

	/**
	* Get fields to search 
	* @return array array of search fields. E.g. array(title,description)
	* @access public
	*/
	function getFields()
	{
		return $this->fields ? $this->fields : array();
	}

	/**
	* set object type to search in
	* @param array Array of object types (e.g array('lm','st','pg','dbk'))
	* @access public
	*/
	function setFilter($a_filter)
	{
		if(is_array($a_filter))
		{
			$this->object_types = $a_filter;
		}
	}

	/**
	* Append object type to filter
	* @param string obj_type e.g. 'role'
	* @access public
	*/
	function appendToFilter($a_type)
	{
		if(is_array($this->object_types))
		{
			if(in_array($a_type,$this->object_types))
			{
				return false;
			}
		}
		$this->object_types[] = $a_type;
		
		return true;
	}


	/**
	* get object type to search in
	* @param array Array of object types (e.g array('lm','st','pg','dbk'))
	* @access public
	*/
	function getFilter()
	{
		return $this->object_types ? $this->object_types : array();
	}

	/**
	* build locate string in case of AND search
	* @return string 
	* @access public
	*/
	function __createLocateString()
	{
		if($this->query_parser->getCombination() == 'or')
		{
			return '';
		}
		if(count($this->fields) > 1)
		{
			$complete_str = 'CONCAT(';
			$complete_str .= implode(',',$this->fields);
			$complete_str .= ')';
		}
		else
		{
			$complete_str = $this->fields[0];
		}

		$counter = 0;
		foreach($this->query_parser->getQuotedWords() as $word)
		{
			$locate .= (", LOCATE('".$word."',".$complete_str.") ");
			$locate .= ("as found".$counter++." ");
		}
		
		return $locate;
	}

	function __prepareFound(&$row)
	{
		if($this->query_parser->getCombination() == 'or')
		{
			return array();
		}
		$counter = 0;
		foreach($this->query_parser->getQuotedWords() as $word)
		{
			$res_found = "found".$counter++;
			$found[] = $row->$res_found;
		}
		return $found ? $found : array();
	}

	function &performSearch()
	{
		echo "Should be overwritten.";
	}


}
?>
