<?php
/**
* Mail Box class
* Base class for creating and handling mail boxes
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias-mail
*/

class ilAddressbook
{
	/**
	* ilias object
	* @var object ilias
	* @access private
	*/
	var $ilias;

	/**
	* lng object
	* @var		object language
	* @access	private
	*/
	var $lng;

	/**
	* user_id
	* @var int user_id
	* @access private
	*/
	var $user_id;

	/**
	* table name of tree table
	* @var string
	* @access private
	*/
	var $table_addr;

	/**
	* Constructor
	* @param integer user_id of mailbox
	* @access	public
	*/
	function ilAddressbook($a_user_id = 0)
	{
		global $ilias,$lng;

		$this->ilias = &$ilias;
		$this->lng = &$lng;
		$this->user_id = $a_user_id;

		$this->table_addr = 'addressbook';
	}
	/**
	* Search users in addressbook
	* @param string query string
	* @return array array of entries found in addressbook
	* @access	public
	*/
	function searchUsers($a_query_str)
	{
		if($a_query_str)
		{
			$query = "SELECT * FROM $this->table_addr ".
				"WHERE (login LIKE '%".$a_query_str."%' ".
				"OR firstname LIKE '%".$a_query_str."%' ".
				"OR lastname LIKE '%".$a_query_str."%' ".
				"OR email LIKE '%".$a_query_str."%') ";
				"AND user_id = '".$this->user_id."'";
		}
		else
		{
			$query = "SELECT * FROM $this->table_addr ".
				"WHERE user_id = '".$this->user_id."'";
		}
		$res = $this->ilias->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$entries[] = array(
				"login"      => stripslashes($row->login),
				"firstname"  => stripslashes($row->firstname),
				"lastname"   => stripslashes($row->lastname),
				"email"      => stripslashes($row->email));
		}
		return $entries ? $entries : array();
	}
	/**
	* add entry
	* @param string login
	* @param string firstname
	* @param string lastname
	* @param string email 
	* @return boolean
	* @access	public
	*/
	function addEntry($a_login,$a_firstname,$a_lastname,$a_email)
	{
		$query = "INSERT INTO $this->table_addr ".
			"SET user_id = '".$this->user_id."',".
			"login = '".addslashes($a_login)."',".
			"firstname = '".addslashes($a_firstname)."',".
			"lastname = '".addslashes($a_lastname)."',".
			"email = '".addslashes($a_email)."'";

		$res = $this->ilias->db->query($query);

		return true;
	}

	/**
	* update entry
	* @param integer addr_id
	* @param string login
	* @param string firstname
	* @param string lastname
	* @param string email 
	* @return boolean
	* @access	public
	*/
	function updateEntry($a_addr_id,$a_login,$a_firstname,$a_lastname,$a_email)
	{
		$query = "UPDATE $this->table_addr ".
			"SET login = '".addslashes($a_login)."',".
			"firstname = '".addslashes($a_firstname)."',".
			"lastname = '".addslashes($a_lastname)."',".
			"email = '".addslashes($a_email)."' ".
			"WHERE user_id = '".$this->user_id."' ".
			"AND addr_id = '".$a_addr_id."'";

		$res = $this->ilias->db->query($query);

		return true;
	}

	/**
	* get all entries the user
	* @return array array of entries found in addressbook
	* @access	public
	*/
	function getEntries()
	{
		$query = "SELECT * FROM $this->table_addr ".
			"WHERE user_id = '".$this->user_id."' ".
			"ORDER BY login,lastname";

		$res = $this->ilias->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$entries[] = array(
				"addr_id"    => $row->addr_id,
				"login"      => stripslashes($row->login),
				"firstname"  => stripslashes($row->firstname),
				"lastname"   => stripslashes($row->lastname),
				"email"      => stripslashes($row->email));
		}
		return $entries ? $entries : array();
	}
	/**
	* get all entries the user
	* @param integer address id
	* @return array array of entry data
	* @access	public
	*/
	function getEntry($a_addr_id)
	{
		$query = "SELECT * FROM $this->table_addr ".
			"WHERE user_id = '".$this->user_id."' ".
			"AND addr_id = '".$a_addr_id."'";

		$row = $this->ilias->db->getRow($query,DB_FETCHMODE_OBJECT);

		return array(
			"addr_id"    => $row->addr_id,
			"login"      => stripslashes($row->login),
			"firstname"  => stripslashes($row->firstname),
			"lastname"   => stripslashes($row->lastname),
			"email"      => stripslashes($row->email));
	}

	/**
	* delete some entries of user
	* @param array array of entry ids
	* @return boolean
	* @access	public
	*/
	function deleteEntries($a_entries)
	{
		if(is_array($a_entries))
		{
			foreach($a_entries as $entry)
			{
				$this->deleteEntry($entry);
			}
		}
		return true;
	}
	/**
	* delete one entry
	* @param integer addr id
	* @return boolean
	* @access	public
	*/
	function deleteEntry($a_addr_id)
	{
		$query = "DELETE FROM $this->table_addr ".
			"WHERE user_id = '".$this->user_id."' ".
			"AND addr_id = '".$a_addr_id."'";
		$res = $this->ilias->db->query($query);

		return true;
	}
}
?>