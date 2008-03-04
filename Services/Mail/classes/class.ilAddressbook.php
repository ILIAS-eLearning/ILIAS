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
* Mail Box class
* Base class for creating and handling mail boxes
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
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
				"WHERE (login LIKE '%".addslashes($a_query_str)."%' ".
				"OR firstname LIKE '%".addslashes($a_query_str)."%' ".
				"OR lastname LIKE '%".addslashes($a_query_str)."%' ".
				"OR email LIKE '%".addslashes($a_query_str)."%') ".
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
				"login"      => ($row->login),
				"firstname"  => ($row->firstname),
				"lastname"   => ($row->lastname),
				"email"      => ($row->email));
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
		global $ilDB;
		
		$query = "INSERT INTO $this->table_addr ".
			"SET user_id = ".$ilDB->quote($this->user_id).",".
			"login = ".$ilDB->quote($a_login).",".
			"firstname = ".$ilDB->quote($a_firstname).",".
			"lastname = ".$ilDB->quote($a_lastname).",".
			"email = ".$ilDB->quote($a_email)."";

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
		global $ilDB;
		
		$query = "UPDATE $this->table_addr ".
			"SET login = ".$ilDB->quote($a_login).",".
			"firstname = ".$ilDB->quote($a_firstname).",".
			"lastname = ".$ilDB->quote($a_lastname).",".
			"email = ".$ilDB->quote($a_email)." ".
			"WHERE user_id = ".$ilDB->quote($this->user_id)." ".
			"AND addr_id = ".$ilDB->quote($a_addr_id)."";

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
		global $ilDB;
		
		$query = "SELECT * FROM $this->table_addr ".
			"WHERE user_id = ".$ilDB->quote($this->user_id)." ";
		
		if (trim($this->getSearchQuery()) != '')
		{
		$query .= " AND (login LIKE '%".addslashes(trim($this->getSearchQuery()))."%' ".
				"OR firstname LIKE '%".addslashes(trim($this->getSearchQuery()))."%' ".
				"OR lastname LIKE '%".addslashes(trim($this->getSearchQuery()))."%' ".
				"OR email LIKE '%".addslashes(trim($this->getSearchQuery()))."%') ";
		}
		
		$query .= " ORDER BY login,lastname";

		$res = $this->ilias->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$entries[] = array(
				"addr_id"    => $row->addr_id,
				"login"      => ($row->login),
				"firstname"  => ($row->firstname),
				"lastname"   => ($row->lastname),
				"email"      => ($row->email));
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
		global $ilDB;
		
		$query = "SELECT * FROM $this->table_addr ".
			"WHERE user_id = ".$ilDB->quote($this->user_id)." ".
			"AND addr_id = ".$ilDB->quote($a_addr_id)." ";

		$row = $this->ilias->db->getRow($query,DB_FETCHMODE_OBJECT);

		return array(
			"addr_id"    => $row->addr_id,
			"login"      => ($row->login),
			"firstname"  => ($row->firstname),
			"lastname"   => ($row->lastname),
			"email"      => ($row->email));
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
		global $ilDB;
		
		$query = "DELETE FROM addressbook_mailing_lists_assignments ".
				 "WHERE addr_id = ".$ilDB->quote($a_addr_id)." ";
		$this->ilias->db->query($query);
		
		$query = "DELETE FROM $this->table_addr ".
			"WHERE user_id = ".$ilDB->quote($this->user_id)." ".
			"AND addr_id = ".$ilDB->quote($a_addr_id)." ";
		$res = $this->ilias->db->query($query);

		return true;
	}

	/**
	* Check whether an entry with a given login name already exists
	* @param string login name
	* @return int number of entries found
	* @access	public
	*/
	function checkEntry($a_login)
	{
		global $ilDB;
		
		if ($a_login != "")
		{
			$query = "SELECT addr_id FROM $this->table_addr ".
					 "WHERE user_id = ".$ilDB->quote($this->user_id)." 
                      AND login = ".$ilDB->quote($a_login)." ";
			return $this->ilias->db->getOne($query);
		}
		
		return 0;
	}

	/* Check whether an entry with a given login name already exists */
	function checkEntryByLogin($a_login)
	{
		global $ilDB;
		
		if ($a_login != "")
		{
			$query = "SELECT addr_id FROM $this->table_addr ".
				"WHERE user_id = ".$ilDB->quote($this->user_id)." ".
				"AND login = ".$ilDB->quote($a_login)." ";
			return $this->ilias->db->getOne($query);
		}
		
		return 0;
	}
	
	public function setSearchQuery($search_query = '')
	{
		$this->search_query = $search_query;
	}
	public function getSearchQuery()
	{
		return $this->search_query;
	}
}
?>