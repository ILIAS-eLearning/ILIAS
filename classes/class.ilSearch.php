<?php
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