<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Contact/BuddySystem/test/ilBuddySystemBaseTest.php';

/**
 * Class ilBuddySystemBaseStateTest
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilBuddySystemBaseStateTest extends ilBuddySystemBaseTest
{
    const RELATION_OWNER_ID = -1;
    const RELATION_BUDDY_ID = -2;

    /**
     * @var bool
     */
    protected $backupGlobals = false;

    /**
     * @var ilBuddySystemRelation
     */
    protected $relation;

    /**
     *
     */
    public function setUp()
    {
        $this->relation = new ilBuddySystemRelation($this->getInitialState());
        $this->relation->setUserId(self::RELATION_OWNER_ID);
        $this->relation->setBuddyUserId(self::RELATION_BUDDY_ID);
    }

    /**
     * @return ilBuddySystemRelationState
     */
    abstract public function getInitialState();
}
