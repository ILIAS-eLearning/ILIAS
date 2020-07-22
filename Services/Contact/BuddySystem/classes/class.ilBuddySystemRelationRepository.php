<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilBuddySystemRelationRepository
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemRelationRepository
{
    const TYPE_APPROVED = 'app';
    const TYPE_REQUESTED = 'req';
    const TYPE_IGNORED = 'ign';

    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * @var int
     */
    protected $usr_id;

    /**
     * @var int $usr_id
     */
    public function __construct($usr_id)
    {
        global $DIC;

        $this->db = $DIC['ilDB'];
        $this->usr_id = $usr_id;
    }

    /**
     * @return ilDBInterface
     */
    public function getDatabaseAdapter()
    {
        return $this->db;
    }

    /**
     * @param ilDBInterface $db
     */
    public function setDatabaseAdapter(ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Reads all items from database
     * @return ilBuddySystemRelation[]
     */
    public function getAll()
    {
        $relations = array();

        $res = $this->db->queryF(
            "
			SELECT usr_id, buddy_usr_id, ts, %s rel_type FROM buddylist WHERE usr_id = %s
			UNION
			SELECT usr_id, buddy_usr_id, ts, (CASE WHEN ignored = 1 THEN %s ELSE %s END) rel_type FROM buddylist_requests WHERE usr_id = %s OR buddy_usr_id = %s
			",
            array(
                'text', 'integer', 'text', 'text', 'integer', 'integer'
            ),
            array(
                self::TYPE_APPROVED, $this->usr_id, self::TYPE_IGNORED, self::TYPE_REQUESTED, $this->usr_id, $this->usr_id
            )
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $relation = $this->getRelationByDatabaseRecord($row);
            $relation->setUserId($row['usr_id']);
            $relation->setBuddyUserId($row['buddy_usr_id']);
            $relation->setTimestamp($row['ts']);
            $relation->setIsOwnedByRequest($relation->getUserId() == $this->usr_id);
            $key = $this->usr_id == $relation->getUserId() ? $relation->getBuddyUserId() : $relation->getUserId();
            $relations[$key] = $relation;
        }

        return $relations;
    }

    /**
     * @param $row
     * @return ilBuddySystemRelation
     */
    private function getRelationByDatabaseRecord($row)
    {
        if (self::TYPE_APPROVED == $row['rel_type']) {
            $relation = new ilBuddySystemRelation(new ilBuddySystemLinkedRelationState());
            return $relation;
        } else {
            if (self::TYPE_IGNORED == $row['rel_type']) {
                $relation = new ilBuddySystemRelation(new ilBuddySystemIgnoredRequestRelationState());
                return $relation;
            } else {
                $relation = new ilBuddySystemRelation(new ilBuddySystemRequestedRelationState());
                return $relation;
            }
        }
    }

    /**
     *
     */
    public function destroy()
    {
        $this->db->queryF(
            "DELETE FROM buddylist WHERE usr_id = %s OR buddy_usr_id = %s",
            array('integer', 'integer'),
            array($this->usr_id, $this->usr_id)
        );

        $this->db->queryF(
            "DELETE FROM buddylist_requests WHERE usr_id = %s OR buddy_usr_id = %s",
            array('integer', 'integer'),
            array($this->usr_id, $this->usr_id)
        );
    }

    /**
     * @param ilBuddySystemRelation $relation
     */
    private function addToApprovedBuddies(ilBuddySystemRelation $relation)
    {
        $this->db->replace(
            'buddylist',
            array(
                'usr_id' => array('integer', $relation->getUserId()),
                'buddy_usr_id' => array('integer', $relation->getBuddyUserId())
            ),
            array(
                'ts' => array('integer', $relation->getTimestamp())
            )
        );

        $this->db->replace(
            'buddylist',
            array(
                'usr_id' => array('integer', $relation->getBuddyUserId()),
                'buddy_usr_id' => array('integer', $relation->getUserId())
            ),
            array(
                'ts' => array('integer', $relation->getTimestamp())
            )
        );
    }

    /**
     * @param ilBuddySystemRelation $relation
     */
    private function removeFromApprovedBuddies(ilBuddySystemRelation $relation)
    {
        $this->db->manipulateF(
            "DELETE FROM buddylist WHERE usr_id = %s AND buddy_usr_id = %s",
            array('integer', 'integer'),
            array($relation->getUserId(), $relation->getBuddyUserId())
        );

        $this->db->manipulateF(
            "DELETE FROM buddylist WHERE buddy_usr_id = %s AND usr_id = %s",
            array('integer', 'integer'),
            array($relation->getUserId(), $relation->getBuddyUserId())
        );
    }

    /**
     * @param ilBuddySystemRelation $relation
     * @param boolean $ignored
     */
    private function addToRequestedBuddies(ilBuddySystemRelation $relation, $ignored)
    {
        $this->db->replace(
            'buddylist_requests',
            array(
                'usr_id' => array('integer', $relation->getUserId()),
                'buddy_usr_id' => array('integer', $relation->getBuddyUserId())
            ),
            array(
                'ts' => array('integer', $relation->getTimestamp()),
                'ignored' => array('integer', (int) $ignored)
            )
        );
    }

    /**
     * @param ilBuddySystemRelation $relation
     */
    private function removeFromRequestedBuddies(ilBuddySystemRelation $relation)
    {
        $this->db->manipulateF(
            "DELETE FROM buddylist_requests WHERE usr_id = %s AND buddy_usr_id = %s",
            array('integer', 'integer'),
            array($relation->getUserId(), $relation->getBuddyUserId())
        );

        $this->db->manipulateF(
            "DELETE FROM buddylist_requests WHERE buddy_usr_id = %s AND usr_id = %s",
            array('integer', 'integer'),
            array($relation->getUserId(), $relation->getBuddyUserId())
        );
    }

    /**
     * @param ilBuddySystemRelation $relation
     */
    public function save(ilBuddySystemRelation $relation)
    {
        $ilAtomQuery = $this->db->buildAtomQuery();
        $ilAtomQuery->addTableLock('buddylist_requests');
        $ilAtomQuery->addTableLock('buddylist');

        $ilAtomQuery->addQueryCallable(function (ilDBInterface $ilDB) use ($relation) {
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
