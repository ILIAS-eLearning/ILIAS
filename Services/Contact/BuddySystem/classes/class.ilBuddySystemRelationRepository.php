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
 * Class ilBuddySystemRelationRepository
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemRelationRepository
{
    private const TYPE_APPROVED = 'app';
    private const TYPE_REQUESTED = 'req';
    private const TYPE_IGNORED = 'ign';

    protected ilDBInterface $db;
    protected int $usrId;

    public function __construct(int $usrId, ilDBInterface $db = null)
    {
        global $DIC;

        $this->db = $db ?? $DIC->database();
        $this->usrId = $usrId;
    }

    /**
     * Reads all items from database
     * @return ilBuddySystemRelation[]
     */
    public function getAll() : array
    {
        $relations = [];

        $res = $this->db->queryF(
            '
			SELECT
			       buddylist.usr_id, buddylist.buddy_usr_id, buddylist.ts, %s rel_type
            FROM buddylist
			INNER JOIN usr_data ud
                ON ud.usr_id = buddylist.usr_id
            INNER JOIN usr_data udbuddy
                ON udbuddy.usr_id = buddylist.buddy_usr_id
			WHERE buddylist.usr_id = %s
			UNION
			SELECT
			       buddylist_requests.usr_id, buddylist_requests.buddy_usr_id, buddylist_requests.ts, (CASE WHEN ignored = 1 THEN %s ELSE %s END) rel_type
			FROM buddylist_requests
			INNER JOIN usr_data ud ON ud.usr_id = buddylist_requests.usr_id
			INNER JOIN usr_data udbuddy ON udbuddy.usr_id = buddylist_requests.buddy_usr_id
			WHERE buddylist_requests.usr_id = %s OR buddylist_requests.buddy_usr_id = %s
			',
            [
                'text',
                'integer',
                'text',
                'text',
                'integer',
                'integer'
            ],
            [
                self::TYPE_APPROVED,
                $this->usrId,
                self::TYPE_IGNORED,
                self::TYPE_REQUESTED,
                $this->usrId,
                $this->usrId
            ]
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $relation = $this->getRelationByDatabaseRecord($row);
            $key = $this->usrId === $relation->getUsrId() ? $relation->getBuddyUsrId() : $relation->getUsrId();
            $relations[$key] = $relation;
        }

        return $relations;
    }

    private function getRelationByDatabaseRecord(array $row) : ilBuddySystemRelation
    {
        if (self::TYPE_APPROVED === $row['rel_type']) {
            return new ilBuddySystemRelation(
                new ilBuddySystemLinkedRelationState(),
                (int) $row['usr_id'],
                (int) $row['buddy_usr_id'],
                (int) $row['usr_id'] === $this->usrId,
                (int) $row['ts']
            );
        }

        if (self::TYPE_IGNORED === $row['rel_type']) {
            return new ilBuddySystemRelation(
                new ilBuddySystemIgnoredRequestRelationState(),
                (int) $row['usr_id'],
                (int) $row['buddy_usr_id'],
                (int) $row['usr_id'] === $this->usrId,
                (int) $row['ts']
            );
        }

        return new ilBuddySystemRelation(
            new ilBuddySystemRequestedRelationState(),
            (int) $row['usr_id'],
            (int) $row['buddy_usr_id'],
            (int) $row['usr_id'] === $this->usrId,
            (int) $row['ts']
        );
    }

    public function destroy() : void
    {
        $this->db->manipulateF(
            'DELETE FROM buddylist WHERE usr_id = %s OR buddy_usr_id = %s',
            ['integer', 'integer'],
            [$this->usrId, $this->usrId]
        );

        $this->db->manipulateF(
            'DELETE FROM buddylist_requests WHERE usr_id = %s OR buddy_usr_id = %s',
            ['integer', 'integer'],
            [$this->usrId, $this->usrId]
        );
    }

    private function addToApprovedBuddies(ilBuddySystemRelation $relation) : void
    {
        $this->db->replace(
            'buddylist',
            [
                'usr_id' => ['integer', $relation->getUsrId()],
                'buddy_usr_id' => ['integer', $relation->getBuddyUsrId()]
            ],
            [
                'ts' => ['integer', $relation->getTimestamp()]
            ]
        );

        $this->db->replace(
            'buddylist',
            [
                'usr_id' => ['integer', $relation->getBuddyUsrId()],
                'buddy_usr_id' => ['integer', $relation->getUsrId()]
            ],
            [
                'ts' => ['integer', $relation->getTimestamp()]
            ]
        );
    }

    private function removeFromApprovedBuddies(ilBuddySystemRelation $relation) : void
    {
        $this->db->manipulateF(
            'DELETE FROM buddylist WHERE usr_id = %s AND buddy_usr_id = %s',
            ['integer', 'integer'],
            [$relation->getUsrId(), $relation->getBuddyUsrId()]
        );

        $this->db->manipulateF(
            'DELETE FROM buddylist WHERE buddy_usr_id = %s AND usr_id = %s',
            ['integer', 'integer'],
            [$relation->getUsrId(), $relation->getBuddyUsrId()]
        );
    }

    private function addToRequestedBuddies(ilBuddySystemRelation $relation, bool $ignored) : void
    {
        $this->db->replace(
            'buddylist_requests',
            [
                'usr_id' => ['integer', $relation->getUsrId()],
                'buddy_usr_id' => ['integer', $relation->getBuddyUsrId()]
            ],
            [
                'ts' => ['integer', $relation->getTimestamp()],
                'ignored' => ['integer', (int) $ignored]
            ]
        );
    }

    private function removeFromRequestedBuddies(ilBuddySystemRelation $relation) : void
    {
        $this->db->manipulateF(
            'DELETE FROM buddylist_requests WHERE usr_id = %s AND buddy_usr_id = %s',
            ['integer', 'integer'],
            [$relation->getUsrId(), $relation->getBuddyUsrId()]
        );

        $this->db->manipulateF(
            'DELETE FROM buddylist_requests WHERE buddy_usr_id = %s AND usr_id = %s',
            ['integer', 'integer'],
            [$relation->getUsrId(), $relation->getBuddyUsrId()]
        );
    }

    public function save(ilBuddySystemRelation $relation) : void
    {
        $ilAtomQuery = $this->db->buildAtomQuery();
        $ilAtomQuery->addTableLock('buddylist_requests');
        $ilAtomQuery->addTableLock('buddylist');

        $ilAtomQuery->addQueryCallable(function (ilDBInterface $ilDB) use ($relation) : void {
            if ($relation->isLinked()) {
                $this->addToApprovedBuddies($relation);
            } elseif ($relation->wasLinked()) {
                $this->removeFromApprovedBuddies($relation);
            }

            if ($relation->isRequested()) {
                $this->addToRequestedBuddies($relation, false);
            } elseif ($relation->isIgnored()) {
                $this->addToRequestedBuddies($relation, true);
            } elseif ($relation->wasRequested() || $relation->wasIgnored()) {
                $this->removeFromRequestedBuddies($relation);
            }
        });

        $ilAtomQuery->run();
    }
}
