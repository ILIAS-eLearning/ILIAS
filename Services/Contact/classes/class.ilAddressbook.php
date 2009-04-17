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
		global $ilDB;

		if($a_query_str)
		{
				$res = $ilDB->queryf("SELECT * FROM ".$this->table_addr." WHERE 
				(login LIKE %s OR firstname LIKE %s OR lastname LIKE %s OR email LIKE %s) AND user_id = %s",
				array('text', 'text', 'text', 'text', 'integer'), 
				array( 	'%'.$a_query_str.'%', 
							'%'.$a_query_str.'%', 
							'%'.$a_query_str.'%', 
							'%'.$a_query_str.'%',
							$this->user_id			
			));
			
		}
		else
		{
			$res = $ilDB->queryf("
				SELECT * FROM ".$this->table_addr." WHERE user_id = %s",
				array('text', 'integer'),
				array($this->table_addr, $this->user_id));
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
		global $ilDB;
		
		$statement = $ilDB->manipulateF("
			INSERT INTO ".$this->table_addr."
			SET user_id = %s,
				login = %s,
				firstname = %s,
				lastname = %s,
				email = %s",
			array('integer', 'text', 'text', 'text', 'text'),
			array($this->user_id, $a_login, $a_firstname, $a_lastname, $a_email));
		
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
		$statement = $ilDB->manipulateF( 
			"UPDATE ".$this->table_addr ."
			SET login = %s,
			firstname = %s,
			lastname = %s,
			email = %s
			WHERE user_id = %s
			AND addr_id = %s",
			array('text', 'text', 'text', 'text', 'integer', 'integer'),
			array($a_login, $a_firstname, $a_lastname, $a_email, $this->user_id, $a_addr_id));
		
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
		$query = "SELECT * FROM ".$this->table_addr." WHERE user_id = %s";
		
		array_push($data_types, 'integer');
		array_push($data, $this->user_id);

		if (trim($this->getSearchQuery()) != '')
		{
			$query .= " AND (login LIKE %s 
				OR firstname LIKE %s 
				OR lastname LIKE %s 
				OR email LIKE %s) ";
			
			array_push($data_types, 'text', 'text', 'text', 'text');
			array_push($data, 	'%'.trim($this->getSearchQuery()).'%', 
								'%'.trim($this->getSearchQuery()).'%', 
								'%'.trim($this->getSearchQuery()).'%', 
								'%'.trim($this->getSearchQuery()).'%'
			);
		}
		
		$query .= " ORDER BY login, lastname";
		
		$res = $ilDB->queryf($query, $data_types, $data);
		
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
		
		$res = $ilDB->queryf("
			SELECT * FROM ".$this->table_addr."
			WHERE user_id = %s
			AND addr_id = %s",
			array('integer', 'integer'),
			array($this->user_id, $a_addr_id));
		
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);

		return array(
			"addr_id"    => $row->addr_id,
			"login"      => ($row->login),
			"firstname"  => ($row->firstname),
			"lastname"   => ($row->lastname),
			"email"      => ($row->email));
	}

	/**
	* returns a readable string representation of a given entry
	* @param integer address_id
	* @return string formated string
	* @access public
	*/
	function entryToString($a_addr_id)
	{
		$entry = $this->getEntry($a_addr_id);
		if (!$entry)
			return "???";
		else
		{
			$out = "";
			if ($entry['firstname'] && $entry['lastname'])
				$out .= $entry['lastname'] . ', ' . $entry['firstname'] . ' ';
			else if ($entry['firstname'])
				$out .= $entry['firstname'] . ' ';
			else if ($entry['lastname'])
				$out .= $entry['lastname'] . ' ';
			
			if ($entry['login'])
				$out .= '(' . $entry['login'] . ') ';
			
			if ($entry['email'])
				$out .= '[' . $entry['email'] . ']';
			return $out;
		}
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
		
		$statement = $ilDB->manipulateF('
			DELETE FROM addressbook_mlist_ass
			WHERE addr_id = %s',
			array('integer'), array($a_addr_id));
		
		$statement = $ilDB->manipulateF("
			DELETE FROM ".$this->table_addr."
			WHERE user_id = %s
			AND addr_id = %s",
			array('integer', 'integer'),
			array($this->user_id, $a_addr_id));

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
			$result = $ilDB->queryf("SELECT addr_id FROM ".$this->table_addr." WHERE user_id = %s AND login = %s",
			     	 	array('integer', 'text'), array($this->user_id, $a_login));
	
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
			$result = $ilDB->queryf("SELECT addr_id FROM ".$this->table_addr." WHERE user_id = %s AND login = %s",
			     	 	array('integer', 'text'), array($this->user_id, $a_login));
			
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