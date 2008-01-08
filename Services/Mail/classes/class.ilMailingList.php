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
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
*
* @ingroup ServicesMail
*/
class ilMailingList
{
	private $mail_id = 0;
	private $user_id = 0;
	private $title = '';
	private $description = '';
	private $createdate = '0000-00-00 00:00:00';
	private $changedate = '0000-00-00 00:00:00';
	
	private $user = null;	
	private $db = null;
	
	public function __construct(ilObjUser $user, $id = 0)
	{
		global $ilDB;

		$this->db = $ilDB;
		$this->user = $user;
		
		$this->mail_id = $id;
		$this->user_id = $this->user->getId();
		
	
		$this->read();
	}
	
	public function insert()
	{
		$query = "INSERT INTO addressbook_mailing_lists "
				."(ml_id, user_id, title, description, createdate, changedate) "
				."VALUES ( "
				."'', "
				.$this->db->quote($this->user_id).", "
				.$this->db->quote($this->title).", "
				.$this->db->quote($this->description).", "
				.$this->db->quote($this->createdate).", "
				."'' "
				.") ";
		$this->db->query($query);	
		
		$this->mail_id = $this->db->getLastInsertId();
		
		return true;
	}
	
	public function update()
	{
		if ($this->mail_id && $this->user_id)
		{
			$query = "UPDATE addressbook_mailing_lists "
					."SET "				
					."title = " . $this->db->quote($this->title) . ", "
					."description = " . $this->db->quote($this->description) . ", "
					."changedate = " . $this->db->quote($this->changedate) . " "
					."WHERE 1 "
					."AND ml_id = " . $this->db->quote($this->mail_id) . " "
					."AND user_id = " . $this->db->quote($this->user_id). " ";
					
			$this->db->query($query);
			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function delete()
	{
		if ($this->mail_id && $this->user_id)
		{
			$this->deassignAllEntries();
			
			$query = "DELETE FROM addressbook_mailing_lists "
					."WHERE 1 "
					."AND ml_id = " . $this->db->quote($this->mail_id) . " "
					."AND user_id = " . $this->db->quote($this->user_id) . " ";
			
			$this->db->query($query);
			
			return true;
		}
		else
		{
			return false;
		}		
	}
	
	private function read()
	{		
		if ($this->mail_id && $this->user_id)
		{
			$query = "SELECT * FROM addressbook_mailing_lists "
					."WHERE 1 "
					."AND ml_id = " . $this->db->quote($this->mail_id) . " "
					."AND user_id = " . $this->db->quote($this->user_id) . " ";
	
			$row = $this->db->getRow($query);
	
			if (is_object($row))
			{
				$this->mail_id = $row->ml_id;
				$this->user_id = $row->user_id;
				$this->title = $row->title;
				$this->description = $row->description;
				$this->createdate = $row->createdate;
				$this->changedate = $row->changedae;		
			}
		}
		
		return true;
	}
	
	public function getAssignedEntries()
	{
		$query = "SELECT * FROM addressbook_mailing_lists_assignments "
				."INNER JOIN addressbook ON addressbook.addr_id = addressbook_mailing_lists_assignments.addr_id "
				."WHERE 1 "
				."AND ml_id = " . $this->db->quote($this->mail_id) . " ";
		$res = $this->db->query($query);

		$entries = array();
		
		$counter = 0;
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{			
			$entries[$counter] = array('a_id' => $row->a_id, 
									   'addr_id' => $row->addr_id,
									   'login' => $row->login,
									   'email' => $row->email,
									   'firstname' => $row->firstname,
									   'lastname' => $row->lastname									   
									   );
			
			++$counter;
		}
		
		return $entries;
	}
	
	public function assignAddressbookEntry($addr_id = 0)	
	{
		$query = "INSERT INTO addressbook_mailing_lists_assignments "
				."(a_id, ml_id, addr_id) "
				."VALUES ( "
				."'', "
				.$this->db->quote($this->mail_id).", "
				.$this->db->quote($addr_id)." "
				.") ";
		$this->db->query($query);
		
		return true;
	}
	
	public function deassignAddressbookEntry($a_id = 0)	
	{
		$query = "DELETE FROM addressbook_mailing_lists_assignments "
				."WHERE 1 "
				."AND a_id = " . $this->db->quote($a_id) . " ";
		
		$this->db->query($query);
		
		return true;
	}
	
	public function deassignAllEntries()	
	{
		$query = "DELETE FROM addressbook_mailing_lists_assignments "
				."WHERE 1 "
				."AND ml_id = " . $this->db->quote($this->mail_id) . " ";
		
		$this->db->query($query);
		
		return true;
	}
	
	public function setId($a_mail_id = 0)
	{
		$this->mail_id = $a_mail_id;
	}
	public function getId()
	{
		return $this->mail_id;
	}
	public function setUserId($a_user_id = 0)
	{
		$this->user_id = $a_user_id;
	}
	public function getUserId()
	{
		return $this->user_id;
	}
	public function setTitle($a_title = '')
	{
		$this->title = $a_title;
	}
	public function getTitle()
	{
		return $this->title;
	}
	public function setDescription($a_description = '')
	{
		$this->description = $a_description;
	}
	public function getDescription()
	{
		return $this->description;
	}
	public function setCreatedate($_createdate = '0000-00-00 00:00:00')
	{
		$this->createdate = $_createdate;
	}
	public function getCreatedate()
	{
		return $this->createdate;
	}
	public function setChangedate($a_changedate = '0000-00-00 00:00:00')
	{
		$this->changedate = $a_changedate;
	}
	public function getChangedate()
	{
		return $this->changedate;
	}
}
?>
