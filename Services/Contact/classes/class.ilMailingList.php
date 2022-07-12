<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * @author Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesMail
 */
class ilMailingList
{
    private int $mail_id;
    private int $user_id;
    private string $title = '';
    private string $description = '';
    private string $createdate;
    private ?string $changedate;

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

    public function insert() : bool
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
            [
                'integer',
                'integer',
                'text',
                'text',
                'timestamp',
                'timestamp',
                'integer'
            ],
            [
                $nextId,
                $this->getUserId(),
                $this->getTitle(),
                $this->getDescription(),
                $this->getCreatedate(),
                null,
                $this->getMode()
            ]
        );

        $this->mail_id = $nextId;

        return true;
    }

    public function update() : bool
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
                [
                    'text',
                    'text',
                    'timestamp',
                    'integer',
                    'integer',
                    'integer'
                ],
                [
                    $this->getTitle(),
                    $this->getDescription(),
                    $this->getChangedate(),
                    $this->getMode(),
                    $this->getId(),
                    $this->getUserId()
                ]
            );

            return true;
        }

        return false;
    }

    public function delete() : bool
    {
        if ($this->mail_id && $this->user_id) {
            $this->deassignAllEntries();

            $statement = $this->db->manipulateF(
                'DELETE FROM addressbook_mlist WHERE ml_id = %s AND user_id = %s',
                ['integer', 'integer'],
                [$this->getId(), $this->getUserId()]
            );

            return true;
        }

        return false;
    }

    private function read() : void
    {
        if ($this->getId() && $this->getUserId()) {
            $res = $this->db->queryF(
                'SELECT * FROM addressbook_mlist WHERE ml_id = %s AND user_id =%s',
                ['integer', 'integer'],
                [$this->getId(), $this->getUserId()]
            );

            $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

            if (is_object($row)) {
                $this->setId((int) $row->ml_id);
                $this->setUserId((int) $row->user_id);
                $this->setTitle($row->title);
                $this->setDescription($row->description);
                $this->setCreatedate($row->createdate);
                $this->setChangedate($row->changedate);
                $this->setMode((int) $row->lmode);
            }
        }
    }

    /**
     * @return array<int, array{a_id: int, usr_id: int}>
     */
    public function getAssignedEntries() : array
    {
        $res = $this->db->queryF(
            'SELECT a_id, usr_data.usr_id FROM addressbook_mlist_ass ' .
            'INNER JOIN usr_data ON usr_data.usr_id = addressbook_mlist_ass.usr_id WHERE ml_id = %s',
            ['integer'],
            [$this->getId()]
        );

        $entries = [];
        $counter = 0;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $entries[(int) $row->a_id] = [
                'a_id' => (int) $row->a_id,
                'usr_id' => (int) $row->usr_id
            ];
        }

        return $entries;
    }


    public function assignUser(int $usr_id = 0) : bool
    {
        $nextId = $this->db->nextId('addressbook_mlist_ass');
        $this->db->manipulateF(
            'INSERT INTO addressbook_mlist_ass (a_id, ml_id, usr_id) VALUES(%s, %s, %s)',
            ['integer', 'integer', 'integer'],
            [$nextId, $this->getId(), $usr_id]
        );
        return true;
    }


    public function deleteEntry(int $a_id = 0) : bool
    {
        $this->db->manipulateF(
            'DELETE FROM addressbook_mlist_ass WHERE a_id = %s',
            ['integer'],
            [$a_id]
        );
        return true;
    }


    public function deassignAllEntries() : bool
    {
        $this->db->manipulateF(
            'DELETE FROM addressbook_mlist_ass WHERE ml_id = %s',
            ['integer'],
            [$this->getId()]
        );
        return true;
    }

    public function setId(int $a_mail_id = 0) : void
    {
        $this->mail_id = $a_mail_id;
    }

    public function getId() : int
    {
        return $this->mail_id;
    }

    public function setUserId(int $a_user_id = 0) : void
    {
        $this->user_id = $a_user_id;
    }

    public function getUserId() : int
    {
        return $this->user_id;
    }

    public function setTitle(string $a_title = '') : void
    {
        $this->title = $a_title;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function setDescription(string $a_description = '') : void
    {
        $this->description = $a_description;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function setCreatedate(string $_createdate) : void
    {
        $this->createdate = $_createdate;
    }

    public function getCreatedate() : string
    {
        return $this->createdate;
    }

    public function setChangedate(?string $a_changedate) : void
    {
        $this->changedate = $a_changedate;
    }

    public function getChangedate() : ?string
    {
        return $this->changedate;
    }

    public function setMode(int $a_mode) : void
    {
        if (in_array($a_mode, [self::MODE_ADDRESSBOOK, self::MODE_TEMPORARY], true)) {
            $this->mode = $a_mode;
        }
    }

    public function getMode() : int
    {
        return $this->mode;
    }
}
