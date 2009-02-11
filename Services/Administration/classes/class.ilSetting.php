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
* ILIAS Setting Class
*
* @author Alex Killing <alex.killing@databay.de>

* @version $Id$
*/
class ilSetting
{
	var $setting = array();
	var $module = "";
	
	/**
	* Initialise Settings 
	*/
	function ilSetting($a_module = "common")
	{
		global $ilDB;
		
		$this->module = $a_module;
		// check whether ini file object exists
		if (!is_object($ilDB))
		{
			die ("Fatal Error: ilSettings object instantiated without DB initialisation.");
		}
		$this->read();
	}
		
	/**
	* Read settings data
	*/
	function read()
	{
		global $ilDB;
		
		$this->setting = array();

		$query = "SELECT * FROM settings WHERE module=".$ilDB->quote($this->module, "text");
		$res = $ilDB->query($query);

		while ($row = $ilDB->fetchAssoc($res))
		{
			$this->setting[$row["keyword"]] = $row["value"];
		}
	}
	
	/**
	* get setting
	*
	* @access	public
	*
	* @param	string	keyword
	* @param	string	default_value This value is returned, when no setting has
    *								  been found for the keyword.
	* @return	string	value
	*/
	function get($a_keyword, $a_default_value = false)
	{
		if ($a_keyword == "ilias_version")
		{
			return ILIAS_VERSION;
		}
		
		if (isset($this->setting[$a_keyword]))
		{
			return $this->setting[$a_keyword];
		}
		else
		{
			return $a_default_value;
		}
	}
	
	/**
	 * Delete all settings of a current module
	 *
	 * @access public
	 * 
	 */
	public function deleteAll()
	{
		global $ilDB;
		
		$query = "DELETE FROM settings WHERE module = ".$ilDB->quote($this->module, "text");
		$ilDB->manipulate($query);

		$this->setting = array();

		return true;
	}
	
	/**
	 * Delete all settings corresponding to a like string
	 *
	 * @access public
	 * 
	 */
	public function deleteLike($a_like)
	{
		global $ilDB;

		$query = "DELETE FROM settings WHERE module = ".$ilDB->quote($this->module, "text").
			" AND ".$ilDB->like("keyword", "text", $a_like);
		$ilDB->manipulate($query);
		
		$this->read();
		return true;
	}

	/**
	* delete one value from settingstable
	* @access	public
	* @param	string	keyword
	* @return	string	value
	*/
	function delete($a_keyword)
	{
		global $ilDB;

		$st = $ilDB->manipulate("DELETE FROM settings WHERE keyword = ".
			$ilDB->quote($a_keyword, "text")." AND module = ".
			$ilDB->quote($this->module, "text"));

		unset($this->setting[$a_keyword]);

		return true;
	}
	
	

	/**
	* read all values from settingstable
	* @access	public
	* @return	array	keyword/value pairs
	*/
	function getAll()
	{
		return $this->setting;
	}

	/**
	* write one value to db-table settings
	* @access	public
	* @param	string		keyword
	* @param	string		value
	* @return	boolean		true on success
	*/
	function set($a_key, $a_val)
	{
		global $ilDB;
		
		$this->delete($a_key);

		$sql = "INSERT INTO settings (module, keyword, value) VALUES (".
			$ilDB->quote($this->module, "text") .
			",".$ilDB->quote($a_key, "text").",".$ilDB->quote((string) $a_val, "clob").")";
		$ilDB->manipulate($sql);

		$this->setting[$a_key] = $a_val;

		return true;
	}

}
?>
