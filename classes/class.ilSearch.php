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
* search
* 
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
* 
* @package application
*/
class ilSearch
{
	/**
	* ilias object
	* @var object DB
	* @access public
	*/	
	var $ilias;

	/**
	* search text
	* @var string
	* @access public
	*/	
	var $text;

	/**
	* search result
	* @var array
	* @access public
	*/	
	var $result;

	/**
	* result count
	* @var integer
	* @access public
	*/	
	var $hits;

	/**
	* search options
	* @var array
	* @access public
	*/	
	var $options;

	/**
	* ?????
	* @var string
	* @access public
	*/	
	var $area;

	/**
	* Constructor
	* @access	public
	*/
	function ilSearch()
	{
		global $ilias;
		
		// Initiate variables
		$this->ilias =& $ilias;
	}

	/**
	* search database with given values
	* execute() performs a search on the databasetables
	* @access private
	*/
	function execute()
	{
		$this->result = array();
		
		if (empty($this->text))
		{
			return false;
		}
		//now only user search and phrase search
		//search for login  firstname  lastname email
		$w = "login LIKE '%".$this->text."%'";
		$w .= " OR firstname LIKE '%".$this->text."%'";
		$w .= " OR lastname LIKE '%".$this->text."%'";
		$w .= " OR email LIKE '%".$this->text."%'";

		$query = "SELECT * FROM usr_data WHERE ".$w;	
		$res = $this->ilias->db->query($query);
		
		$this->hits = $res->numRows();

		if ($this->hits > 0)
		{
			while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$this->result[] = array(
										"text" => $row["firstname"]." ".$row["lastname"],
										"link" => "mail.php?to=".$row["usr_id"]
										);
			}
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* set options
	* @access	public
	* @param	array
	*/
	function setOptions($a_arr)
	{
		$this->options = $a_arr;
	}

	/**
	* set area
	* @access	public
	* @param	string
	*/
	function setArea($a_str)
	{
		$this->area = $a_str;
	}

	/**
	* set searchtext
	* @access	public
	* @param	string 
	*/
	function setText($a_str)
	{
		$this->text = trim($a_str);
	}
} // END class.Search
?>