<?php
/**
 * search
 * @author Peter Gabriel <pgabriel@databay.de>
 * 
 * @package ilias-core
 * @version $Id$
 */
class Search extends PEAR
{
	/**
	* database handler
	*
	* @var object DB
	*/	
	var $db;

	/**
	* Constructor
	*
	* setup database
	*
	* @param object database handler
	*/
	function Search(&$dbhandle)
	{
		// Initiate variables
		$this->db =& $dbhandle;
	}

	/**
	* search database with given values
	 *
	 * execute() performs a search on the databasetables
	 * 
	* @access private
	*/

	function execute()
	{
		$this->result = array();
		
		if ($this->text == "")
			return false;
		//now only user search and phrase search
		//search for login  firstname  surname email
		$w = "login LIKE '%".$this->text."%'";
		$w .= " OR firstname LIKE '%".$this->text."%'";
		$w .= " OR surname LIKE '%".$this->text."%'";
		$w .= " OR email LIKE '%".$this->text."%'";
		
		$query = "SELECT * FROM user_data WHERE ".$w;	
		$res = $this->db->query($query);
		
		$this->hits = $res->numRows();

		if ($this->hits > 0)
		{
			while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$this->result[] = array(
					"text" => $row["firstname"]." ".$row["surname"],
					"link" => "mail.php?to=".$row["usr_id"]
				);
			}
			return true;
		}
		else
			return false;
	}

	/**
	* set options
	* @param array
	* @access public
	*/
	function setOptions($ar)
	{
		$this->options = $ar;
	}

	/**
	 * set area
	 * @access public
	 * @param string str
	 */
	function setArea($str)
	{
		$this->area = $str;
	}

	/**
	 * set searchtext
	 * @access public
	 * @param string 
	 */
	function setText($str)
	{
		$this->text = trim($str);
	}
	
} // END class user
?>