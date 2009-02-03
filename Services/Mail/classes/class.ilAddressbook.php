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
			$statement = $this->ilias->db->prepare("SELECT * FROM ".$this->table_addr." WHERE (login LIKE ? OR firstname LIKE ? OR lastname LIKE ? OR email LIKE ?) AND user_id = ?",
				array('text', 'text', 'text', 'text', 'integer')
			);

			
			$data = array( 	'%'.$a_query_str.'%', 
							'%'.$a_query_str.'%', 
							'%'.$a_query_str.'%', 
							'%'.$a_query_str.'%',
							$this->user_id			
			);

			$res = $this->ilias->db->execute($statement, $data);
		}
		else
		{
			$statement = $this->ilias->db->prepare("
				SELECT * FROM ".$this->table_addr." WHERE user_id = ?",
				array('text', 'integer')
			);

			$data = array($this->table_addr, $this->user_id);
			
			$res = $this->ilias->db->execute($statement, $data);
		
		}
	
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
		$statement = $this->ilias->db->prepareManip("
			INSERT INTO ".$this->table_addr."
			SET user_id = ?,
				login = ?,
				firstname = ?,
				lastname = ?,
				email = ?",
			array('integer', 'text', 'text', 'text', 'text')
		);
		
		$data = array($this->user_id, $a_login, $a_firstname, $a_lastname, $a_email);
		$res = $this->ilias->db->execute($statement, $data);
		
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
		$statement = $this->ilias->db->prepareManip( 
			"UPDATE ".$this->table_addr ."
			SET login = ?,
			firstname = ?,
			lastname = ?,
			email = ?
			WHERE user_id = ?
			AND addr_id = ?",
			array('text', 'text', 'text', 'text', 'integer', 'integer')
		);
		
		$data = array($a_login, $a_firstname, $a_lastname, $a_email, $this->user_id, $a_addr_id);
		
		$res = $this->ilias->db->execute($statement, $data);
		
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
		
		$data_types = array();
		$data = array();
		$query = "SELECT * FROM ".$this->table_addr." WHERE user_id = ?";
		
		array_push($data_types, 'integer');
		array_push($data, $this->user_id);

		if (trim($this->getSearchQuery()) != '')
		{
			$query .= " AND (login LIKE ? 
				OR firstname LIKE ? 
				OR lastname LIKE ? 
				OR email LIKE ?) ";
			
			array_push($data_types, 'text', 'text', 'text', 'text');
			array_push($data, 	'%'.trim($this->getSearchQuery()).'%', 
								'%'.trim($this->getSearchQuery()).'%', 
								'%'.trim($this->getSearchQuery()).'%', 
								'%'.trim($this->getSearchQuery()).'%'
			);
		}
		
		$query .= " ORDER BY login, lastname";
		
		$statement = $this->ilias->db->prepare($query, $data_types);
		$res = $this->ilias->db->execute($statement, $data);
		
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
		$statement = $this->ilias->db->prepare("
			SELECT * FROM ".$this->table_addr."
			WHERE user_id = ?
			AND addr_id = ?",
			array('integer', 'integer')
		);
		
		$data = array($this->user_id, $a_addr_id);
		$res = $this->ilias->db->execute($statement, $data);
		
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

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
		
		$statement = $this->ilias->db->prepareManip('
			DELETE FROM addressbook_mailing_lists_assignments
			WHERE addr_id = ?',
			array('integer')
		);
		
		$data = array($a_addr_id);
		$res = $this->ilias->db->execute($statement, $data);
		
		$statement = $this->ilias->db->prepareManip("
			DELETE FROM ".$this->table_addr."
			WHERE user_id = ?
			AND addr_id = ?",
			array('integer', 'integer')
		);
		
		$data = array($this->user_id, $a_addr_id);
		$res = $this->ilias->db->execute($statement, $data);
		
		
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
		
		if ($a_login != '')
		{
			$query = $ilDB->prepare("SELECT addr_id FROM ".$this->table_addr." WHERE user_id = ? AND login = ?",
			     	 	array('integer', 'text'));
			
			$result = $ilDB->execute($query, array($this->user_id, $a_login));			
			while($record = $ilDB->fetchAssoc($result))
			{
				return $record['addr_id'];
			}
		
		
		}
		
		return 0;
	}

	/* Check whether an entry with a given login name already exists */
	function checkEntryByLogin($a_login)
	{
		global $ilDB;
		
		if ($a_login != "")
		{
			$query = $ilDB->prepare("SELECT addr_id FROM ".$this->table_addr." WHERE user_id = ? AND login = ?",
			     	 	array('integer', 'text'));
			
			$result = $ilDB->execute($query, array($this->user_id, $a_login));			
			while($record = $ilDB->fetchAssoc($result))
			{
				return $record['addr_id'];
			}
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