<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Contact/BuddySystem/test/ilBuddySystemBaseTest.php';

/**
 * Class ilBuddySystemBaseStateTest
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilBuddySystemBaseStateTest extends ilBuddySystemBaseTest
{
    private const RELATION_OWNER_ID = -1;
    private const RELATION_BUDDY_ID = -2;

    protected ilBuddySystemRelation $relation;

    protected function setUp() : void
    {
        $this->relation = new ilBuddySystemRelation(
            $this->getInitialState(),
            self::RELATION_OWNER_ID,
            self::RELATION_BUDDY_ID,
            false,
            time()
        );
    }

    abstract public function getInitialState() : ilBuddySystemRelationState;
}
