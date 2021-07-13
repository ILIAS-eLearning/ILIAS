<?php declare(strict_types=1);
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
    private int $mail_id;
    private int $user_id;
    private string $title = '';
    private string $description = '';
    private string $createdate;
    private string $changedate;

    private ilDBInterface $db;
    
    public const MODE_ADDRESSBOOK = 1;
    public const MODE_TEMPORARY = 2;
    private int $mode;

    public function __construct(ilObjUser $user, int $id = 0)
    {
        global $DIC;

        $this->db = $DIC['ilDB'];

        $this->mail_id = $id;
        $this->user_id = $user->getId();
        
        $this->setMode(self::MODE_ADDRESSBOOK);
    
        $this->read();
    }
    
    public function insert(): bool
    {
        $nextId = $this->db->nextId('addressbook_mlist');
        $statement = $this->db->manipulateF(
            '
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
                    null,
                    $this->getMode()
        )
        );
        
        $this->mail_id = $nextId;
        
        return true;
    }
    
    public function update(): bool
    {
        if ($this->mail_id && $this->user_id) {
            $statement = $this->db->manipulateF(
                '
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
            )
            );
            
            return true;
        }

        return false;
    }
    
    public function delete(): bool
    {
        if ($this->mail_id && $this->user_id) {
            $this->deassignAllEntries();

            $statement = $this->db->manipulateF(
                '
				DELETE FROM addressbook_mlist
				WHERE ml_id = %s
				AND user_id = %s',
                array('integer', 'integer'),
                array($this->getId(), $this->getUserId())
            );
            
            return true;
        }

        return false;
    }
    
    private function read(): void
    {
        if ($this->getId() && $this->getUserId()) {
            $res = $this->db->queryf(
                '
				SELECT * FROM addressbook_mlist 
				WHERE ml_id = %s
				AND user_id =%s',
                array('integer', 'integer'),
                array($this->getId(), $this->getUserId())
            );
    
            $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
    
            if (is_object($row)) {
                $this->setId($row->ml_id);
                $this->setUserId($row->user_id);
                $this->setTitle($row->title);
                $this->setDescription($row->description);
                $this->setCreatedate($row->createdate);
                $this->setChangedate($row->changedae);
                $this->setMode($row->lmode);
            }
        }
    }

    public function getAssignedEntries(): array
    {
        $res = $this->db->queryf(
            'SELECT a_id, usr_data.usr_id FROM addressbook_mlist_ass INNER JOIN usr_data ON usr_data.usr_id = addressbook_mlist_ass.usr_id WHERE ml_id = %s',
            array('integer'),
            array($this->getId())
        );

        $entries = array();
        $counter = 0;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $entries[$row->a_id] = array(
                'a_id' => $row->a_id,
                'usr_id' => $row->usr_id
            );
        }
        
        return $entries;
    }

    /**
     * @param int $usr_id
     * @return bool
     */
    public function assignUser(int $usr_id = 0): bool
    {
        $nextId = $this->db->nextId('addressbook_mlist_ass');
        $this->db->manipulateF(
            'INSERT INTO addressbook_mlist_ass (a_id, ml_id, usr_id) VALUES(%s, %s, %s)',
            array('integer', 'integer', 'integer'),
            array($nextId, $this->getId(), $usr_id)
        );
        return true;
    }

    /**
     * @param int $a_id
     * @return bool
     */
    public function deleteEntry(int $a_id = 0): bool
    {
        $this->db->manipulateF(
            'DELETE FROM addressbook_mlist_ass WHERE a_id = %s',
            array('integer'),
            array($a_id)
        );
        return true;
    }

    /**
     * @return bool
     */
    public function deassignAllEntries(): bool
    {
        $this->db->manipulateF(
            'DELETE FROM addressbook_mlist_ass WHERE ml_id = %s',
            array('integer'),
            array($this->getId())
        );
        return true;
    }
    
    public function setId(int $a_mail_id = 0): void
    {
        $this->mail_id = $a_mail_id;
    }
    public function getId(): int
    {
        return $this->mail_id;
    }
    public function setUserId(int $a_user_id = 0): void
    {
        $this->user_id = $a_user_id;
    }
    public function getUserId(): int
    {
        return $this->user_id;
    }
    public function setTitle(string $a_title = ''): void
    {
        $this->title = $a_title;
    }
    public function getTitle(): string
    {
        return $this->title;
    }
    public function setDescription(string $a_description = ''): void
    {
        $this->description = $a_description;
    }
    public function getDescription(): string
    {
        return $this->description;
    }
    public function setCreatedate(string $_createdate): void
    {
        $this->createdate = $_createdate;
    }
    public function getCreatedate(): string
    {
        return $this->createdate;
    }
    public function setChangedate(string $a_changedate): void
    {
        $this->changedate = $a_changedate;
    }
    public function getChangedate(): string
    {
        return $this->changedate;
    }
    
    public static function _isOwner(int $a_ml_id, int $a_usr_id): bool
    {
        /** @var $ilDB ilDBInterface */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryf(
            'SELECT * FROM addressbook_mlist WHERE ml_id = %s AND user_id =%s',
            array('integer', 'integer'),
            array($a_ml_id, $a_usr_id)
        );
        $row = $ilDB->fetchObject($res);
        
        return is_object($row) ? true : false;
    }
    
    public function setMode(int $a_mode): void
    {
        if (in_array($a_mode, array(self::MODE_ADDRESSBOOK, self::MODE_TEMPORARY))) {
            $this->mode = $a_mode;
        }
    }
    
    public function getMode(): int
    {
        return $this->mode;
    }

    /**
     * @param int $usr_id
     */
    public static function removeAssignmentsByUserId(int $usr_id): void
    {
        /** @var $ilDB ilDBInterface */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ilDB->manipulate('DELETE FROM addressbook_mlist_ass WHERE usr_id = ' . $ilDB->quote($usr_id, 'integer'));
    }
}
