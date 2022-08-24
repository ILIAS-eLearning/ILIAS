<?php

declare(strict_types=1);

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
class ilMailingLists
{
    private ilDBInterface $db;
    private ilObjUser $user;
    private ?ilMailingList $ml = null;

    public function __construct(ilObjUser $a_user)
    {
        global $DIC;

        $this->db = $DIC['ilDB'];
        $this->user = $a_user;
    }

    public function isOwner(int $a_ml_id, int $a_usr_id): bool
    {
        $res = $this->db->queryF(
            'SELECT EXISTS(SELECT 1 FROM addressbook_mlist WHERE ml_id = %s AND user_id = %s) cnt',
            ['integer', 'integer'],
            [$a_ml_id, $a_usr_id]
        );
        $row = $this->db->fetchAssoc($res);

        return is_array($row) && (int) $row['cnt'] === 1;
    }

    public function get(int $id = 0): ilMailingList
    {
        return new ilMailingList($this->user, $id);
    }

    /**
     * @param int[] $a_ids
     * @return ilMailingList[]
     */
    public function getSelected(array $a_ids = []): array
    {
        $entries = [];

        foreach ($a_ids as $id) {
            $entries[] = new ilMailingList($this->user, (int) $id);
        }

        return $entries;
    }

    public function hasAny(): bool
    {
        $res = $this->db->queryF(
            'SELECT EXISTS(SELECT 1 FROM addressbook_mlist WHERE user_id = %s) cnt',
            ['integer'],
            [$this->user->getId()]
        );
        $row = $this->db->fetchAssoc($res);

        return (is_array($row) && (int) $row['cnt'] === 1);
    }

    /**
     * @return ilMailingList[]
     */
    public function getAll(): array
    {
        $res = $this->db->queryF(
            'SELECT * FROM addressbook_mlist WHERE user_id = %s',
            ['integer'],
            [$this->user->getId()]
        );

        $entries = [];

        $counter = 0;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $tmpObj = new ilMailingList($this->user, 0);
            $tmpObj->setId((int) $row->ml_id);
            $tmpObj->setUserId((int) $row->user_id);
            $tmpObj->setTitle($row->title);
            $tmpObj->setDescription($row->description);
            $tmpObj->setCreatedate($row->createdate);
            $tmpObj->setChangedate($row->changedate);
            $tmpObj->setMode((int) $row->lmode);

            $entries[$counter] = $tmpObj;

            unset($tmpObj);

            ++$counter;
        }

        return $entries;
    }

    public function mailingListExists(string $a_list_name): bool
    {
        $ml_id = (int) substr($a_list_name, strrpos($a_list_name, '_') + 1);
        if (!is_numeric($ml_id) || $ml_id <= 0) {
            return false;
        }

        $this->setCurrentMailingList($ml_id);

        return true;
    }

    public function setCurrentMailingList(int $id = 0): void
    {
        $this->ml = $this->get($id);
    }


    public function getCurrentMailingList(): ?ilMailingList
    {
        return $this->ml;
    }

    public function deleteTemporaryLists(): void
    {
        foreach ($this->getAll() as $mlist) {
            if ($mlist->getMode() === ilMailingList::MODE_TEMPORARY) {
                $mlist->delete();
            }
        }
    }

    public function deleteAssignments(): void
    {
        $this->db->manipulate(
            'DELETE FROM addressbook_mlist_ass WHERE usr_id = ' . $this->db->quote($this->user->getId(), 'integer')
        );
    }
}
