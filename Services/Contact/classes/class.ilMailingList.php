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
	
	const MODE_ADDRESSBOOK = 1;
	const MODE_TEMPORARY = 2;
	
	public function __construct(ilObjUser $user, $id = 0)
	{
		global $ilDB;

		$this->db = $ilDB;
		$this->user = $user;
		
		$this->mail_id = $id;
		$this->user_id = $this->user->getId();
		
		$this->setMode(self::MODE_ADDRESSBOOK);
	
		$this->read();
	}
	
	public function insert()
	{
		$nextId = $this->db->nextId('addressbook_mlist');
		$statement = $this->db->manipulateF('
			INSERT INTO addressbook_mlist 
			(   ml_id,
				user_id,
				title,
				description,
				createdate,
				changedate,
				lmode
			)
			VALUES(%s, %s, %s, %s, %s, %s, %s)',
			array(	'integer',
					'integer',
					'text', 
					'text', 
					'timestamp',
					'timestamp',
					'integer'),
			array(	$nextId,  
					$this->getUserId(), 
					$this->getTitle(), 
					$this->getDescription(), 
					$this->getCreatedate(), 
					NULL,
					$this->getMode()
		));
		
		$this->mail_id = $nextId;
		
		return true;
	}
	
	public function update()
	{
		if ($this->mail_id && $this->user_id)
		{
			$statement = $this->db->manipulateF('
				UPDATE addressbook_mlist
				SET title = %s,
					description = %s,
					changedate =  %s,
					lmode = %s
				WHERE ml_id =  %s
				AND user_id =  %s',
				array(	'text',
						'text',
						'timestamp',
						'integer',
						'integer',
						'integer'
				),
				array(	$this->getTitle(),
							$this->getDescription(),
							$this->getChangedate(),
							$this->getMode(),
							$this->getId(),
							$this->getUserId()
			));
			
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

			$statement = $this->db->manipulateF('
				DELETE FROM addressbook_mlist
				WHERE ml_id = %s
				AND user_id = %s',
				array('integer', 'integer'),
				array($this->getId(), $this->getUserId()));
			
			return true;
		}
		else
		{
			return false;
		}		
	}
	
	private function read()
	{
		if ($this->getId() && $this->getUserId())
		{
			$res = $this->db->queryf('
				SELECT * FROM addressbook_mlist 
				WHERE ml_id = %s
				AND user_id =%s',
				array('integer', 'integer'),
				array($this->getId(), $this->getUserId())); 
	
			$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
	
			if (is_object($row))
			{
				$this->setId($row->ml_id);
				$this->setUserId($row->user_id);
				$this->setTitle($row->title);
				$this->setDescription($row->description);
				$this->setCreatedate($row->createdate);
				$this->setChangedate($row->changedae);		
				$this->setMode($row->lmode);
			}
		}		
		
		
		return true;
	}
	
	public function getAssignedEntries()
	{
		if($this->getMode() == self::MODE_ADDRESSBOOK)
		{
			$res = $this->db->queryf('
				SELECT * FROM addressbook_mlist_ass 
				INNER JOIN addressbook ON addressbook.addr_id = addressbook_mlist_ass.addr_id 
				WHERE ml_id = %s',
				array('integer'),
				array($this->getId()));
		}
		else
		{
			$res = $this->db->queryf('
				SELECT * FROM addressbook_mlist_ass 
				INNER JOIN usr_data ON addressbook_mlist_ass.addr_id = usr_data.usr_id 
				WHERE ml_id = %s',
				array('integer'),
				array($this->getId()));
		}
		
		$entries = array();
		
		$counter = 0;
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
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
		$nextId = $this->db->nextId('addressbook_mlist_ass');
		$statement = $this->db->manipulateF('
			INSERT INTO addressbook_mlist_ass 
			( 	a_id, 
				ml_id,
				addr_id
			)
			VALUES(%s,%s,%s )',
			array('integer', 'integer', 'integer'),
			array($nextId, $this->getId(), $addr_id));
		
		return true;
	}
	
	public function deassignAddressbookEntry($a_id = 0)	
	{
	
		$statement = $this->db->manipulateF('	
		DELETE FROM addressbook_mlist_ass 
			WHERE a_id = %s',
			array('integer'),
			array($a_id));
		
		return true;
	}
	
	public function deassignAllEntries()	
	{
		$statement = $this->db->manipulateF('	
		DELETE FROM addressbook_mlist_ass 
				WHERE ml_id = %s',
				array('integer'),
				array($this->getId()));
			
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
		if($a_changedate == '0000-00-00 00:00:00')
		$this->changedate = NULL;
		else
		$this->changedate = $a_changedate;
	}
	public function getChangedate()
	{
		return $this->changedate;
	}
	
	public static function _isOwner($a_ml_id, $a_usr_id)
	{
		global $ilDB;
		
		$res = $ilDB->queryf('
			SELECT * FROM addressbook_mlist 
			WHERE ml_id = %s
			AND user_id =%s',
			array('integer', 'integer'),
			array($a_ml_id, $a_usr_id));
			
		$row = $ilDB->fetchObject($res);
		
		return is_object($row) ? true : false;
	}
	
	public function setMode($a_mode)
	{
		$a_mode = (int)$a_mode;
		if(in_array($a_mode, array(self::MODE_ADDRESSBOOK, self::MODE_TEMPORARY)))
		{
			$this->mode = (int)$a_mode;
		}
	}
	
	public function getMode()
	{
		return $this->mode;
	}	
}
?>
