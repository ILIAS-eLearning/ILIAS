<?php
/**
* Mail Box class
* Base class for creating and handling mail boxes
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias-core
*/

class Addressbook
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
	* @param $a_user_id    user_id of mailbox
	* @access	public
	*/
	function Addressbook($a_user_id = 0)
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
				"WHERE login LIKE '%".$a_search_str."%' ".
				"OR firstname LIKE '%".$a_search_str."%' ".
				"OR lastname LIKE '%".$a_search_str."%' ".
				"OR email LIKE '%".$a_search_str."%'";
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
				"login"      => $row->login,
				"firstname"  => $row->firstname,
				"lastname"   => $row->lastname,
				"email"      => $row->email);
		}
		return $entries ? $entries : array();
	}
	/**
	* get all entries the user
	* @return array array of entries found in addressbook
	* @access	public
	*/
	function getEntries()
	{
		$query = "SELECT * FROM $this->table_addr ".
			"WHERE user_id = '".$this->user_id."'";

		$res = $this->ilias->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$entries[] = array(
				"addr_id"    => $row->addr_id,
				"login"      => $row->login,
				"firstname"  => $row->firstname,
				"lastname"   => $row->lastname,
				"email"      => $row->email);
		}
		return $entries ? $entries : array();
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
	* @param integer entry id
	* @return boolean
	* @access	public
	*/
	function deleteEntry($a_entry_id)
	{
		$query = "DELETE FROM $this->table_addr ".
			"WHERE user_id = '".$this->user_id."' ".
			"AND entry_id = '".$a_entry_id."'";
		$res = $this->ilias->db->query($query);

		return true;
	}
}
?>