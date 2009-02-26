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
* ILIAS Cache Class
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*/
class ilCache
{
	private $module = "";
	
	/**
	* Initialise Cache 
	*/
	function __construct($a_module = "common")
	{
		global $ilDB;
		
		$this->module = $a_module;
	}
	
	/**
	* get cached value
	*
	* @access	public
	*
	* @param	string	keyword
	* @return	string	value
	*/
	public function getValue($a_keyword)
	{
		global $ilDB;
		$query = sprintf("SELECT * FROM data_cache WHERE module = %s AND keyword = %s",
			$ilDB->quote($this->module,'text'),
			$ilDB->quote($a_keyword,'text')
		);
		$res = $ilDB->query($query);

		if ($res->numRows() == 1) 
		{
			$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
			return $row["value"];
		}
		else
		{
			return NULL;
		}
	}
	
	/**
	* get cached value
	*
	* @access	public
	*
	* @param	string	module
	* @param	string	keyword
	* @return	string	value
	*/
	public function getValueForModule($a_module, $a_keyword)
	{
		global $ilDB;
		$query = sprintf("SELECT * FROM data_cache WHERE module = %s AND keyword = %s",
			$ilDB->quote($a_module ,'text'),
			$ilDB->quote($a_keyword ,'text')
		);
		$res = $ilDB->query($query);

		if ($res->numRows() == 1) 
		{
			$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
			return $row["value"];
		}
		else
		{
			return NULL;
		}
	}
	
	/**
	* Delete all cached values of a current module
	*
	* @param string $a_module A module or if empty, the current module
	* @access public
	* 
	*/
	public function deleteAll($a_module = "")
	{
		global $ilDB;
		
		$module = (strlen($a_module) ? $a_module : $this->module);
		$query = sprintf("DELETE FROM data_cache WHERE module = %s",
			$ilDB->quote($module ,'text')
		);
		$res = $ilDB->manipulate($query);
	}
	
	/**
	* Delete a single value from the data cache
	* @access	public
	* @param	string	keyword
	*/
	public function deleteValue($a_keyword)
	{
		global $ilDB;

		$query = sprintf("DELETE FROM data_cache WHERE keyword = %s AND module = %s",
			$ilDB->quote($a_keyword ,'text'),
			$ilDB->quote($this->module ,'text')
		);
		$res = $ilDB->manipulate($query);
	}
	
	/**
	* Write a cached value
	*
	* @access	public
	* @param	string	$a_key keyword
	* @param	string $a_val value
	*/
	public function setValue($a_key, $a_val)
	{
		global $ilDB, $ilLog;
		
		$sql = sprintf("DELETE FROM data_cache WHERE keyword = %s AND module = %s",
			$ilDB->quote($a_key ,'text'),
			$ilDB->quote($this->module ,'text')
		);
		$res = $ilDB->manipulate($sql);

		$values = array(
			'module'	=> array('text',$this->module),
			'keyword'	=> array('text',$a_key),
			'value'		=> array('clob',$a_val)
			);
		$ilDB->insert('data_cache',$values);
	}

}
?>
